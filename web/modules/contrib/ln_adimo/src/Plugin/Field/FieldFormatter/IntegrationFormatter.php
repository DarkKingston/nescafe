<?php

namespace Drupal\ln_adimo\Plugin\Field\FieldFormatter;


use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'integrationFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "integrationFormatter",
 *   label = @Translation("Integration Formatter"),
 *   description = @Translation("Integration Formatter"),
 *   field_types = {
 *     "integrationParams",
 *   }
 * )
 */
class IntegrationFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get current language code to get multilingual adimo widget.
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $elements = [];

    $file = file_get_contents(drupal_get_path('module', 'ln_adimo') . '/integrations.json', FILE_USE_INCLUDE_PATH);

    $json = json_decode($file);

    $selectedItems = [];

    foreach ($json->integrations as $integration) {
      if ($items->integrationType == $integration->id) {
        $selectedItems = $integration;

      }
    }

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme'            => $selectedItems->htmltemplate,
        '#attached'         => ['library' => ['ln_adimo/adimo-cookie-consent']],
        '#touchpointId'     => $item->touchpointID,
        '#customCSS'        => $item->customCSS,
        '#customButtonHTML' => $item->customButtonHTML,
        '#language'         => !empty($lang_code) ? $lang_code : 'en',
      ];
    }

    return $elements;
  }

}
