<?php

namespace Drupal\ln_notification\Plugin\DbNotificationProvider;

use Drupal\notification_system_database\DbNotificationProviderPluginBase;

/**
 * @DbNotificationProvider (
 *   id = "ln_notification",
 *   label = @Translation("Lightnest Notification"),
 *   description = @Translation("Generates custom Lightnest notifications.")
 * )
 */
class LnNotificationProvider extends DbNotificationProviderPluginBase {
  public function getTypes() {
    // We just have a single static type.
    return [
      'fcm'
    ];
  }
}
