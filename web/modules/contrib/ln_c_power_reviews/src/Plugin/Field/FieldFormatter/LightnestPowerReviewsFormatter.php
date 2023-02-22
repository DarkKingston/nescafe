<?php

namespace Drupal\ln_c_power_reviews\Plugin\Field\FieldFormatter;


use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_ln_c_power_rev_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "field_ln_c_power_rev_formatter",
 *   label = @Translation("Power Reviews Formatter"),
 *   description = @Translation("Power Reviews Formatter"),
 *   field_types = {
 *     "field_ln_c_power_rev_type",
 *   }
 * )
 */
class LightnestPowerReviewsFormatter extends FormatterBase {


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'power_rev_disp_type' => [
          'pr_write_review_form',
          'pr_categorysnippet',
          'pr_review_display',
        ],
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [
      'pr_categorysnippet'   => 'Count of reviews',
      'pr_write_review_form' => 'Write a review',
      'pr_review_display'    => 'Star rating',
    ];


    $elements['power_rev_disp_type'] = [
      '#type'          => 'checkboxes',
      '#options'       => $options,
      '#multiple'      => TRUE,
      '#title'         => t('Show Power Reviews Disp Type'),
      '#default_value' => $this->getSetting('power_rev_disp_type'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $settings = $this->getSettings();

    if (!empty($settings['power_rev_disp_type'])) {
      $summary[] = t('Power reviews Display Type', [
        '@power_rev_disp_type' => $settings['power_rev_disp_type'],
      ]);
    }
    else {
      $summary[] = t('Define Power Reviews Display Type');
    }

    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $ln_c_power_rev_disp_type['display_formatter'] = array_filter($this->getSetting('power_rev_disp_type'));
    $html_id = 'ln-c-power-reviews-type-' . rand(0, 999999);
    // Check if the paragraph has your field.
    $ln_c_power_reviews_settings['pr_api_key_read'] = \Drupal::config('ln_c_power_reviews.settings')
      ->get('pr_api_key_read');
    $ln_c_power_reviews_settings['pr_api_key_write'] = \Drupal::config('ln_c_power_reviews.settings')
      ->get('pr_api_key_write');
    $ln_c_power_reviews_settings['pr_merchant_group_id'] = \Drupal::config('ln_c_power_reviews.settings')
      ->get('pr_merchant_group_id');
    $ln_c_power_reviews_settings['pr_merchant_id'] = \Drupal::config('ln_c_power_reviews.settings')
      ->get('pr_merchant_id');
    $ln_c_power_reviews_settings['pr_locale'] = \Drupal::config('ln_c_power_reviews.settings')
      ->get('pr_locale');
    // Get all variables settings and pass in the twig for rendering the component of reviews.
    $ln_c_power_rev_keys['pr_settings_key'] = $ln_c_power_reviews_settings;

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#theme'                      => 'ln-c-power-reviews-type',
        '#html_id'                    => $html_id,
        '#ln_c_power_reviews_page_id' => $item->ln_c_power_reviews_page_id,
        '#ln_c_power_rev_disp_type'   => $ln_c_power_rev_disp_type['display_formatter'],
        '#ln_c_power_reviews_keys'    => $ln_c_power_rev_keys['pr_settings_key'],
        '#attached'                   => [
          'library' => [
            'ln_c_power_reviews/ln-c-power-reviews-library',
          ],
        ],
      ];
    }
    return $element;
  }

}
