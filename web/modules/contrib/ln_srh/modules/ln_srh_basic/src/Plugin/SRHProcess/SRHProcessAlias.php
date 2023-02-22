<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessBase;
use Drupal\ln_srh\SRHConstants;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_alias",
 *   field_name = "path",
 *   label = @Translation("Path Alias")
 * )
 */

class SRHProcessAlias extends SRHProcessBase {

  public function process(ContentEntityInterface $entity, $srh_data, $field_name) {
    $srh_seo_url = $srh_data['seo']['slug'] ?? FALSE;
    if (!$srh_seo_url) {
      return NULL;
    }

    $config = \Drupal::config('ln_srh.settings');
    $locales = $config->get('locales');

    $prefixKey = $this->getProcessBundle() == SRHConstants::SRH_COMPLEMENT_BUNDLE ? 'complement_prefix' : 'content_prefix';
    $prefixes = array_column($locales, $prefixKey, 'connect_markets');
    $locale = $srh_data['locale'] ?? '';
    $prefix = $prefixes[$locale] ?? '';

    return [
      'alias' => "{$prefix}/{$srh_seo_url}",
      'pathauto' => 0,
    ];
  }

}
