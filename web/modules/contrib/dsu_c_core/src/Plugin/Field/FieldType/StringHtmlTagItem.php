<?php

namespace Drupal\dsu_c_core\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'string html tag' entity field type.
 *
 * @FieldType(
 *   id = "string_html_tag",
 *   label = @Translation("Text with HTML tag selector"),
 *   description = @Translation("A field containing a plain string value and html tag selector."),
 *   category = "Lightnest",
 *   default_widget = "string_html_tag_widget",
 *   default_formatter = "string_html_tag_formatter"
 * )
 */
class StringHtmlTagItem extends StringItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['html_tag'] = [
      'type' => 'varchar_ascii',
      'length' => 255,
    ];
    $schema['indexes']['html_tag'] = ['html_tag'];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['html_tag'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('HTML tag'))
      ->setRequired(FALSE);

    return $properties;
  }

}
