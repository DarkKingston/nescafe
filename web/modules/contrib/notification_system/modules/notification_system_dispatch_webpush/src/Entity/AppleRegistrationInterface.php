<?php

namespace Drupal\notification_system_dispatch_webpush\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining Apple registration entities.
 *
 * @ingroup notification_system_dispatch_webpush
 */
interface AppleRegistrationInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Get the user.
   *
   * @return \Drupal\user\UserInterface
   *   The user of the Apple registration.
   */
  public function getUser();

  /**
   * Set the user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user of the Apple registration.
   *
   * @return \Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationInterface
   *   The called Apple registration entity.
   */
  public function setUser(UserInterface $user);

  /**
   * Gets the Apple registration token.
   *
   * @return string
   *   DeviceToken of the Apple registration.
   */
  public function getDeviceToken();

  /**
   * Sets the Apple registration token.
   *
   * @param string $token
   *   The Apple registration token.
   *
   * @return \Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationInterface
   *   The called Apple registration entity.
   */
  public function setDeviceToken($token);

  /**
   * Gets the Apple registration creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Apple registration.
   */
  public function getCreatedTime();

  /**
   * Sets the Apple registration creation timestamp.
   *
   * @param int $timestamp
   *   The Apple registration creation timestamp.
   *
   * @return \Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationInterface
   *   The called Apple registration entity.
   */
  public function setCreatedTime($timestamp);

}
