<?php

namespace Drupal\dsu_c_core\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a field has a valid css classes value.
 *
 * @Constraint(
 *   id = "CssClasses",
 *   label = @Translation("Valid css classes", context = "Validation"),
 * )
 */
class CssClassesConstraint extends Constraint {
  public $message = 'The value %value is not a valid css class.';
}
