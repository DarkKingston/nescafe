<?php

namespace Drupal\notification_system_dispatch_webpush;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Drupal\web_push_api\Component\WebPush;
use Drupal\web_push_api\Component\WebPushAuthVapid;
use Drupal\web_push_api\Component\WebPushNotification;
use function ceil;

/**
 * Service that can be used to store subscriptions and send notifications.
 */
class WebPushClient {

  protected const BATCH_SIZE = 500;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $loggerChannel;

  /**
   * The Push API VAPID authorization.
   *
   * @var \Drupal\web_push_api\Component\WebPushAuthVapid
   */
  protected WebPushAuthVapid $webPushAuth;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a WebPushClient object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factoy.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_factory->get('notification_system_dispatch_webpush');
    $this->config = $config_factory->get('notification_system_dispatch_webpush.settings');
    $publicKey = $this->config->get('vapid_public_key');
    $privateKey = $this->config->get('vapid_private_key');
    $this->webPushAuth = new WebPushAuthVapid($publicKey ?? '', $privateKey ?? '');
  }

  /**
   * Sends the notification to given subscriptions.
   *
   * @param \Drupal\web_push_api\Component\WebPush $webpush
   *   The dispatcher.
   * @param \Drupal\web_push_api\Entity\WebPushSubscription[] $subscriptions
   *   The list of subscriptions.
   * @param string $notification
   *   The notification to send.
   *
   * @throws \ErrorException
   */
  public function send(WebPush $webpush, iterable $subscriptions, string $notification): void {
    $storage = $webpush->getSubscriptionsStorage();

    foreach ($subscriptions as $subscription) {
      if (strpos($subscription->getEndpoint(), 'mozilla') > 0) {
        $webpush->setAutomaticPadding(0);
      }

      $webpush->queueNotification($subscription, $notification);
    }

    foreach ($webpush->flush(static::BATCH_SIZE) as $report) {
      if (!$report->isSuccess()) {
        $this->loggerChannel->error('[fail] @report', [
          '@report' => Json::encode($report),
        ]);

        try {
          $storage->deleteByEndpoint($report->getEndpoint());
        }
        catch (\Exception $e) {
          $this->loggerChannel->error(Error::renderExceptionSafe($e));
        }
      }
    }
  }

  /**
   * Sends the notification to all subscriptions.
   *
   * @param \Drupal\web_push_api\Component\WebPushNotification $notification
   *   The notification to send.
   *
   * @throws \ErrorException
   */
  public function sendToAll(WebPushNotification $notification): void {
    $webpush = new WebPush($this->entityTypeManager, $this->webPushAuth);
    $storage = $webpush->getSubscriptionsStorage();
    $count = $storage
      ->getQuery()
      ->count()
      ->execute();

    $batches_count = $count > static::BATCH_SIZE ? ceil($count / static::BATCH_SIZE) : 1;
    $notification_string = (string) $notification;

    for ($batch_number = 0; $batch_number < $batches_count; $batch_number++) {
      $ids = $storage
        ->getQuery()
        ->range($batch_number * static::BATCH_SIZE, static::BATCH_SIZE)
        ->execute();

      $this->send($webpush, $storage->loadMultiple($ids), $notification_string);
    }
  }

  /**
   * Sends the notification to the given user.
   *
   * @param int $uid
   *   The ID of a Drupal user.
   * @param \Drupal\web_push_api\Component\WebPushNotification $notification
   *   The notification to send.
   *
   * @throws \ErrorException
   */
  public function sendToUser(int $uid, WebPushNotification $notification): void {
    $webpush = new WebPush($this->entityTypeManager, $this->webPushAuth);

    $this->send($webpush, $webpush->getSubscriptionsStorage()->loadByUserId($uid), (string) $notification);
  }

}
