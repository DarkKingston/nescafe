<?php

namespace Drupal\ln_seo_hreflang\Plugin\Validation\Constraint;

use Drupal\Core\Path\Plugin\Validation\Constraint\ValidPathConstraint;

/**
 * Validation constraint for valid clean system paths.
 *
 * @Constraint(
 *   id = "ValidCleanPath",
 *   label = @Translation("Valid clean path.", context = "Validation"),
 * )
 */
class ValidCleanPathConstraint extends ValidPathConstraint {

}
