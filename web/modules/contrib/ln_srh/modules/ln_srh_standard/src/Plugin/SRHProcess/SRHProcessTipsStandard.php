<?php

namespace Drupal\ln_srh_standard\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessGallery;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessCloudFrontMedias;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_tips_standard",
 *   field_name = "field_srh_tips",
 *   label = @Translation("Tips standard")
 * )
 */

class SRHProcessTipsStandard extends SRHProcessParagraph {

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * SRHProcessStepsStandard constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param SRHProcessManager $srhProcessManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHProcessManager $srhProcessManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhProcessManager = $srhProcessManager;
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
      $container->get('plugin.manager.srh_process')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    $tips = $srh_data['tips']['general'] ?? $srh_data['tips'] ?? [];
    if(!empty($tips)){
      $order = array_column($tips, 'order');
      array_multisort($order, SORT_ASC, $tips);
    }
    return $tips;
  }

  public function getValues($srh_data, $langcode) {
    /** @var SRHProcessGallery $srhProcessPlugin */
    $srhProcessPlugin = $this->srhProcessManager->createInstance('srh_process_gallery', ['multiple' => FALSE]);
    /** @var SRHProcessCloudFrontMedias $srhCloudFrontProcessPlugin */
    $srhCloudFrontProcessPlugin = $this->srhProcessManager->createInstance('srh_process_cloudfront_medias', ['multiple' => FALSE]);
    $values = [
      'type' => 'srh_tip',
      'field_c_title' => $srh_data['title'] ?? '',
      'field_c_text' => $srh_data['description'] ?? '',
    ];
    /** @var ParagraphInterface $srh_tip */
    $srh_tip = $this->paragraphStorage->create($values);
    $values['field_srh_media'] = $srhProcessPlugin->process($srh_tip,$srh_data,'field_srh_media');
    $values['field_srh_cloudfront_media'] = $srhCloudFrontProcessPlugin->process($srh_tip,$srh_data,'field_srh_cloudfront_media');
    return $values;
  }

}
