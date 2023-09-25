<?php

namespace Drupal\ln_bazaarvoice\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'ln_bazaarvoice_id' entity field type.
 *
 * @FieldType(
 *   id = "ln_bazaarvoice_id",
 *   label = @Translation("Bazaarvoice id"),
 *   description = @Translation("A field for store bazaarvoice id values."),
 *   category = @Translation("Lightnest"),
 *   default_widget = "string_textfield",
 *   default_formatter = "ln_bazaarvoice"
 * )
 */
class BazaarvoiceId extends StringItem {

}
