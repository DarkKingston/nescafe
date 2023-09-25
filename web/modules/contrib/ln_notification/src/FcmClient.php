<?php

namespace Drupal\ln_notification;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Drupal\ln_notification\Entity\FCMSubscriptionInterface;
use Drupal\firebase\Service\FirebaseMessageService;
use Drupal\firebase\Service\FirebaseTopicManagerService;
use GuzzleHttp\ClientInterface;


use function ceil;

/**
 * Service that can be used to store subscriptions and send notifications.
 */
class FcmClient {

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
   * The module configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * A storage of the "fcm_subscription" entities.
   *
   * @var \Drupal\ln_notification\Entity\FCMSubscriptionStorage
   */
  protected $storage;

  /**
   * Drupal\firebase\Service\FirebaseMessageService definition.
   *
   * @var \Drupal\firebase\Service\FirebaseMessageService
   */
  protected $firebaseMessage;

  /**
   * Drupal\firebase\Service\FirebaseTopicManagerService definition.
   *
   * @var \Drupal\firebase\Service\FirebaseTopicManagerService
   */
  protected $firebaseTopicManager;

  /**
   * Drupal\Core\File\FileUrlGeneratorInterface definition.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a FcmClient object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factoy.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    ClientInterface $client,
    FirebaseMessageService $firebase_message_service,
    FirebaseTopicManagerService $firebase_topic_service,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_factory->get('ln_notification');
    $this->config = $config_factory->get('ln_notification.settings');
    $this->client = $client;
    $this->storage = $this->entityTypeManager->getStorage(FCMSubscriptionInterface::ENTITY_TYPE);
    $this->firebaseMessage = $firebase_message_service;
    $this->firebaseTopicManager = $firebase_topic_service;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Sends the notification to the given user.
   *
   * @param int $uid
   *   The ID of a Drupal user.
   *
   * @throws \ErrorException
   */
  public function sendToUser(int $uid, $title, $message, $link): void {
    $fcm_subscriptions = $this->storage->loadByUserId($uid);
    foreach ($fcm_subscriptions as $subscription) {
      $target = ['tokens' => $subscription->getToken()];
      $this->sendNotification($target, $title, $message, $link);
    }
  }

  /**
   * Sends the notification to a given topic
   *
   * @param string $topic
   *   Machine name of the topic
   *
   * @throws \ErrorException
   */
  public function sendToTopic($topic, $title, $message, $link): void {
    $target = ['topics' => $topic];
    $this->sendNotification($target, $title, $message, $link);
  }

  /**
   * Function send message to firebase.
   * @param array $target
   *  Associative array with the target of the notification. Allowed keys:
   *  - 'tokens' string|array with individual tokens to send notification to
   *  - 'topics' string|array with machine names of topics to send notification to
   */
  public function sendNotification($target, $title, $message, $url = NULL, $option = []) {

    global $base_url;
    global $base_path;

    if (empty($url)) {
      $url = $base_url . $base_path;
    }

    // Note: this is the image that is used for the manifest file.
    $icon = !empty($option['image']) ? $option['image'] : theme_get_setting('logo.path');
    $icon = $this->fileUrlGenerator->generateAbsoluteString($icon);

    $messageService = $this->firebaseMessage;

    $tokens = $target['tokens'] ?? null;
    if (!empty($tokens)) {
      $messageService->setRecipients($tokens);
    }

    $topics = $target['topics'] ?? null;
    if (!empty($topics)) {
      $messageService->setTopics($topics);
    }

    $messageService->setNotification([
      'title' => $title,
      'body' => $message,
      'badge' => 1,
      'image' => $icon,
      'sound' => 'default',
    ]);
    
    $messageService->setData([
      'open_url' => $url ?? Url::fromRoute('<front>')->setAbsolute(true)->toString(),
    ]);

    $messageService->setOptions(['priority' => 'high']);
    $response = $messageService->send();

    return $response;
  }

  public function getFirebaseConfig() {
    $settings = [
      'apiKey' => $this->config->get('firebase_apiKey_id'),
      'authDomain' => $this->config->get('firebase_project_id') . '.firebaseapp.com',
      'projectId' => $this->config->get('firebase_project_id'),
      'storageBucket' => $this->config->get('firebase_project_id') . '.appspot.com',
      'databaseURL' => 'https://' . $this->config->get('firebase_project_id') . '.firebaseio.com',
      'messagingSenderId' => $this->config->get('firebase_sender_id'),
      'appId' => $this->config->get('firebase_app_id'),
    ];
    if (!empty($this->config->get('firebase_measurement_id'))) {
      $settings['measurementId'] = $this->config->get('firebase_measurement_id');
    }
    return $settings;
  }

  public function getFirebaseSetting($key) {
    return $this->config->get($key);
  }

  public function subscribeToTopic($topic, $token) {
    $this->firebaseTopicManager->processTopicSubscription($topic, $token, FirebaseTopicManagerService::SUBSCRIBE_ENDPOINT);
  }

  public function getDefaultTopic() {

    // We generate default subscription topic from $base_url
    global $base_url;

    // Remove http protocol
    $topic = preg_replace('/https?:\/\//i', '', $base_url);

    // Generate machine name from $base_url
    // @see \Drupal\Core\Block\BlockBase::getMachineNameSuggestion()
    // @see \Drupal\system\MachineNameController::transliterate()
    $topic = \Drupal::transliteration()->transliterate($topic, LanguageInterface::LANGCODE_DEFAULT, '_');
    $topic = mb_strtolower($topic);
    $topic = preg_replace('@[^a-z0-9_.]+@', '_', $topic);

    return $topic;
  }

}
