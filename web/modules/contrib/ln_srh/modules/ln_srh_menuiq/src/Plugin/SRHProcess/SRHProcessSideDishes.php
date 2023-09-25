<?php

namespace Drupal\ln_srh_menuiq\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHMediaUilsInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessTag;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_sidedishes",
 *   field_name = "field_srh_sidedishes",
 *   label = @Translation("Shide Dishes")
 * )
 */

class SRHProcessSideDishes extends SRHProcessParagraph {

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
   * @var SRHMediaUilsInterface
   */
  protected $srhMediaUtils;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils,SRHInterface $srhConnerctor, SRHProcessManager $srhProcessManager, SRHMediaUilsInterface $srhMediaUtils, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhUtils = $srhUtils;
    $this->srhConnerctor = $srhConnerctor;
    $this->srhProcessManager = $srhProcessManager;
    $this->srhMediaUtils = $srhMediaUtils;
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
      $container->get('ln_srh_media.utils'),
      $container->get('logger.factory')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    $srh_shidedishes = [];
    if($srh_id = $srh_data['id'] ?? FALSE){
      $srh_shidedishes = $this->srhConnerctor->getRecipeSideDishes($srh_id);
    }
    return $srh_shidedishes;
  }

  public function getValues($srh_data, $langcode) {
    if(!$srh_id = $srh_data['id'] ?? FALSE){
      return [];
    }
    /** @var SRHProcessTag $srhProcessTagAssociationType */
    $srhProcessTagAssociationType = $this->srhProcessManager->createInstance('srh_process_tag',[
      'srh_source_field' => 'associationType',
      'vocabulary_id' => 'srh_association_type',
    ]);
    /** @var SRHProcessTag $srhProcessTagSideDishType */
    $srhProcessTagSideDishType = $this->srhProcessManager->createInstance('srh_process_tag',[
      'srh_source_field' => 'sideDishType',
      'vocabulary_id' => 'srh_sidedish_type',
    ]);
    $values =  [
      'type' => 'srh_sidedish',
      'field_srh_id' => $srh_id,
      'field_c_title' => $srh_data['name'] ?? '',
      'field_srh_percentage' => $srh_data['myMenuIQ']['score'] ?? '',
    ];
    /** @var ParagraphInterface $sideDish */
    $sideDish = $this->paragraphStorage->create($values);
    $values += [
      'field_srh_association_type' => $srhProcessTagAssociationType->process($sideDish,$srh_data,'field_srh_association_type'),
      'field_srh_sidedish_type' => $srhProcessTagSideDishType->process($sideDish,$srh_data,'field_srh_sidedish_type'),
    ];

    if(isset($srh_data['associationType']['id'])){
      switch ($srh_data['associationType']['id']) {
        case SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_RECIPE:
          $values += [
            'field_srh_recipe' => $this->provideSideDishRecipe($srh_data),
          ];
          break;
        case SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_COMPLEMENT:
          $values += [
            'field_srh_media' => $this->provideComplementMedia($srh_data),
          ];
          break;
      }
    }
    return $values;
  }

  private function provideSideDishRecipe($srh_data){
    if($srh_id = $srh_data['id'] ?? FALSE){
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
      }
    }
    return $recipe;
  }

  private function provideComplementMedia($srh_data){
    $url = $srh_data['complementMetadata']['media'] ?? FALSE;
    $title = $srh_data['name'] ?? 'srh_media_' . \Drupal::time()->getRequestTime();
    if($url){
      if($media_image = $this->srhMediaUtils->provideMediaImage($url,$title)){
        return $media_image;
      }
    }
    return NULL;
  }

}
