<?php


namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'SRH Nutrients Implode' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_nutrients_implode",
 *   label = @Translation("SRH Nutrients Implode"),
 *   field_types = {
 *     "entity_reference_revisions",
 *   }
 * )
 */
class SRHNutrientsImplode extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'glue' => ',',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['glue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Glue'),
      '#default_value' => $this->getSetting('glue'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $glue = $this->getSetting('glue');
    $summary[] = $this->t('Glue: @glue', ['@glue' => $glue]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $glue = $this->getSetting('glue');
    $labels = [];
    foreach ($items as $item){
      /** @var  ContentEntityInterface $entity */
      $entity = $item->entity;
      if($entity->hasField('field_srh_nutrient') && !$entity->get('field_srh_nutrient')->isEmpty()){
        $labels[] = ucfirst($entity->get('field_srh_nutrient')->entity->label());
      }
    }
    $elements[] =  [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => implode($glue,$labels),
    ];
    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getTargetEntityTypeId() == 'node' && $field_definition->getTargetBundle() == 'srh_recipe' && $field_definition->getName() == 'field_srh_nutrients';
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
