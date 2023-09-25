<?php

namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_entity_reference_revision_grid",
 *   label = @Translation("SRH Entity Reference Revision Grid"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class SRHEntityReferenceRevisionGrid extends EntityReferenceRevisionsEntityFormatter implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'wrapper_class' => 'container',
        'column_class' => 'col-md-3',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['wrapper_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set grid wrapper class'),
      '#default_value' => $this->getSetting('wrapper_class'),
    ];

    $elements['column_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set column class'),
      '#default_value' => $this->getSetting('column_class'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $wrapper_class = $this->getSetting('wrapper_class');
    $column_class = $this->getSetting('column_class');
    $summary[] = $this->t('Wrapper class: @container', ['@container' => $wrapper_class]);
    $summary[] = $this->t('Column class: @column', ['@column' => $column_class]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    foreach ($items as $delta=>$item){
      $values = $item->getValue();
      $values['_attributes']['class'][] = $this->getSetting('column_class');
      $items[$delta]->setValue($values);
    }
    $elements = parent::view($items, $langcode);
    $elements['#attributes']['class'][] = $this->getSetting('wrapper_class');

    return $elements;
  }

}
