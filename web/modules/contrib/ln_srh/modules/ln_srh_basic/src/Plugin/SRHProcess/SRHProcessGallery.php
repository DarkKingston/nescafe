<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessEntityReference;
use Drupal\ln_srh\Services\SRHMediaUilsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_gallery",
 *   field_name = "field_srh_media_gallery",
 *   label = @Translation("Media Gallery")
 * )
 */

class SRHProcessGallery extends SRHProcessEntityReference {

  /**
   * @var SRHMediaUilsInterface
   */
  protected $srhMediaUtils;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SRHMediaUilsInterface $srhMediaUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->srhMediaUtils = $srhMediaUtils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_srh_media.utils')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['media'] ?? FALSE;
  }

  public function provideEntityRefernce($srh_media, $langcode, $delta = 0) {
    $url = $srh_media['url'] ?? FALSE;
    $title = isset($srh_media['description']) && !empty($srh_media['description']) ? $srh_media['description'] : 'srh_media_' . \Drupal::time()->getRequestTime();
    $title = isset($srh_media['media_title']) && !empty($srh_media['media_title']) ? $srh_media['media_title'] : $title;
    $thumbnailUrl = $srh_media['thumbnailUrl'] ?? '';
    $mimeType = $srh_media['mimeType'] ?? FALSE;
    if($mimeType && $url){
      $mimeTypeParts = explode('/',$mimeType);
      $type = $mimeTypeParts[0] ?? '';
      $provider = $mimeTypeParts[1] ?? '';
      switch ($type) {
        case 'image':
          if($media_image = $this->srhMediaUtils->provideMediaImage($url,$title)){
            return $media_image;
          }
          break;
        case 'video':
          if($media_remote_video = $this->srhMediaUtils->provideMediaRemoteVideo($url,$title,$provider,$thumbnailUrl)){
            return $media_remote_video;
          }
          break;
      }
    }
    return NULL;
  }

  /**
   * @return bool
   */
  protected function isMultiple(){
    $config = $this->getConfiguration();
    $isMultiple = $config['multiple'] ?? TRUE;
    return $isMultiple;
  }

  public function getValues($srh_entity_refernce_data, $langcode)  {
    return [];
  }


}
