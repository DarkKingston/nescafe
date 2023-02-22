<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessBase;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_metatag",
 *   field_name = "field_meta_tags",
 *   label = @Translation("Metatags")
 * )
 */

class SRHProcessMetatag extends SRHProcessBase {

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name){
    $metatags = [
      'title'       => $srh_data['seo']['title'] ?? '',
      'description' => $srh_data['seo']['description'] ?? '',
      'canonical_url' => $srh_data['seo']['canonicalOwner'] ?? '',
      'content_language' => str_replace('_', '-', $srh_data['locale'] ?? '')
    ];

    $metatags['srh_alternate'] = '';
    foreach ($srh_data['alternates'] ?? [] as $alternate) {
      if (isset($alternate['locale']) && !empty($alternate['locale']) && isset($alternate['url']) && !empty($alternate['url'])) {
        $metatags['srh_alternate'] .= str_replace('_', '-', $alternate['locale']) . '|' . $alternate['url'] . PHP_EOL;
      }
    }

    $sharing = $srh_data['sharing'] ?? [];
    foreach ($sharing as $socialNetwork => $share){
      switch ($socialNetwork) {
        case 'facebook':
          $metatags['og_image'] = $share['image'] ?? '';
          $metatags['og_description'] = $share['description'] ?? '';
          $metatags['og_title'] = $share['title'] ?? '';
          break;
        case 'twitter':
          $metatags['twitter_cards_image'] = $share['image'] ?? '';
          $metatags['twitter_cards_description'] = $share['description'] ?? '';
          $metatags['twitter_cards_title'] = $share['title'] ?? '';
          break;
        case 'pinterest':
          $metatags['pinterest_media'] = $share['image'] ?? '';
          $metatags['pinterest_description'] = $share['description'] ?? '';
          $metatags['pinterest_id'] = $share['title'] ?? '';
          break;
        case 'instagram':
          $metatags['srh_ins_image'] = $share['image'] ?? '';
          $metatags['srh_ins_description'] = $share['description'] ?? '';
          $metatags['srh_ins_title'] = $share['title'] ?? '';
          break;
      }
    }
    return serialize($metatags);
  }

}
