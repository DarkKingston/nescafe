<?php

namespace Drupal\ln_notification\Entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the entity storage.
 *
 * @method FCMSubscriptionInterface create(array $values = [])
 * @method FCMSubscriptionInterface|null load($id)
 * @method FCMSubscriptionInterface|null loadRevision($revision_id)
 * @method FCMSubscriptionInterface|null loadUnchanged($id)
 * @method FCMSubscriptionInterface[] loadMultiple(array $ids = NULL)
 * @method FCMSubscriptionInterface[] loadByProperties(array $values = [])
 * @method FCMSubscriptionInterface[] loadMultipleRevisions(array $revision_ids)
 */
class FCMSubscriptionStorage extends SqlContentEntityStorage {

  /**
   * Returns the FCM subscription, loaded by the given token.
   *
   * @param string $token
   *   The FCM token.
   *
   * @return \Drupal\ln_notification\Entity\FCMSubscriptionInterface|null
   *   The subscription.
   *
   * @link https://www.w3.org/TR/push-api/#dfn-push-token
   */
  public function loadByToken(string $token): ?FCMSubscriptionInterface {
    $list = $this->loadByProperties(['token' => $token]);

    return empty($list) ? NULL : \reset($list);
  }

  /**
   * Returns FCM subscriptions for a user account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Generator|\Drupal\ln_notification\Entity\FCMSubscriptionInterface[]
   *   The list of subscriptions.
   */
  public function loadByUserAccount(AccountInterface $account): \Generator {
    yield from $this->loadByUserId($account->id());
  }

  /**
   * Returns FCM subscriptions for a user account.
   *
   * @param int $uid
   *   The ID of a user account.
   *
   * @return \Generator|\Drupal\ln_notification\Entity\FCMSubscriptionInterface[]
   *   The list of subscriptions.
   */
  public function loadByUserId(int $uid): \Generator {
    yield from $this->loadByProperties([
      'uid' => $uid,
    ]);
  }

  /**
   * Removes a subscription with the given token.
   *
   * @param string $token
   *   The FCM token.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   When the operation fails.
   */
  public function deleteByToken(string $token): void {
    $this->delete($this->loadByProperties(['token' => $token]));
  }

  /**
   * Removes user's FCM subscriptions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   When the operation fails.
   */
  public function deleteByUserAccount(AccountInterface $account): void {
    $this->deleteByUserId($account->id());
  }

  /**
   * Removes user's FCM subscriptions.
   *
   * @param int $uid
   *   The ID of a user account.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   When the operation fails.
   */
  public function deleteByUserId(int $uid): void {
    $this->delete(\iterator_to_array($this->loadByUserId($uid)));
  }

}
