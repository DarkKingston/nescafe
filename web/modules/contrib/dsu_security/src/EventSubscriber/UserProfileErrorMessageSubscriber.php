<?php

namespace Drupal\dsu_security\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handle user profile error message.
 */
class UserProfileErrorMessageSubscriber implements EventSubscriberInterface {

  /**
   * Configuration state Drupal Site.
   *
   * @var string
   */
  const USER_PATH_FRAGMENT = "/user/";

  /**
   * Drupal login and register routes.
   *
   * @var array
   */
  const USER_ROUTES_IGNORE = [
    'user.register',
    'user.login',
  ];


  /**
   * Configuration state Drupal Site.
   *
   * @var string
   */
  const EVENT_LISTENER_METHOD = 'RespondForbiddenAsNotFound';

  /**
   * Check forbidden response.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   Event object.
   */
  public function RespondForbiddenAsNotFound(GetResponseForExceptionEvent $event) {

    $request = $event->getRequest();
    $route_name = $request->get('_route');
    $exception = $event->getException();
    if ($exception instanceof AccessDeniedHttpException
        && stripos($request->getPathInfo(), UserProfileErrorMessageSubscriber::USER_PATH_FRAGMENT) !== FALSE
        && !in_array($route_name, UserProfileErrorMessageSubscriber::USER_ROUTES_IGNORE)
    ) {
      $event->setException(new NotFoundHttpException());
    }
  }

  /**
   * Get subscribe event.
   *
   * @return array|mixed
   *   return mixed value.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = [
      UserProfileErrorMessageSubscriber::EVENT_LISTENER_METHOD,
      100,
    ];
    return $events;
  }

}
