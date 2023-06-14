<?php

namespace Drupal\mobile_number_login_otp;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * The AccountAuth Class.
 */
class AccountAuth {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AccountAuth object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, ConfigFactoryInterface $config, TimeInterface $time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->userStorage = $this->entityTypeManager->getStorage('user');
    $this->db = $connection;
    $this->config = $config;
    $this->time = $time;
  }

  /**
   * This will help to authenticate with mobile and OTP generated.
   */
  public function authenticate($extension, $mobile, $otp) {
    $configMain = $this->config->get('mobilenumberlogin.otp.settings');
    $users = $this->userStorage->loadByProperties([
      'field_login_mobile_number' => $extension . '-' . $mobile,
      'status' => 1,
    ]);
    $user = reset($users);
    $time = $this->time->getRequestTime();
    $expiry = (int) !empty($configMain->get('otp_expiry')) ? $configMain->get('otp_expiry') : 300;
    $otpExists = $this->db->select('mobile_otp', 'mo')
      ->fields('mo', ['oid', 'otp'])
      ->condition('mobilenumber', $mobile)
      ->condition('used', 0, '=')
      ->condition('expiry', [$time, $time + $expiry], 'BETWEEN')
      ->execute()->fetchObject();
    if (!empty($otpExists)) {
      if ($otp == $otpExists->otp) {
        $this->db->update('mobile_otp')
          ->fields([
            'used' => 1,
          ])
          ->condition('oid', $otpExists->oid)
          ->execute();
        return $user->id();
      }
    }
    return 0;
  }

}
