<?php

namespace Drupal\dsu_c_core\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dsu_c_core\Services\CCoreUtilsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a paragraph id is valid and unique for the entity it belongs to.
 */
class ParagraphUniqueIdConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The ccore utils service.
   *
   * @var \Drupal\dsu_c_core\Services\CCoreUtilsInterface
   */
  protected $ccoreUtils;

  /**
   * Creates a new ParagraphUniqueIdConstraintValidator instance.
   *
   * @param \Drupal\dsu_c_core\Services\CCoreUtilsInterface $ccore_utils
   *   The ccore utils service.
   */
  public function __construct(CCoreUtilsInterface $ccore_utils) {
    $this->ccoreUtils = $ccore_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dsu_c_core.utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if ($value->isEmpty()) {
      return;
    }
    /** @var \Drupal\paragraphs\ParagraphInterface $entity */
    $entity = $value->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    if($entity_type_id != 'paragraph'){
      return;
    }

    if(!preg_match('/^[A-Za-z-][A-Za-z0-9_:\.-]*/', $value->value)) {
      $this->context->addViolation($constraint->invalidMessage, [
        '%value' => $value->value,
      ]);
      return;
    }
    $parent_entity = $this->ccoreUtils->getParagraphRealParentEntity($entity);
    $unique_ids = &drupal_static("paragraph_unique_id_{$parent_entity->id()}", []);
    if(isset($unique_ids[$value->value])){
      if($unique_ids[$value->value] != $entity->id()){
        $this->context->addViolation($constraint->uniqueMessage);
      }
    }else{
      $unique_ids[$value->value] = $entity->id();
    }
  }

}
