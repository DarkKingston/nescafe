<?php

namespace Drupal\ln_seo_hreflang\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Path\Plugin\Validation\Constraint\ValidPathConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for validating clean system paths.
 */
class ValidCleanPathConstraintValidator extends ValidPathConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!isset($value)) {
      return;
    }

    if($value !== parse_url($value, PHP_URL_PATH)){
      $this->context->addViolation($constraint->message, [
        '%link_path' => $value,
      ]);
    }else{
      parent::validate($value, $constraint);
    }
  }

}
