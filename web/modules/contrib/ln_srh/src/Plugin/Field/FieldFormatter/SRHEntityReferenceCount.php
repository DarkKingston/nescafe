<?php


namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'SRH Entity Reference Count' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_entity_reference_count",
 *   label = @Translation("SRH Entity Reference Count"),
 *   field_types = {
 *     "entity_reference_revisions",
 *     "entity_reference",
 *   }
 * )
 */
class SRHEntityReferenceCount extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'singular_prefix' => 'Entity',
        'plural_prefix' => 'Entities',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['singular_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Singular Prefix'),
      '#default_value' => $this->getSetting('singular_prefix'),
    ];

    $elements['plural_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plural Prefix'),
      '#default_value' => $this->getSetting('plural_prefix'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $singular_prefix = $this->getSetting('singular_prefix');
    $plural_prefix = $this->getSetting('plural_prefix');
    $summary[] = $this->t('Singular Prefix: @singular_prefix', ['@singular_prefix' => $singular_prefix]);
    $summary[] = $this->t('Plural Prefix: @plural_prefix', ['@plural_prefix' => $plural_prefix]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $singular_prefix = $this->getSetting('singular_prefix');
    $plural_prefix = $this->getSetting('plural_prefix');
    $output = \Drupal::translation()->formatPlural($items->count(), "1 @singular_prefix", '@count @plural_prefix',['@singular_prefix' => $singular_prefix, '@plural_prefix' => $plural_prefix]);
    $elements[] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $output,
    ];
    return $elements;
  }


}
