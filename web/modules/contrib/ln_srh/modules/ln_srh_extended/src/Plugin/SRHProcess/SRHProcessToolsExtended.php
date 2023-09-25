<?php

namespace Drupal\ln_srh_extended\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessTerm;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessGallery;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_tools_extended",
 *   field_name = "field_srh_tools",
 *   label = @Translation("Tools Extended")
 * )
 */
class SRHProcessToolsExtended extends SRHProcessTerm{

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * SRHProcessTerm constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils, SRHProcessManager $srhProcessManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $srhUtils);
    $this->srhProcessManager = $srhProcessManager;
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
      $container->get('plugin.manager.srh_process')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['tools'];
  }

  public function getValues($srh_data, $langcode) {
    /** @var SRHProcessGallery $srhProcessPlugin */
    $srhProcessMediaGalleryPlugin = $this->srhProcessManager->createInstance('srh_process_gallery',[]);
    $srhProcessTipsPlugin = $this->srhProcessManager->createInstance('srh_process_tips_standard',[]);
    $srhCloudFrontProcessPlugin = $this->srhProcessManager->createInstance('srh_process_cloudfront_medias', []);
    $values = [
      'vid' => 'srh_tool',
      'name' => $srh_data['name'] ?? $srh_data['id'],
      SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_data['id'],

    ];
    /** @var TermInterface $tool */
    $tool = $this->termStorage->create($values);
    $srh_data['media_name'] = $srh_data['name'] ?? NULL;
    $values +=[
      'field_srh_media_gallery' => $srhProcessMediaGalleryPlugin->process($tool,$srh_data,'field_srh_media_gallery'),
      'field_srh_cloudfront_media' => $srhCloudFrontProcessPlugin->process($tool,$srh_data,'field_srh_cloudfront_media'),
      'field_srh_tips' => $srhProcessTipsPlugin->process($tool,$srh_data,'field_srh_tips'),
    ];
    return $values;
  }

  protected function getVocabularyId() {
    return 'srh_tool';
  }

  protected function isMultiple() {
    return TRUE;
  }

}
