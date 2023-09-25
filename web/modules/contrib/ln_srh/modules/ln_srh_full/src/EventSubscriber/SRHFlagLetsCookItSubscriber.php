<?php

namespace Drupal\ln_srh_full\EventSubscriber;

use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\Core\Cache\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\ln_srh_full\SRHFullConstants;

/**
 * Class SRHFlagLetsCookItSubscriber.
 */
class SRHFlagLetsCookItSubscriber implements EventSubscriberInterface {

  /**
   * Invalidate cache when a "Let's cook it" flag is added.
   *
   * @param \Drupal\flag\Event\FlaggingEvent $event
   *   The flagging event.
   */
  public function invalidateCache(FlaggingEvent $event) {
    $flagging = $event->getFlagging();
    $flag = $flagging->getFlag();

    if ($flag->id() == SRHFullConstants::SRH_LETS_COOK_IT_FLAG_ID) {
      $entity = $flagging->getFlaggable();
      $cacheTag = 'flag:' . $flag->id() . ':' . $entity->id();
      Cache::invalidateTags([$cacheTag]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    if (class_exists('Drupal\flag\Event\FlagEvents')) {
      $events[FlagEvents::ENTITY_FLAGGED][] = ['invalidateCache'];
    }
    return $events;
  }

}
