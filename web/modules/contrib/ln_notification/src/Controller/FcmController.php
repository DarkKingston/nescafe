<?php

namespace Drupal\ln_notification\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Error;
use Drupal\ln_notification\Entity\FCMSubscriptionInterface;
use Drupal\ln_notification\FcmClient;
use Drupal\notification_system_database\Entity\Notification;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Returns responses for Notification System Dispatch FCM routes.
 */
class FcmController extends ControllerBase {

  /**
   * The list of headers the inbound request must have present.
   */
  public const HEADERS = [
    'Content-Type' => 'application/json',
  ];

  /**
   * A storage of the "fcm_subscription" entities.
   *
   * @var \Drupal\ln_notification\Entity\FCMSubscriptionStorage
   */
  protected $storage;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * The FcmClient.
   *
   * @var \Drupal\ln_notification\FcmClient
   */
  protected FcmClient $fcmClient;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    FileSystemInterface $file_system,
    ModuleExtensionList $extension_list_module,
    AccountInterface $current_user,
    TranslationInterface $string_translation,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    FcmClient $fcm_client
  ) {
    $this->fileSystem = $file_system;
    $this->extensionListModule = $extension_list_module;
    $this->currentUser = $current_user;
    $this->loggerFactory = $logger_factory;
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $this->loggerFactory->get('ln_notification.controller');
    $this->storage = $this->entityTypeManager->getStorage(FCMSubscriptionInterface::ENTITY_TYPE);
    $this->fcmClient = $fcm_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('extension.list.module'),
      $container->get('current_user'),
      $container->get('string_translation'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('ln_notification')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function subscription(Request $request): JsonResponse {
    $errors = $this->validateRequest($request);

    if ($errors->valid()) {
      return static::response(...$errors);
    }

    $body = $errors->getReturn();

    return static::response(...\call_user_func(
      [$this, $request->getMethod() === 'DELETE' ? 'delete' : 'manage'],
      $this->storage->loadByToken($body['token']),
      $body
    ));
  }

  /**
   * Returns the JSON response.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup ...$errors
   *   The list of errors occurred during the request process.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public static function response(TranslatableMarkup ...$errors): JsonResponse {
    return new JsonResponse([
      'errors' => $errors,
    ]);
  }

  /**
   * Returns the request content and yields validation errors.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request to validate.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function validateRequest(Request $request): \Generator {
    foreach (static::HEADERS as $header => $value) {
      if ($request->headers->get($header) !== $value) {
        yield $this->t('The "@header" header must be "@value".', [
          '@header' => $header,
          '@value' => $value,
        ]);
      }
    }

    $content = $request->getContent();
    $body = empty($content) ? [] : Json::decode($content);

    if (empty($body['token'])) {
      yield $this->t('The "token" must not be empty.');
    }

    return $body;
  }

  /**
   * Creates/updates the subscription.
   *
   * @param \Drupal\ln_notification\Entity\FCMSubscriptionInterface|null $subscription
   *   The subscription.
   * @param array $body
   *   The subscription's data.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function manage(?FCMSubscriptionInterface $subscription, array $body): \Generator {
    $subscription = $subscription ?? $this->storage->create();
    $body['uid'] = $this->currentUser->id();

    foreach ($body as $key => $value) {
      $subscription->set($key, $value);
    }

    foreach ($subscription->validate() as $violation) {
      \assert($violation instanceof ConstraintViolationInterface);
      yield $this->t('@property=@message', [
        '@message' => $violation->getMessage(),
        '@property' => $violation->getPropertyPath(),
      ]);
    }

    if (!isset($violation)) {
      try {
        $this->storage->save($subscription);
      }
      catch (\Exception $e) {
        $this->loggerChannel->error(Error::renderExceptionSafe($e));
        yield $this->t('Unable to save the subscription.');
      }
      
      try {
        // Subscribe current subscription to default topic
        $token = $subscription->getToken();
        $topic = $this->fcmClient->getDefaultTopic();
        $this->fcmClient->subscribeToTopic($topic, $token);
      }
      catch(\Exception $e) {
        $this->loggerChannel->error(Error::renderExceptionSafe($e));
        yield $this->t('Unable to subscribe to default topic.');
      }
    }
  }

  /**
   * Removes the subscription.
   *
   * @param \Drupal\ln_notification\Entity\FCMSubscriptionInterface|null $subscription
   *   The subscription.
   *
   * @return \Generator
   *   The list of errors (empty if none).
   */
  protected function delete(?FCMSubscriptionInterface $subscription): \Generator {
    if ($subscription !== NULL) {
      try {
        $this->storage->delete([$subscription]);
      }
      catch (\Exception $e) {
        $this->loggerChannel->error(Error::renderExceptionSafe($e));
        yield $this->t('Unable to delete the subscription.');
      }
    }
  }

  /**
   * Serves the service worker file.
   */
  public function serviceWorker(): CacheableResponse {
    $modulePath = $this->extensionListModule->getPath('ln_notification');
    $serviceWorker = file_get_contents($modulePath . '/js/fcm_serviceworker.js');

    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->setCacheMaxAge(1);

    $firebaseConfig = $this->fcmClient->getFirebaseConfig();

    $search = [
      '/*drupalSettings.serviceworker*/',
      '/*drupalSettings.firebaseVersion*/',
    ];
    
    $replace = [
      'settings.firebaseConfig = ' . Json::encode($firebaseConfig) . ';',
      $this->fcmClient->getFirebaseSetting('firebase_version'),
    ];

    $response = new CacheableResponse(str_replace($search, $replace, $serviceWorker), 200, [
      'Service-Worker-Allowed' => '/',
      'Content-Type' => 'application/javascript',
    ]);

    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

  /**
   * Function to generate manifest.json.
   */
  public function manifest() {
    $config = \Drupal::config('ln_notification.settings');
    $logo = theme_get_setting('logo.url');
    $typeId = exif_imagetype(DRUPAL_ROOT . $logo);
    $type = image_type_to_mime_type($typeId);
    $lang = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $systemSite = \Drupal::config('system.site');
    $manifest = [
      "name" => $systemSite->get('name'),
      "icons" => [
        [
          "src" => $logo,
          "sizes" => "48x48 72x72 96x96 128x128 256x256 512x512",
          "type" => $type ? $type : 'image/png',
          "purpose" => "any",
        ],
      ],
      "start_url" => "/",
      "display" => "standalone",
      "lang" => $lang,
      "gcm_sender_id" => $config->get('firebase_sender_id'),
      // "theme_color" => "#e91e63",
      // "background_color" => '#ffffff',
    ];
    if ($slogan = $systemSite->get('slogan')) {
      $manifest["short_name"] = $slogan;
    }

    $response = new CacheableResponse(
      json_encode($manifest, JSON_UNESCAPED_SLASHES),
      200,
      ['Content-Type' => 'application/json']
    );
    $meta_data = $response->getCacheableMetadata();
    $meta_data->addCacheTags(['manifestjson']);
    $meta_data->addCacheContexts(['languages:language_interface']);
    return $response;

  }
  
  public function form($notification = null) {
    if (empty($notification)) {
      /**
       * Notice the audience of the notification (user_id) is set to the anonymous
       * user by default (uid = 0). This is done in purpose to make sure FCM
       * notifications are not added to the notification_system_dispatch queue
       * 
       * The only requirement is that the FCM Dispatcher must NOT be enabled
       * by default in /admin/config/system/notification-system-dispatch
       * 
       * TODO: Disable FCM checkbox in notification system dispatch settings
       * so it's not possible to enable by default
       * 
       * Also, note it's mandatory to populate the user_id field. Otherwise,
       * notifications with empty user field are removed on cron runs by
       * notification_system_database_cron()
       * 
       * @see Drupal\notification_system_dispatch\Service\NotificationDispatcherService::queue
       */
      $notification = Notification::create([
        'user_id' => [0],
        'provider_id' => 'database',
        'notification_type' => 'fcm',
      ]);
    }
    return $this->entityFormBuilder()->getForm($notification, 'fcm');
  }

}
