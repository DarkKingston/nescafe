<?php

namespace Drupal\dsu_security_user_profile\Routing;

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
    // Change path 'user/{id}/edit to '/my-profile/edit' for current user.
    if ($route = $collection->get('entity.user.edit_form')) {
      $route->setDefault('_controller', '\Drupal\dsu_security_user_profile\Controller\CurrentUserPathsController::alter_user_edit_route');
    }
    // Change path 'user/{uid}' to 'my-profile' for current user.
    if ($route = $collection->get('entity.user.canonical')) {
      $route->setDefault('_controller', '\Drupal\dsu_security_user_profile\Controller\CurrentUserPathsController::userPageView');
    }
  }

}
