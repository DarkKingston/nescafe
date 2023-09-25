<?php

namespace Drupal\dsu_security\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Remove X generator subscriber.
 */
class RemoveXGeneratorSubscriber implements EventSubscriberInterface {

  /**
   * Remove subscriber.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Event object.
   */
  public function RemoveXGenerator(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->remove('X-Generator');
  }

  /**
   * Get subscribe event.
   *
   * @return array|mixed
   *   return mixed value.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['RemoveXGenerator', -10];
    return $events;
  }

}
