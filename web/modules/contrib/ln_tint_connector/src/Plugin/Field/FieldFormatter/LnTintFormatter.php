<?php

namespace Drupal\ln_tint_connector\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\ln_tint_connector\LnTintConstants;


/**
 * Defines the 'ln_tint' field formatter.
 *
 * @FieldFormatter(
 *   id = "ln_tint_formatter",
 *   label = @Translation("Tint Formatter"),
 *   field_types = {
 *     "serialized_settings_item"
 *   }
 * )
 */
class LnTintFormatter extends FormatterBase {

  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param string $langcode
   *
   * @return array
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $info = \Drupal::service('extension.list.module')->getExtensionInfo('ln_tint_connector');
    $extension = \Drupal::service('module_handler')->getModule('ln_tint_connector');

    foreach ($items as $delta=>$item) {

      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attached' => [
          'drupalSettings' => [
            'ln_tint_connector' => [
              'data' => [
                'module_name' => $info['name'],
                'module_version' => $info['version'],
              ]
            ],
          ],
        ],
        '#attributes' => [
          'class' => ['tintup', 'tint-social', "tint-mode-{$item->mode}"],
          'data-id' => $item->tint_id,
          'style' => "width:100%;",
        ]
      ];

      if ($item->mode == LnTintConstants::TINT_SELECT_OPTIONS_IFRAME) {
        $elements[$delta]['#attached']['library'][] = 'ln_tint_connector/embed';
        $elements[$delta]['#attributes'] += [
          'data-expand' => 'true',
          'data-personalization-id' => $item->iframe['personalization_id'],
          'data-clickformore' => $item->iframe['pagination_mode'] == LnTintConstants::TINT_IFRAME_SELECT_OPTION_CLICKFORME ? 'true' : NULL,
          'data-paginate' => $item->iframe['pagination_mode'] == LnTintConstants::TINT_IFRAME_SELECT_OPTIONS_PAGINATE ? 'true' : NULL,
          'data-infinitescroll' => $item->iframe['pagination_mode'] == LnTintConstants::TINT_IFRAME_SELECT_OPTIONS_INFINITE ? 'true' : NULL,
          'data-notrack' => 'false',
          'data-tags' => str_replace(' ', '', $item->iframe['tags']),
          'data-count' => $item->iframe['data_count'],
        ];
      }
      else {
        $elements[$delta]['#attached']['library'][] = 'ln_tint_connector/custom';
        $elements[$delta]['#attached']['drupalSettings']['ln_tint_connector'] += [
          'api_url' => LnTintConstants::API_URL,
          'pager_size' => $item->custom['pager_size'],
          'default_avatar' => \Drupal::service('file_url_generator')->generateString($extension->getPath() . DIRECTORY_SEPARATOR . 'img/avatar.png')
        ];
      }
    }

    return $elements;
  }
}
