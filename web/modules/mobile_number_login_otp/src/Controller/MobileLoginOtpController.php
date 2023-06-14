<?php

namespace Drupal\mobile_number_login_otp\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mobile_number_login_otp\GateWay;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * The MobileLoginOtpController Class.
 */
class MobileLoginOtpController extends ControllerBase {
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Database\Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The GateWay service..
   *
   * @var \Drupal\mobile_number_login_otp\GateWay
   */
  protected $gateway;

  /**
   * Obtaining system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new MobileLoginOtpController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, ConfigFactoryInterface $config, LoggerChannelFactoryInterface $factory, GateWay $gateway, TimeInterface $time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->config = $config;
    $this->loggerFactory = $factory;
    $this->gateway = $gateway;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('mobile_number_login_otp.gateway'),
      $container->get('datetime.time')
    );
  }

  /**
   * Function sendSms.
   */
  public function sendSms(Request $request) {
    $mobile = $request->request->get('mobile');
    $extension = $request->request->get('extension');
    $time = $this->time->getRequestTime();
    $expiry = (int) !empty($this->config('mobilenumberlogin.otp.settings')->get('otp_expiry')) ? $this->config('mobilenumberlogin.otp.settings')->get('otp_expiry') : 300;
    if (!empty($mobile)) {
      $userExists = $this->entityTypeManager()->getStorage('user')->loadByProperties(['field_login_mobile_number' => $extension . '-' . $mobile]);
      $userExist = reset($userExists);
      if (empty($userExist)) {
        $user = User::create();
        $user->setUsername('user_' . $mobile);
        $user->setPassword('p@ssw0rd');
        $user->setEmail('email_' . $mobile);
        $user->set('field_login_mobile_number', $extension . '-' . $mobile);
        $user->enforceIsNew();
        $user->activate();
        $user->save();
        $userExist = $user;
      }
      if ($userExist->status->value) {
        $otpExists = $this->connection->select('mobile_otp', 'mo')
          ->fields('mo', ['otp', 'oid', 'otpcount'])
          ->condition('mobilenumber', $mobile)
          ->condition('used', 0, '=')
          ->condition('expiry', [$time, $time + $expiry], 'BETWEEN')
          ->execute()->fetchObject();
        if (!empty($otpExists)) {
          $otp = $otpExists->otp;
          if ($otpExists->otpcount >= $this->config('mobilenumberlogin.otp.settings')->get('otp_max_count')) {
            $status = 3;
          }
          else {
            $status = 2;
            $this->connection->update('mobile_otp')
              ->fields([
                'otpcount' => $otpExists->otpcount + 1,
              ])
              ->condition('oid', $otpExists->oid)
              ->execute();
          }
        }
        else {
          $otp = rand(100000, 999999);
          $this->connection->insert('mobile_otp')
            ->fields([
              'otp' => $otp,
              'mobilenumber' => $mobile,
              'expiry' => $time + $expiry,
              'otpcount' => 1,
              'created' => $time,
            ])
            ->execute();
          $status = 1;
        }
        $mobileOtpSettings = $this->config('mobilenumberlogin.otp.settings');
        $sms = Json::decode($this->gateway->sendSms([ltrim($extension . $mobile, '+')], $mobileOtpSettings->get('login_otp'), [
          'User',
          'Khaga',
          $otp,
          $mobileOtpSettings->get('otp_expiry') / 60,
        ]));
        if (!empty($sms->status_code)) {
          $this->loggerFactory->get('mobile_number_login_otp')->error($sms->message);
        }
      }
      else {
        $status = 4;
      }
    }
    return new JsonResponse(['status' => $status]);
  }

}
