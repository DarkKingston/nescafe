<?php

namespace Drupal\ln_srh_full\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\ln_srh_full\SRHFullConstants;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_versions_full",
 *   label = @Translation("SRH Versions Full"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class SRHVersionsFull extends EntityReferenceRevisionsEntityFormatter{

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'current_version_label' => 'Original version',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['current_version_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Current version label'),
      '#default_value' => $this->getSetting('current_version_label'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Current version label: @label', ['@label' => $this->getSetting('current_version_label')]);

    return $summary;
  }


  /**
   * @inerhitDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements=[];
    foreach ($items as $item) {
     $paragraph = $item->entity;
     if(!$paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_TYPE_FIELD)->isEmpty() && $paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_TYPE_FIELD)->entity
       && !$paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_RECIPE_FIELD)->isEmpty() && $paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_RECIPE_FIELD)->entity){
       $elements[] = [
         '#title' => $paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_TYPE_FIELD)->entity->label(),
         '#type' => 'link',
         '#url' => $paragraph->get(SRHFullConstants::SRH_PARAGRAPH_VERSIONS_RECIPE_FIELD)->entity->toUrl(),
       ];
     }
    }

    if(!empty($elements)){
      array_unshift($elements, [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => ['class' => ['current-srh-version']],
        '#value' => $this->t($this->getSetting('current_version_label'))
      ]);

      return [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $elements,
        '#attributes' => ['class' => 'srh-versions-submenu'],
      ];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getTargetEntityTypeId() == 'node' && $field_definition->getTargetBundle() == 'srh_recipe' && $field_definition->getName() == SRHFullConstants::SRH_RECIPE_VERSIONS_FIELD;
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
