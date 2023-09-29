<?php

namespace Drupal\change_user_route\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // At the top of the method.
    $myConfig = \Drupal::config('simple.settings');

    // Somewhere later in the method.
    $login = $myConfig->get('login_route');

    // Change path '/user/login' to '/login'.
    if ($route = $collection->get('user.login')) {
      $login = $myConfig->get('login_route');
      if ($login) {
        $route->setPath($login);
      }
    }

    if ($route = $collection->get('user.logout')) {
      $logout_route = $myConfig->get('logout_route');
      if ($logout_route) {
        $route->setPath($logout_route);
      }
    }
    if ($route = $collection->get('user.pass')) {
      $pass_route = $myConfig->get('pass_route');
      if ($pass_route) {
        $route->setPath($pass_route);
      }
    }
    if ($route = $collection->get('user.register')) {
      $regi_route = $myConfig->get('regi_route');
      if ($regi_route) {
        $route->setPath($regi_route);
      }
    }
  }

}
