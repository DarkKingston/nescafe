<?php

namespace Drupal\dsu_security_node\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class ConfigRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override whitelabel dsu_security form..
    $config_form_route = $collection->get('dsu_security.settings_form');
    if (!empty($config_form_route)) {
      $config_form_route->setDefaults([
        '_form' => '\Drupal\dsu_security_node\Form\NodeSecuritySettingsForm',
      ]);
      $collection->remove('dsu_security.settings_forms');
      $collection->add('dsu_security.settings_forms', $config_form_route);
    }
  }

}
