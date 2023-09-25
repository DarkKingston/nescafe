<?php

namespace Drupal\dsu_c_core\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks if a field has a valid css classes value.
 */
class CssClassesConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if ($value->isEmpty()) {
      return;
    }

    foreach (preg_split('/\s+/', $value->value) as $css_class){
      if(!preg_match('/^[A-Za-z-][A-Za-z0-9_:\.-]*/', $css_class)) {
        $this->context->addViolation($constraint->message, [
          '%value' => $css_class,
        ]);
        return;
      }
    }
  }

}
