<?php

namespace Drupal\ln_srh_extended\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Services\SRHMediaUilsInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessGallery;
use Drupal\ln_srh_standard\Plugin\SRHProcess\SRHProcessStepsStandard;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_steps_extended",
 *   field_name = "field_srh_steps",
 *   label = @Translation("Steps Extended")
 * )
 */

class SRHProcessStepsExtended extends SRHProcessStepsStandard {

  /**
   * @var SRHMediaUilsInterface
   */
  protected $srhMediaUtils;

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * SRHProcessStepsExtended constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param SRHProcessManager $srhProcessManager
   * @param SRHMediaUilsInterface $srhMediaUtils
   * @param SRHUtilsInterface $srhUtils
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHProcessManager $srhProcessManager, SRHMediaUilsInterface $srhMediaUtils, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager,$srhProcessManager);
    $this->srhMediaUtils = $srhMediaUtils;
    $this->srhUtils = $srhUtils;
  }

  /**
   * @inerhitDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.srh_process'),
      $container->get('ln_srh_media.utils'),
      $container->get('ln_srh.utils')
    );
  }
  public function getValues($srh_data, $langcode) {
    $values = parent::getValues($srh_data, $langcode);
    $srhProcessIngredientsPlugin = $this->srhProcessManager->createInstance('srh_process_ingredients_standard', []);
    $srhProcessToolsPlugin = $this->srhProcessManager->createInstance('srh_process_tools_extended', []);
    $srhProcessGalleryPlugin = $this->srhProcessManager->createInstance('srh_process_gallery', ['multiple' => FALSE]);
    $srhCloudFrontProcessPlugin = $this->srhProcessManager->createInstance('srh_process_cloudfront_medias', ['multiple' => FALSE]);
    /** @var ParagraphInterface $srh_step */
    $srh_step = $this->paragraphStorage->create($values);
    $values += [
      'field_c_title'               => $srh_data['title'] ?? '',
      'field_srh_duration'          => $srh_data['duration'] ?? '',
      'field_srh_step_type'         => $srh_data['stepType'] ?? '',
      'field_srh_is_active'         => $srh_data['isActive'] ?? FALSE,
      'field_srh_media'             => $srhProcessGalleryPlugin->process($srh_step,$srh_data,'field_srh_media'),
      'field_srh_cloudfront_media'  => $srhCloudFrontProcessPlugin->process($srh_step,$srh_data,'field_srh_cloudfront_media'),
      'field_srh_ingredients'       => $srhProcessIngredientsPlugin->process($srh_step,$srh_data,'field_srh_ingredients'),
      'field_srh_tools'             => $srhProcessToolsPlugin->process($srh_step,$srh_data,'field_srh_tools'),
    ];

    return $values;
  }

}
