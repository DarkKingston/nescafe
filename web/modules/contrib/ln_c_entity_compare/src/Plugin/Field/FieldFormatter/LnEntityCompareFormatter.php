<?php

namespace Drupal\ln_c_entity_compare\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'LN Entity Compare' formatter.
 *
 * @FieldFormatter(
 *   id = "ln_c_entity_compare",
 *   label = @Translation("LN Entity Compare"),
 *   field_types = {
 *     "serialized_settings_item"
 *   }
 * )
 */
class LnEntityCompareFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();
    $paragraph_settings = $entity->getParagraphSettings();
    $available_entities = $entity->getEntitiesAvailableForComparison();
    
    if (empty($available_entities)) {
      // No entities are available for comparison, quit early
      return $element;
    }

    $initial_entities = array_slice($available_entities, 0, $paragraph_settings['number_of_entities'], TRUE);
    $fields = $entity->getFieldsToRender($initial_entities);
    $initial_entities_rendered = $entity->getRenderedFieldsFromEntities($initial_entities, $fields);
    $element[] = [
      '#prefix' => '<div id="' . $entity->getParagraphWrapperId() . '">',
      '#theme' => 'ln_c_entity_compare_tables',
      '#paragraph' => $entity,
      '#settings' => $paragraph_settings,
      '#available_entities' => $available_entities,
      '#rows' => $entity->getRows($initial_entities_rendered, $fields),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          'ln_c_entity_compare/ln_c_entity_compare',
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetBundle() == 'ln_c_entity_compare';
  }

}
