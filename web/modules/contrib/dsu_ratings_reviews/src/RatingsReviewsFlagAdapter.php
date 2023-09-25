<?php

namespace Drupal\dsu_ratings_reviews;

use Drupal;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RatingsReviewsFlagAdapter.
 */
class RatingsReviewsFlagAdapter implements ContainerInjectionInterface {

  const FLAG_ID_USEFUL = 'dsu_ratings_comment_useful';

  const FLAG_ID_UNUSEFUL = 'dsu_ratings_comment_unuseful';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Returns list of customizable flags.
   *
   * @return string[]
   *   Flags this module controls.
   */
  public function getControlledFlags() {
    return [self::FLAG_ID_USEFUL, self::FLAG_ID_UNUSEFUL];
  }

  /**
   * Implements hook_entity_insert().
   *
   * Un-flags/flags reverse flag for DSU comments.
   */
  public function flaggingEntityInsert(EntityInterface $entity) {
    // Filter out anything that is not a flagging entity.
    if ($entity->getEntityTypeId() != 'flagging') {
      return;
    }
    $flag_id = $entity->get('flag_id')->getValue();
    $flag_id = !empty($flag_id[0]['target_id']) ? $flag_id[0]['target_id'] : NULL;
    if (empty($flag_id)) {
      return;
    }

    // Get the opposite flag id.
    $reverse_flag_id = $this->getReverseFlag($flag_id);
    if (empty($reverse_flag_id)) {
      return;
    }

    // Get the flagging entity and un-flag opposite flag.
    /** @var \Drupal\flag\Entity\Flagging $entity */
    $flaggable_entity = $entity->getFlaggable();
    /** @var \Drupal\flag\FlagServiceInterface $flag_service */
    $flag_service = Drupal::service('flag');
    $reverse_flag = $flag_service->getFlagById($reverse_flag_id);
    try {
      $flag_service->unflag($reverse_flag, $flaggable_entity);
    } catch (LogicException $e) {
    }
  }

  /**
   * Returns a reverse flag identifier if exists for this flag.
   *
   * @param string $flag_id
   *   Id of the flag to find reverse for.
   *
   * @return string
   *   Id of the opposite flag, if any.
   */
  public function getReverseFlag(string $flag_id) {
    $reverse_flag_id = '';
    if ($flag_id === self::FLAG_ID_UNUSEFUL) {
      $reverse_flag_id = self::FLAG_ID_USEFUL;
    }
    elseif ($flag_id === self::FLAG_ID_USEFUL) {
      $reverse_flag_id = self::FLAG_ID_UNUSEFUL;
    }
    return $reverse_flag_id;
  }

}
