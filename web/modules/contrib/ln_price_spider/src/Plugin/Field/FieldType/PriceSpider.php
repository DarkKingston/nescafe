<?php
/**
 * @file Contains \Drupal\ln_price_spider\Plugin\Field\FieldType\PriceSpider.
 */

namespace Drupal\ln_price_spider\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'PriceSpider' field type.
 *
 * @FieldType(
 *   id = "field_price_spider",
 *   label = @Translation("PriceSpider BuyNow"),
 *   module = "ln_price_spider",
 *   category = @Translation("Lightnest"),
 *   description = @Translation("Introduce the lightbox id to display a
 *   PriceSpider Buynow button."),
 *   default_widget = "price_spider_widget",
 *   default_formatter = "price_spider_formatter"
 * )
 */
class PriceSpider extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Price Spider button'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();

    return $value === NULL || $value === '';
  }

}
