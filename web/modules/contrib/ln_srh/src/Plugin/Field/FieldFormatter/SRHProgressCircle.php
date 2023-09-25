<?php

namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'SRH Quantity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_progress_circle",
 *   label = @Translation("SRH Progress Circle"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float",
 *   }
 * )
 */
class SRHProgressCircle extends FormatterBase{


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'limit' => 'of 100',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit'),
      '#default_value' => $this->getSetting('limit'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $limit = $this->getSetting('limit');
    $summary[] = $this->t('Limit: @limit', ['@limit' => $limit]);
    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $limit = $this->getSetting('limit');
    $elements = [];
    foreach ($items as $delta=>$item){
      $elements[$delta] = [
        '#theme' => 'srh_progress_circle',
        '#value' => $item->getString(),
        '#limit' => $this->t($limit),
      ];
    }
    $elements['#attached']['library'][] = 'ln_srh/main';
    return $elements;
  }

}
