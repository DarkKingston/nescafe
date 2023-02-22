<?php

namespace Drupal\dsu_c_core\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a paragraph field has a unique and valid id value.
 *
 * @Constraint(
 *   id = "ParagraphUniqueId",
 *   label = @Translation("Unique paragraph id constraint", context = "Validation"),
 * )
 */
class ParagraphUniqueIdConstraint extends Constraint {

  public $uniqueMessage = 'The id should be unique for this entity.';
  public $invalidMessage = 'The value %value is not a valid html id.';
}
