<?php

namespace Drupal\dsu_c_core\Helpers;


use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use function Drupal\Component\Serialization\Yaml;

/**
 * Contains helper functions for manipulating paragraphs.
 */
class ParagraphHelper {
  /**
   * Alters an paragraph widget form element.
   *
   * @param array $element
   *   The widget form element.
   */
  public static function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];
    $fields['section_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Section ID'))
      ->setDescription(t('Allows to create an anchor link to this section of the page.'))
      ->setTranslatable(FALSE)
      ->setRevisionable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->addConstraint('ParagraphUniqueId');

    $fields['css_class'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Custom CSS classes'))
      ->setDescription(t('You can add as many custom css classes as you want separated by spaces.'))
      ->setTranslatable(FALSE)
      ->setRevisionable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->addConstraint('CssClasses');

    $fields['classy'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Classy'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'classy_paragraphs_style')
      ->setSetting('handler', 'classy_group')
      ->setTranslatable(FALSE)
      ->setRevisionable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'classy_group',
      ])
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ]);

    return $fields;
  }


  /**
   * Alters a paragraph widget form element.
   *
   * @param array $element
   *   The widget form element.
   */
  public static function widgetFormAlter(array &$element, FormStateInterface $form_state, $context) {
    if(isset($element['subform']['section_id'])
        || isset($element['subform']['css_class'])
        || isset($element['subform']['classy'])) {
      $element['subform']['advanced'] = [
        '#type' => 'details',
        '#title' => t('Advanced'),
        '#weight' => 99
      ];
      $group = implode('][', array_merge($element['subform']['#parents'], ['advanced']));
      if(isset($element['subform']['section_id'])) {
        $element['subform']['section_id']['#group'] = $group;
      }
      if(isset($element['subform']['css_class'])) {
        $element['subform']['css_class']['#group'] = $group;
      }
      if(isset($element['subform']['classy'])) {
        $element['subform']['classy']['#group'] = $group;
      }
    }
  }
}
