<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_cloudfront_medias",
 *   field_name = "field_srh_cloudfront_medias",
 *   label = @Translation("CloudFront Medias")
 * )
 */

class SRHProcessCloudFrontMedias extends SRHProcessParagraph {

  protected function getSRHEntityReferenceData($srh_data) {

    return $srh_data['media'] ?? [];
  }

  public function getValues($srh_data, $langcode) {
    if(isset($srh_data['url']) && !empty($srh_data['url'])){
      return [
        'type' => 'srh_cloudfront_media',
        'field_srh_cloudfront_url' => $srh_data['url'],
        'field_srh_cloudfront_description' => $srh_data['description'] ?? '',
        'field_srh_cloudfront_mediaright' => $srh_data['mediaRight'] ?? '',
        'field_srh_cloudfront_mime_type' => $srh_data['mimeType'] ?? '',
        'field_srh_cloudfront_thumbnail' => $srh_data['thumbnailUrl'] ?? '',
        'field_srh_cloudfront_height' => $srh_data['height'] ?? '',
        'field_srh_cloudfront_width' => $srh_data['width'] ?? '',
      ];
    }

    return FALSE;
  }

  /**
   * @return bool
   */
  protected function isMultiple(){
    $config = $this->getConfiguration();
    $isMultiple = $config['multiple'] ?? TRUE;
    return $isMultiple;
  }

}
