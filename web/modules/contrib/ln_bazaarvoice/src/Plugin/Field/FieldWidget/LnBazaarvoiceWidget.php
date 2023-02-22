<?php

namespace Drupal\ln_bazaarvoice\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ln_bazaarvoice' widget.
 *
 * @FieldWidget(
 *   id = "ln_bazaarvoice",
 *   label = @Translation("Bazaarvoice"),
 *   field_types = {
 *     "ln_bazaarvoice_id"
 *   }
 * )
 */
class LnBazaarvoiceWidget extends StringTextfieldWidget {
}
