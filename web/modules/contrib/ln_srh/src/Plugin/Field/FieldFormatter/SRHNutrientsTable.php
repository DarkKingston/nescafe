<?php


namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\ln_srh\SRHConstants;
use Drupal\taxonomy\TermInterface;

/**
 * Plugin implementation of the 'SRH Nutrients Implode' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_nutrients_table",
 *   label = @Translation("SRH Nutrients Table"),
 *   field_types = {
 *     "entity_reference_revisions",
 *   }
 * )
 */
class SRHNutrientsTable extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $header = [];
    $rows = [];
    foreach ($items as $item){
      /** @var  ContentEntityInterface $entity */
      $entity = $item->entity;
      if (!$entity) {
        continue;
      }
      $quantity = 0;
      if($entity->hasField('field_srh_nutrient_quantity') && !$entity->get('field_srh_nutrient_quantity')->isEmpty()){
        $quantity = $entity->get('field_srh_nutrient_quantity')->getString();
      }
      if($quantity > 0){
        if($entity->hasField('field_srh_nutrient') && !$entity->get('field_srh_nutrient')->isEmpty()){
          /** @var TermInterface $nutrientTerm */
          $nutrientTerm = $entity->get('field_srh_nutrient')->entity;
          // Get term in the correct language.
          if ($nutrientTerm->isTranslatable() && $nutrientTerm->hasTranslation($langcode)) {
            $nutrientTerm = $nutrientTerm->getTranslation($langcode);
          }
          $displayName = $nutrientTerm->label();

          if($nutrientTerm->hasField('field_srh_display_name') && !$nutrientTerm->get('field_srh_display_name')->isEmpty()){
            $displayName = $nutrientTerm->get('field_srh_display_name')->getString();
          }
          $unit = '';
          if($nutrientTerm->hasField('field_srh_unit_type') && !$nutrientTerm->get('field_srh_unit_type')->isEmpty()){
            /** @var TermInterface $unitTypeTerm */
            $unitTypeTerm = $nutrientTerm->get('field_srh_unit_type')->entity;
            // Get term in the correct language.
            if ($unitTypeTerm->isTranslatable() && $unitTypeTerm->hasTranslation($langcode)) {
              $unitTypeTerm = $unitTypeTerm->getTranslation($langcode);
            }
            $singularUnit = $unitTypeTerm->getName();
            $pluralUnit = $unitTypeTerm->getName();
            if($unitTypeTerm->hasField('field_srh_abbreviation') && !$unitTypeTerm->get('field_srh_abbreviation')->isEmpty()){
              $singularUnit = $unitTypeTerm->get('field_srh_abbreviation')->getString();
            }
            if($unitTypeTerm->hasField('field_srh_plural_abbreviation') && !$unitTypeTerm->get('field_srh_plural_abbreviation')->isEmpty()){
              $pluralUnit = $unitTypeTerm->get('field_srh_plural_abbreviation')->getString();
            }else if($unitTypeTerm->hasField('field_srh_plural_name') && !$unitTypeTerm->get('field_srh_plural_name')->isEmpty()){
              $pluralUnit = $unitTypeTerm->get('field_srh_plural_name')->getString();
            }
            $quantity = round($quantity,1);
            $count = $quantity == 1;
            $unit = \Drupal::translation()
              ->formatPlural($count, "@quantity @singularUnit", '@quantity @pluralUnit', [
                '@singularUnit' => $singularUnit,
                '@pluralUnit' => $pluralUnit,
                '@quantity' => $quantity,
              ]);
          }
          $rows[] = [
            $displayName,
            $unit
          ];

        }
      }
    }
    $elements[] =  [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable =
      $field_definition->getTargetEntityTypeId() == 'node' &&
      in_array($field_definition->getTargetBundle(), [
        SRHConstants::SRH_RECIPE_BUNDLE,
        SRHConstants::SRH_COMPLEMENT_BUNDLE,
      ]) &&
      $field_definition->getName() == 'field_srh_nutrients';
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
