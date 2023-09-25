<?php

namespace Drupal\ln_campaign;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Campaign entity.
 *
 * @see \Drupal\ln_campaign\Entity\LnCampaign.
 */
class LnCampaignAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ln_campaign\Entity\LnCampaign $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view published campaigns entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit campaigns entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete campaigns entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add campaigns entities');
  }

}
