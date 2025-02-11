<?php

namespace Drupal\notification_system_dispatch\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\State\StateInterface;
use Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager;

/**
 * Handle queueing of notifications.
 */
class NotificationDispatcherService {

  /**
   * The user settings service.
   *
   * @var \Drupal\notification_system_dispatch\Service\UserSettingsService
   */
  protected $userSettingsService;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The queue which holds the dispatch jobs.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * A list of all available dispatchers.
   *
   * @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface[]
   */
  protected ?array $dispatchers;

  /**
   * The dispatcher module settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructs a NotificationDispatcherService instance.
   *
   * @param \Drupal\notification_system_dispatch\Service\UserSettingsService $userSettingsService
   *   The user settings service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The QueueFactory service.
   * @param \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager
   *   The NotificationSystemDispatcherPluginManager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(UserSettingsService $userSettingsService, StateInterface $state, QueueFactory $queueFactory, NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager, ConfigFactoryInterface $config_factory) {
    $this->userSettingsService = $userSettingsService;
    $this->state = $state;
    $this->config = $config_factory->get('notification_system_dispatch.settings');
    $this->dispatchers = $notificationSystemDispatcherPluginManager->getDefinitions();
    $this->queue = $queueFactory->get('notification_system_dispatch');
    $this->queue->createQueue();
  }

  /**
   * Create notification dispatch jobs.
   *
   * One item in the queue for each dispatcher that the user has enabled.
   * Also checks if a user is whitelisted.
   *
   * @param \Drupal\notification_system\model\NotificationInterface[] $notifications
   *   The notifications that should be dispatched.
   * @param int $userId
   *   The id of the user.
   */
  public function queue(array $notifications, $userId) {
    $whitelistEnabled = $this->state->get('notification_system_dispatch.enable_whitelist');
    $whitelist = $this->state->get('notification_system_dispatch.whitelist');

    // If whitelist mode is enabled, allow only users of the whitelist.
    if ($whitelistEnabled === 1) {
      if (!is_array($whitelist)) {
        return;
      }

      if (!in_array($userId, $whitelist)) {
        return;
      }
    }

    $alreadyDispatchedTo = FALSE;


    // Bypass for forced notifications.
    // They should always be sent to the "forced dispatcher".
    // There cannot be multiple notifications, as forced notifications are not
    // bundled.
    $forcedDispatcher = $this->config->get('forced_dispatcher');
    if ($forcedDispatcher !== '' && $notifications[0]->isForced()) {
      // Add an item to the dispatcher queue.
      $item = new \stdClass();
      $item->user = $userId;
      $item->dispatcher = $forcedDispatcher;
      $item->notifications = [];

      $data = new \stdClass();
      $data->notification_provider = $notifications[0]->getProvider();
      $data->notification_id = $notifications[0]->getId();
      $item->notifications[] = $data;

      $this->queue->createItem($item);

      $alreadyDispatchedTo = $forcedDispatcher;
    }


    foreach ($this->dispatchers as $dispatcher) {
      // If a user has disabled a dispatcher, don't create queue items.
      if (!$this->userSettingsService->dispatcherEnabled($dispatcher['id'], $userId)) {
        continue;
      }

      // If a notification was already sent to this dispatcher (for example
      // forced notifications) don't send again.
      if ($alreadyDispatchedTo == $dispatcher['id']) {
        continue;
      }

      // Add an item to the dispatcher queue.
      $item = new \stdClass();
      $item->user = $userId;
      $item->dispatcher = $dispatcher['id'];
      $item->notifications = [];

      foreach ($notifications as $notification) {
        $data = new \stdClass();
        $data->notification_provider = $notification->getProvider();
        $data->notification_id = $notification->getId();
        $item->notifications[] = $data;
      }

      $this->queue->createItem($item);
    }
  }

}
