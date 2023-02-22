<?php

namespace Drupal\ln_notification;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\notification_system\Service\NotificationSystem;
use Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager;
use Psr\Log\LoggerInterface;

/**
 * LnNotificationManager service.
 */
class LnNotificationManager {

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Scheduler Logger service object.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The notification_system service.
   *
   * @var \Drupal\notification_system\Service\NotificationSystem;
   */
  protected $notificationSystem;

  /**
   * The plugin.manager.notification_system_dispatcher service.
   *
   * @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager
   */
  protected $notificationSystemDispatcherPluginManager;

  /**
   * Constructs a LnNotificationManager object.
   */
  public function __construct(
    TimeInterface $time,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    NotificationSystem $notification_system,
    NotificationSystemDispatcherPluginManager $plugin_manager_notification_system_dispatcher
  ) {
    $this->time = $time;
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
    $this->notificationSystem = $notification_system;
    $this->notificationSystemDispatcherPluginManager = $plugin_manager_notification_system_dispatcher;
  }

  /**
   * Method description.
   */
  public function processScheduled() {
    $notification_storage = $this->entityTypeManager->getStorage('notification');
    $query = $notification_storage->getQuery()
      ->condition('field_ln_notif_sent', false)
      ->condition('field_ln_notif_scheduled', $this->time->getRequestTime(), '<=')
      ->sort('id');
    
    $query->accessCheck(FALSE);
    $notification_ids = $query->execute();
    
    $notifications = $notification_storage->loadMultiple($notification_ids);

    $logger_variables = [
      '@count' => count($notifications),
    ];

    $this->logger->notice('Found @count Firebase notifications due to be sent', $logger_variables);
    
    $this->notificationSystemDispatcherPluginManager = \Drupal::service('plugin.manager.notification_system_dispatcher');
    
    /** @var \Drupal\user\UserStorageInterface $userStorage */
    $userStorage = $this->entityTypeManager->getStorage('user');

    /**
     * It's mandatory to dispatch a notification to a user, so we set by default 
     * the anonymous user. However, in our case, because we send to topic subscriptions
     * and not to user tokens, this is not really used. If it ever becomes necessary to
     * send messages to individual users, then we would need to populate an array of userIds
     */
    $userId = 0;

    $dispatcherId = 'fcm';
    $notificationProviderId = 'database';
    
    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
    $dispatcher = $this->notificationSystemDispatcherPluginManager->createInstance($dispatcherId);
    
    /** @var \Drupal\user\UserInterface $user */
    $user = $userStorage->load($userId);
    
    $notification_models = [];
    
    foreach ($notifications as $notification) {
      $notification_model = $this->notificationSystem->loadNotification($notificationProviderId, $notification->id());
      if ($notification_model) {
        $notification_models[] = $notification_model;
        $logger_variables = [
          '@id' => $notification_model->getEntityId(),
          '@label' => $notification_model->getTitle(),
        ];
        $this->logger->notice('Attempting to send firebase notification @id - @label', $logger_variables);
      }
    }
    
    if ($dispatcher && $user && count($notification_models) > 0) {
      $dispatcher->dispatch($user, $notification_models);
    }
  }

}
