<?php

namespace Drupal\ln_notification\Plugin\Menu\LocalAction;

use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Modifies the 'Add Firebase Notification' local action.
 */
class LnNotificationAddLocalAction extends LocalActionDefault {

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $route_match) {
    $options = parent::getOptions($route_match);

    // Add destination on custom block listing.
    $options['query']['destination'] = Url::fromRoute('<current>')->toString();
    return $options;
  }

}
