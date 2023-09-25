<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessEntityReference;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_translations",
 *   field_name = "field_srh_translations",
 *   label = @Translation("Translations")
 * )
 */

class SRHProcessTranlations extends SRHProcessEntityReference {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * @var SRHInterface
   */
  protected $srhConnerctor;

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SRHUtilsInterface $srhUtils,SRHInterface $srhConnerctor, SRHProcessManager $srhProcessManager, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->srhUtils = $srhUtils;
    $this->srhConnerctor = $srhConnerctor;
    $this->srhProcessManager = $srhProcessManager;
    $this->loggerFactory = $loggerFactory->get('ln_srh');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_srh.utils'),
      $container->get('srh'),
      $container->get('plugin.manager.srh_process'),
      $container->get('logger.factory')
    );
  }

  public function provideEntityRefernce($srh_entity_refernce_data, $langcode, $delta = 0) {
    if ($srh_id = $srh_entity_refernce_data['id'] ?? FALSE) {
      /** @var NodeInterface $recipe */
      if (!$recipe = $this->srhUtils->getRecipeBySRHId($srh_id)) {
        // If neither the parent recipe nor its side dish exists, the parent recipe is saved
        if ($this->entity->isNew()) {
          $this->entity->save();
        }
        if ($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)) {
          try {
            $recipe = $this->srhUtils->syncRecipe($srh_recipe);
          } catch (SRHException $e) {
            $this->loggerFactory->warning($e->getMessage());
            return NULL;
          }
        }
      } else {
        if ($this->needResync($recipe)) {
          if ($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)) {
            $queue = \Drupal::service('queue')->get('srh_recipe_syncronizer_queue');
            $queue->createItem($srh_recipe);
          }
        }
      }
      return $recipe;
    }
    return NULL;
  }

  protected function getSRHEntityReferenceData($srh_data) {
    if(isset($srh_data['translations'])){
      $srh_translations = $srh_data['translations'];
    }
    else{
      if($srh_id = $srh_data['id'] ?? FALSE){
        $srh_translations = $this->srhConnerctor->getRecipeTranslations($srh_id);
      }
    }

    return $srh_translations;
  }

  public function getValues($srh_data, $langcode) {
    return [];
  }

  private function getSRHTranslationByRecipe(NodeInterface $recipe){
    $srhTranslations = [];
    $recipeTranslations = $recipe->get('field_srh_translations')->referencedEntities();
    /** @var NodeInterface $recipeTranslation */
    foreach ($recipeTranslations as $recipeTranslation){
      if(!$recipeTranslation->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
        $srhTranslations[] = $recipeTranslation->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString();
      }
    }
    return $srhTranslations;
  }

  private function needResync(NodeInterface $recipe){
    if($srh_id = $recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString()){
      $srh_translations = $this->srhConnerctor->getRecipeTranslations($srh_id);
      $srh_translations_ids = array_column($srh_translations,'id');
      $recipe_translations = $this->getSRHTranslationByRecipe($recipe);
      $intersect = array_intersect($srh_translations_ids,$recipe_translations);
      if((!empty($recipe_translations) && count($intersect) != count($recipe_translations)) || count($recipe_translations) != count($srh_translations_ids)){
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function isMultiple() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    // This formatter is only available for node.
    return parent::isApplicable($field_definition,$plugin_definition) && $target_type == 'node';
  }
}
