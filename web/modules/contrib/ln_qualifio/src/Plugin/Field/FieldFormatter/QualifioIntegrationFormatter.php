<?php

namespace Drupal\ln_qualifio\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'QualifioIntegrationFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "QualifioIntegrationFormatter",
 *   label = @Translation("Qualifio Integration Formatter"),
 *   description = @Translation("Qualifio Integration Formatter"),
 *   field_types = {
 *     "QualifioIntegrationParams",
 *   }
 * )
 */
class QualifioIntegrationFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get current language code to get multilingual adimo widget.
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $elements = [];

    $campaign_feed_url = \Drupal::config('ln_qualifio.settings')->get('campaigns_feed');
    $file = simplexml_load_file($campaign_feed_url);
    $json = $file;

    $qualifioscript = [];
    $campaigntitle = [];

    if (!empty($file->channels)) {
      foreach ($file->channels as $fkey => $fval) {
        $campaign_id = 'campaign_' . $fval->campaign->campaignId;
        if ($items->qualifiointegrationType == $campaign_id) {
          $qualifioscript = $fval->integration->javascript;
          $campaigntitle = $fval->campaign->campaignTitle;
        }
      }
    }

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme'            => 'qualifio',
        '#qualifiointegrationType' => $item->qualifiointegrationType,
        '#campaigntitle' => $campaigntitle,
        '#qualifioscript' => $qualifioscript,
        '#attached'         => ['library' => ['ln_qualifio/qualifio-general']],
        '#language'         => !empty($lang_code) ? $lang_code : 'en',
      ];
    }

    return $elements;
  }

}
