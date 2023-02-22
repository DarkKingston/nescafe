<?php

namespace Drupal\ln_srh_full\Services;

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
use Drupal\ln_srh\Services\SRH;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_full\SRHFullConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermStorageInterface;

class SRHComplementUtils implements SRHComplementUtilsInterface {

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
    $srhProcessManager->setProcessBundle(SRHConstants::SRH_COMPLEMENT_BUNDLE);
    $srhProcessManager->setMultilanguage(TRUE);
    $this->srhProcessManager = $srhProcessManager;
    $this->configFactory = $configFactory;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->loggerFactory = $logger_factory->get('ln_srh');
    $this->srhConnector = $srhConnector;
    $this->time = $time;
    $this->state = $state;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->queue = $queueFactory->get('srh_complement_syncronizer_queue');
    $this->queueManager = $queue_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocaleSync($locale){
    $localesSync = $this->state->get('ln_srh_complements_locales_sync',[]);
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
    $localesSync = $this->state->get('ln_srh_complements_locales_sync',[]);
    $localeSync = $this->getLocaleSync($locale);
    $pages_to_sync = $connection_settings->get('pages_to_sync');
    $localeSync['page_index'] = $localeSync['page_index'] + $pages_to_sync;
    $totalPages = $this->srhConnector->countPagesComplementsByLocale($localeSync['last_sync'],$locale);
    // When reaching the end, the page index and the last synchronization time are restarted.
    if($totalPages < $localeSync['page_index']){
      $localeSync['page_index'] = 1;
      $localeSync['last_sync'] = $totalPages > 0 ? $this->time->getRequestTime() : $localeSync['last_sync'];
    }
    $localesSync[$locale] = $localeSync;
    $this->state->set('ln_srh_complements_locales_sync',$localesSync);
  }

  /**
   * @return array
   */
  private function getFieldMapping() {
    $mapping = $this->configFactory->get('ln_srh.mapping')->get(SRHConstants::SRH_COMPLEMENT_BUNDLE);
    $mapping = array_filter($mapping, function ($var) {
      return $var['enable_mapping'] ?? FALSE;
    });
    return $mapping;
  }

  /**
   * @param $srh_id
   * @return \Drupal\Core\Entity\EntityInterface|false
   */
  public function getComplementBySRHId($srh_id, $langCode = NULL) {
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', SRHConstants::SRH_COMPLEMENT_BUNDLE)
      ->condition(SRHFullConstants::SRH_COMPLEMENT_EXTERNAL_FIELD, $srh_id)
      ->range(0, 1);
    $result = $query->execute();
    $complements = $this->nodeStorage->loadMultiple($result);
    /** @var NodeInterface $complement */
    $complement = empty($result) ? FALSE : reset($complements);
    if ($complement && $langCode && $langCode != $complement->language()->getId()) {
      // Get translation if available.
      if (!$complement->hasTranslation($langCode)) {
        $complement->addTranslation($langCode, $complement->toArray());
      }
      $complement = $complement->getTranslation($langCode);
    }
    return $complement;
  }

  /**
   * {@inheritdoc}
   */
  public function getSRHComplement($srh_id) {
    if ($srh_complements = $this->srhConnector->getComplements(0, [$srh_id])) {
      $langId = \Drupal::languageManager()->getCurrentLanguage()->getId();
      return $srh_complements[$srh_id . ':' . $langId] ?? FALSE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function reSyncComplement(NodeInterface $complement) {
    $complementSync = FALSE;
    if ($srh_id = $complement->get(SRHFullConstants::SRH_COMPLEMENT_EXTERNAL_FIELD)->getString()) {
      try {
        if ($complementSync = $this->syncComplement($srh_id)) {
          \Drupal::messenger()->addMessage(t('The complement @title has been successfully synced', ['@title' => $complementSync->label()]), 'status');
        }
      } catch (SRHException $e) {
        \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
      }
    }
    if(!$complementSync){
      \Drupal::messenger()->addMessage(t('An error has occurred synchronizing the complement @title. It may be that the complement is not published on the server',['@title' => $complement->label()]), 'warning');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function syncComplements() {
    $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
    $pages_to_sync = $connection_settings->get('pages_to_sync');
    $locales = $connection_settings->get('locales');
    foreach ($locales as $locale){
      $locale_code = $locale['connect_markets'];
      $localeSync = $this->getLocaleSync($locale_code);
      $timeSync = $localeSync['last_sync'];
      $pageIndex = $localeSync['page_index'];
      $totalPages = $this->srhConnector->countPagesComplementsByLocale($timeSync, $locale_code);
      $srh_complements = $this->srhConnector->getComplementsToSync($timeSync,$locale_code,$pages_to_sync,$pageIndex,$totalPages);
      foreach ($srh_complements as $srh_complement) {
        $this->queue->createItem($srh_complement);
      }
      $this->updateLocaleSync($locale_code);
    }
  }

  /**
   * {@inheritdoc}
   * @throws SRHException
   */
  public function syncComplement($srh_complement) {
    if (!is_array($srh_complement)) {
      $srh_complement = $this->getSRHComplement($srh_complement);
    }

    if (!is_array($srh_complement)) {
      $this->loggerFactory->error('SyncComplement expected parameter to be array');
      return FALSE;
    }

    $mapping = $this->getFieldMapping();
    $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
    $status = $srh_complement['status'];
    $message_arguments = [
      '@name' => $srh_complement['name'],
      '@id' => $srh_complement['id'],
    ];

    /** @var FieldableEntityInterface $complement */
    if (!$complement = $this->getComplementBySRHId($srh_complement['id'], $srh_complement['langcode'])) {
      /** @var FieldableEntityInterface $recipe */
      $complement = $this->nodeStorage->create([
        'type' => SRHConstants::SRH_COMPLEMENT_BUNDLE,
        'title' => $srh_complement['name'],
        'uid' => $connection_settings->get('author'),
        SRHFullConstants::SRH_COMPLEMENT_EXTERNAL_FIELD => $srh_complement['id'],
      ]);
      $langcode = $srh_complement['langcode'] ?? \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
      $complement->set('langcode', $langcode);
    }
    if ($complement->isNew() && $status != SRHFullConstants::SRH_COMPLEMENT_STATUS_PUBLISHED) {
      throw new SRHException($this->t('The complement: @name (@id) will not be synchronized because it is not published on the server', $message_arguments));
    }
    if ($status == SRHFullConstants::SRH_COMPLEMENT_STATUS_DELETED) {
      $complement->delete();
      throw new SRHException($this->t('The complement: @name (@id) has been deleted because on the server it is marked for deletion', $message_arguments));
    }
    else {
      $published = $status == SRHFullConstants::SRH_COMPLEMENT_STATUS_PUBLISHED;
      $complement->set('status', $published);
    }
    $complement->set('title', $srh_complement['name']);
    foreach ($mapping as $field => $field_map) {
      if (!$complement->hasField($field)) {
        continue;
      }
      $configuration = $field_map['plugin']['settings'] ?? [];
      try {
        $srhProcessPlugin = $this->srhProcessManager->createInstance($field_map['plugin']['id'], $configuration);
        $complement->set($field, $srhProcessPlugin->process($complement, $srh_complement, $field));
      } catch (PluginException $e) {
        $this->loggerFactory->error($e->getMessage());
      }
    }
    try {
      $complement->save();
      return $complement;
    } catch (EntityStorageException $e) {
      $this->loggerFactory->error($this->t('An error occurred while creating the complement: @name (@id)', $message_arguments));
      return FALSE;
    }
  }

}
