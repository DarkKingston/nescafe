<?php

namespace Drupal\ln_srh\Services;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_srh\Form\SRHConnectionSettings;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;

class SRHUtils implements SRHUtilsInterface {

  use StringTranslationTrait;

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var SRHInterface
   */
  protected $srhConnector;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerFactory;

  /**
   * @var TimeInterface
   */
  protected $time;

  /**
   * @var StateInterface
   */
  protected $state;

  /**
   * @var TermStorageInterface
   */
  protected $termStorage;

  /**
   * @var QueueInterface
   */
  protected $queue;

  /**
   * @var QueueWorkerManagerInterface
   */
  protected $queueManager;

  public function __construct(SRHProcessManager $srhProcessManager, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $logger_factory, SRHInterface $srhConnector, TimeInterface $time, StateInterface $state, QueueFactory $queueFactory, QueueWorkerManagerInterface $queue_manager){
    $this->srhProcessManager = $srhProcessManager;
    $this->configFactory = $configFactory;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->loggerFactory = $logger_factory->get('ln_srh');
    $this->srhConnector = $srhConnector;
    $this->time = $time;
    $this->state = $state;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->queue = $queueFactory->get('srh_recipe_syncronizer_queue');
    $this->queueManager = $queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function syncRecipes() {
    $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
    $pages_to_sync = $connection_settings->get('pages_to_sync');
    $locales = $connection_settings->get('locales');
    foreach ($locales as $locale){
      $locale_code = $locale['connect_markets'];
      $localeSync = $this->getLocaleSync($locale_code);
      $timeSync = $localeSync['last_sync'];
      $pageIndex = $localeSync['page_index'];
      $totalPages = $this->srhConnector->countPagesRecipesByLocale($timeSync, $locale_code);
      $srh_recipes = $this->srhConnector->getRecipesToSync($timeSync,$locale_code,$pages_to_sync,$pageIndex,$totalPages);
      foreach ($srh_recipes as $srh_recipe) {
        $this->queue->createItem($srh_recipe);
      }
      $this->updateLocaleSync($locale_code);
    }
  }

  /**
   * {@inheritdoc}
   * @throws SRHException
   */
  public function syncRecipe($srh_recipe){
    if(!is_array($srh_recipe)){
      $srh_recipe = $this->getSRHRecipe($srh_recipe);
    }
    $mapping = $this->getFieldMapping();
    $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
    $status = $srh_recipe['status']['id'] ?? 1;
    /** @var FieldableEntityInterface $recipe */
    if (!$recipe = $this->getRecipeBySRHId($srh_recipe['id'])) {
      /** @var FieldableEntityInterface $recipe */
      $recipe = $this->nodeStorage->create([
        'type' => SRHConstants::SRH_RECIPE_BUNDLE,
        'title' => $srh_recipe['name'],
        'uid' => $connection_settings->get('author'),
        SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_recipe['id'],
      ]);
    }
    if($recipe->isNew() && $status != SRHConstants::SRH_STATUS_PUBLISHED){
      throw new SRHException($this->t('The recipe: @name (@id) will not be synchronized because it is not published on the server', ['@name' => $srh_recipe['name'], '@id' => $srh_recipe['id']]));
    }
    if($status == SRHConstants::SRH_STATUS_DELETED){
      $recipe->delete();
      throw new SRHException($this->t('The recipe: @name (@id) has been deleted because on the server it is marked for deletion', ['@name' => $srh_recipe['name'], '@id' => $srh_recipe['id']]));
    }else{
      $published = $status == SRHConstants::SRH_STATUS_PUBLISHED;
      $recipe->set('status',$published);
    }
    $langcode = $srh_recipe['langcode'] ?? \Drupal::languageManager()->getCurrentLanguage()->getId();
    $recipe->set('langcode',$langcode);
    $recipe->set('title',$srh_recipe['name']);
    foreach ($mapping as $recipe_field => $field_map) {
      $configuration = $field_map['plugin']['settings'] ?? [];
      try {
        $srhProcessPlugin = $this->srhProcessManager->createInstance($field_map['plugin']['id'], $configuration);
      } catch (PluginException $e) {
        $this->loggerFactory->error($e->getMessage());
      }
      if($recipe->hasField($recipe_field)){
        $recipe->set($recipe_field,$srhProcessPlugin->process($recipe,$srh_recipe,$recipe_field));
      }
    }
    try {
      $recipe->save();
      return $recipe;
    } catch (EntityStorageException $e) {
      $this->loggerFactory->error($this->t('An error occurred while creating the recipe: @name (@id)', ['@name' => $srh_recipe['name'], '@id' => $srh_recipe['id']]));
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processQueueSync($queue_type = 'srh_recipe') {
    $queue_worker = $this->queueManager->createInstance($queue_type . '_syncronizer_queue');
    while($item = $this->queue->claimItem()) {
      try {
        $queue_worker->processItem($item->data);
        $this->queue->deleteItem($item);
      }
      catch (SuspendQueueException $e) {
        $this->queue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        $item_name = $item['name'] ?? t('Unnamed');
        if ($queue_type == 'srh_recipe') {
          $this->loggerFactory->error($this->t('An error occurred while synchronizing the recipe: @name', ['@name' => $item_name]));
        }
        else {
          $this->loggerFactory->error($this->t('An error occurred while synchronizing the complement: @name', ['@name' => $item_name]));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLocaleSync($locale){
    $localesSync = $this->state->get('ln_srh_recipes_locales_sync',[]);
    return [
      'page_index' => $localesSync[$locale]['page_index'] ?? 1,
      'last_sync' => $localesSync[$locale]['last_sync'] ?? 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function updateLocaleSync($locale){
    $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
    $localesSync = $this->state->get('ln_srh_recipes_locales_sync', []);
    $localeSync = $this->getLocaleSync($locale);
    $pages_to_sync = $connection_settings->get('pages_to_sync');
    $localeSync['page_index'] = $localeSync['page_index'] + $pages_to_sync;
    $totalPages = $this->srhConnector->countPagesRecipesByLocale($localeSync['last_sync'],$locale);
    // When reaching the end, the page index and the last synchronization time are restarted.
    if($totalPages < $localeSync['page_index']){
      $localeSync['page_index'] = 1;
      $localeSync['last_sync'] = $totalPages > 0 ? $this->time->getRequestTime() : $localeSync['last_sync'];
    }
    $localesSync[$locale] = $localeSync;
    $this->state->set('ln_srh_recipes_locales_sync', $localesSync);
  }

  /**
   * @return array
   */
  private function getFieldMapping(){
    $mapping = $this->configFactory->get('ln_srh.mapping')->get(SRHConstants::SRH_RECIPE_BUNDLE);
    $mapping = array_filter($mapping,function ($var){
      return $var['enable_mapping'] ?? FALSE;
    });
    return $mapping;
  }

  /**
   * @param $srh_id
   * @return \Drupal\Core\Entity\EntityInterface|false
   */
  public function getRecipeBySRHId($srh_id){
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', SRHConstants::SRH_RECIPE_BUNDLE)
      ->condition(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD, $srh_id)
      ->range(0, 1);
    $result = $query->execute();
    $recipes = $this->nodeStorage->loadMultiple($result);
    return empty($result) ? FALSE : reset($recipes);
  }

  /**
   * {@inheritdoc}
   */
  public function getSRHRecipe($srh_id){
    if($srh_recipes = $this->srhConnector->getRecipes(0,[$srh_id])){
      return $srh_recipes[$srh_id] ?? FALSE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function reSyncRecipe(NodeInterface $recipe){
    $recipeSync = FALSE;
    if(!$recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
      if($srh_recipe = $this->getSRHRecipe($recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString())){
        try{
          if($recipeSync = $this->syncRecipe($srh_recipe)){
            \Drupal::messenger()->addMessage(t('The recipe @title has been successfully synced',['@title' => $recipeSync->label()]), 'status');
          }
        }catch (SRHException $e){
          \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
        }
      }
    }
    if(!$recipeSync){
      \Drupal::messenger()->addMessage(t('An error has occurred synchronizing the recipe @title. It may be that the recipe is not published on the server',['@title' => $recipe->label()]), 'warning');
    }
  }

  private function clearObsoleteRecipes(){
    // Get all recipes from webservice
    $srh_recipes = $this->srhConnector->getRecipes(0);
    $srh_recipe_ids = array_column($srh_recipes,'id','id');
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', SRHConstants::SRH_RECIPE_BUNDLE)
      ->condition(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD, $srh_recipe_ids, 'NOT IN')
      ->exists(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD);
    $result = $query->execute();
    $obsoleteRecipes = $this->nodeStorage->loadMultiple($result);
    if(!empty($obsoleteRecipes)){
      try {
        $this->nodeStorage->delete($obsoleteRecipes);
        \Drupal::messenger()->addMessage(t('SRH sync: @total recipes have been deleted',['@total' => count($obsoleteRecipes)]), 'status');
      } catch (EntityStorageException $e) {
        $this->loggerFactory->error($e->getMessage());
      }
    }
  }

  /**
   * @return int
   */
  private function countRecipesSyncronized(){
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', SRHConstants::SRH_RECIPE_BUNDLE)
      ->exists(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD);
    return $query->count()->execute();
  }

  /**
   * @param $srh_id
   * @param $vid
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getTermBySRHId($srh_id, $vid) {
    $query = $this->termStorage->getQuery();
    $query
      ->condition('vid', $vid)
      ->condition(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD, $srh_id);
    $result = $query->execute();
    $terms = $this->termStorage->loadMultiple($result);
    return empty($result) ? FALSE : reset($terms);
  }

  /**
   * @param $name
   * @param $vid
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getTermByName($name, $vid) {
    $query = $this->termStorage->getQuery();
    $query
      ->condition('vid', $vid)
      ->condition('name', $name);
    $result = $query->execute();
    $terms = $this->termStorage->loadMultiple($result);
    return empty($result) ? FALSE : reset($terms);
  }

  /**
   * {@inheritdoc}
   */
  public function addTermTranslation(TermInterface $term, $langcode, $values) {
    if ($term->isTranslatable() && $term->language()->getId() != $langcode) {
      if (!$term->hasTranslation($langcode)) {
        $object = $term->addTranslation($langcode, $values);
      }
      else {
        $object = $term->getTranslation($langcode);
      }
      foreach ($values as $field_name => $value) {
        $object->set($field_name, $value);
      }
      try {
        $object->save();
      } catch (EntityStorageException $e) {
        \Drupal::logger('ln_srh')->error($e->getMessage());
      }
    }
  }

  /**
   * @param $values
   * @param $langcode
   * @return \Drupal\Core\Entity\EntityInterface|TermInterface|mixed|null
   */
  public function provideTerm($values, $langcode) {
    /** @var TermInterface $term */
    if (isset($values[SRHConstants::SRH_RECIPE_EXTERNAL_FIELD])) {
      $term = $this->getTermBySRHId($values[SRHConstants::SRH_RECIPE_EXTERNAL_FIELD], $values['vid']);
    } else {
      $term = $this->getTermByName($values['name'], $values['vid']);
    }

    if (!$term) {
      /** @var TermInterface $term */
      $term = $this->termStorage->create($values);
    }

    if ($term->isTranslatable() && $term->language()->getId() != $langcode) {
      $this->addTermTranslation($term, $langcode, $values);
    }
    else {
      foreach ($values as $field_name => $value) {
        $term->set($field_name, $value);
      }
    }

    try {
      $term->save();
    } catch (EntityStorageException $e) {
      \Drupal::logger('ln_srh')->error($e->getMessage());
      return NULL;
    }

    return $term;
  }

}
