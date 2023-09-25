<?php

namespace Drupal\ln_srh_extended\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessTag;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_versions",
 *   field_name = "field_srh_versions",
 *   label = @Translation("Versions")
 * )
 */

class SRHProcessVersions extends SRHProcessParagraph {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils,SRHInterface $srhConnerctor, SRHProcessManager $srhProcessManager, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
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
      $container->get('entity_type.manager'),
      $container->get('ln_srh.utils'),
      $container->get('srh'),
      $container->get('plugin.manager.srh_process'),
      $container->get('logger.factory')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    $srh_versions = [];
    if($srh_id = $srh_data['id'] ?? FALSE){
      $srh_versions = $this->srhConnerctor->getRecipeVersions($srh_id);
    }
    return $srh_versions;
  }

  public function getValues($srh_data, $langcode) {
    /** @var SRHProcessTag $srhProcessTagVersions */
    $srhProcessTagVersions = $this->srhProcessManager->createInstance('srh_process_tag',[
      'srh_source_field' => 'versionType',
      'vocabulary_id' => 'srh_version_type',
    ]);
    $values =  [
      'type' => 'srh_version',
    ];
    /** @var ParagraphInterface $version */
    $version = $this->paragraphStorage->create($values);
    $values += [
      'field_srh_version_type' => $srhProcessTagVersions->process($version,$srh_data,'field_srh_version_type'),
      'field_srh_recipe' => $this->provideVersionRecipe($srh_data),
    ];

    return $values;
  }

  private function provideVersionRecipe($srh_data){
    if($srh_id = $srh_data['id'] ?? FALSE){
      /** @var NodeInterface $recipe */
      if(!$recipe = $this->srhUtils->getRecipeBySRHId($srh_id)){
        // If neither the parent recipe nor its side dish exists, the parent recipe is saved
        if($this->entity->isNew()){
          $this->entity->save();
        }
        if($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)){
          try{
            $recipe = $this->srhUtils->syncRecipe($srh_recipe);
          }catch (SRHException $e){
            $this->loggerFactory->warning($e->getMessage());
            return FALSE;
          }
        }
      }else{
        if($this->needResync($recipe)){
          if ($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)) {
            $queue = \Drupal::service('queue')->get('srh_recipe_syncronizer_queue');
            $queue->createItem($srh_recipe);
          }
        }
      }
    }
    return $recipe;
  }

  private function getSRHVersionByRecipe(NodeInterface $recipe){
    $srhVersions = [];
    $recipeVersions = $recipe->get('field_srh_versions')->referencedEntities();
    /** @var NodeInterface $recipeVersion */
    foreach ($recipeVersions as $recipeVersion){
      if(!$recipeVersion->get('field_srh_recipe')->isEmpty()){
        $nodeRecipe = $recipeVersion->get('field_srh_recipe')->entity;
        if(!$nodeRecipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
          $srhVersions[] = $nodeRecipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString();
        }
      }
    }
    return $srhVersions;
  }

  private function needResync(NodeInterface $recipe){
    if($srh_id = $recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString()){
      $srh_versions = $this->srhConnerctor->getRecipeVersions($srh_id);
      if ($srh_versions === FALSE) {
        return FALSE;
      }
      $srh_versions_ids = array_column($srh_versions,'id');
      $recipe_versions = $this->getSRHVersionByRecipe($recipe);
      $intersect = array_intersect($srh_versions_ids,$recipe_versions);
      if((!empty($recipe_versions) && count($intersect) != count($recipe_versions)) || count($recipe_versions) != count($srh_versions_ids)){
        return TRUE;
      }
    }
    return FALSE;
  }

}
