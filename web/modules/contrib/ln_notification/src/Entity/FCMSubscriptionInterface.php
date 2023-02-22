<?php

namespace Drupal\ln_notification\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * The definition of a FCM subscription.
 */
interface FCMSubscriptionInterface extends ContentEntityInterface {

  public const ENTITY_TYPE = 'fcm_subscription';

  /**
   * Returns the account of a subscription owner.
   *
   * @return \Drupal\user\UserInterface|null
   *   The account of a subscription owner.
   */
  public function getOwner(): ?UserInterface;

  /**
   * Returns the subscription creation date.
   *
   * @return \DateTimeInterface
   *   The subscription creation date.
   */
  public function getCreatedDate(): \DateTimeInterface;

  /**
   * Returns the subscription last modification date.
   *
   * @return \DateTimeInterface
   *   The subscription last modification date.
   */
  public function getChangedDate(): \DateTimeInterface;

  /**
   * Returns the user agent of a browser the subscription is created for.
   *
   * @return string
   *   The user agent of a browser the subscription is created for.
   */
  public function getUserAgent(): string;

  /**
   * Returns the timezone of a subscription owner.
   *
   * @return \DateTimeZone
   *   The timezone of a subscription owner.
   */
  public function getUserTimeZone(): \DateTimeZone;

  /**
   * @return null|string
   */
  public function getToken(): ?string;

}
