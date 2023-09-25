<?php

namespace Drupal\ln_notification\Plugin\NotificationSystemDispatcher;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginBase;
use Drupal\ln_notification\FcmClient;
use Drupal\user\UserInterface;
use Drupal\web_push_api\Component\WebPushData;
use Drupal\web_push_api\Component\WebPushNotification;
use Html2Text\Html2Text;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function file_create_url;

/**
 * Plugin implementation of the notification_system_dispatcher.
 *
 * @NotificationSystemDispatcher(
 *   id = "fcm",
 *   label = @Translation("FCM-Notification"),
 *   description = @Translation("Send notifications via FCM. Please note that, in order to work properly, this dispatcher can not be enabled by default.")
 * )
 */
class FcmDispatcher extends NotificationSystemDispatcherPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The TwigEnvironment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected TwigEnvironment $twig;

  /**
   * The DateFormatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private DateFormatterInterface $dateFormatter;

  /**
   * The FcmClient.
   *
   * @var \Drupal\ln_notification\FcmClient
   */
  protected FcmClient $fcmClient;

  /**
   * The FileSystem.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The LibraryDiscovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected LibraryDiscoveryInterface $libraryDiscovery;

  /**
   * The ElementInfoManager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected ElementInfoManagerInterface $elementInfoManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The configuration for this module.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Drupal\ln_notification\FcmClient $fcm_client
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info_manager
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    TwigEnvironment $twig,
    DateFormatterInterface $date_formatter,
    FcmClient $fcm_client,
    FileSystemInterface $file_system,
    LibraryDiscoveryInterface $library_discovery,
    ElementInfoManagerInterface $element_info_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->twig = $twig;
    $this->dateFormatter = $date_formatter;
    $this->fcmClient = $fcm_client;
    $this->fileSystem = $file_system;
    $this->libraryDiscovery = $library_discovery;
    $this->elementInfoManager = $element_info_manager;
    $this->logger = $logger_factory->get('ln_notification');
    $this->config = $config_factory->getEditable('ln_notification.settings');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('twig'),
      $container->get('date.formatter'),
      $container->get('ln_notification'),
      $container->get('file_system'),
      $container->get('library.discovery'),
      $container->get('plugin.manager.element_info'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['firebase_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase endpoint'),
      '#description' => $this->t('Google Firebase Cloud Messaging endpoint.'),
      '#default_value' => $this->config->get('firebase_endpoint'),
      '#required' => TRUE,
    ];
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#description' => $this->t('https://console.firebase.google.com/u/0/project/[your-project]/settings/general'),
    ];
    $form['general']['firebase_project_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase project id'),
      '#description' => $this->t('Google Firebase: Project Settings -> Setting -> Project ID'),
      '#default_value' => $this->config->get('firebase_project_id'),
      '#required' => TRUE,
    ];
    $form['cloud_messaging'] = [
      '#type' => 'details',
      '#title' => $this->t('Cloud Messaging'),
      '#description' => $this->t('https://console.firebase.google.com/u/0/project/[your-project]/settings/cloudmessaging'),
    ];
    $form['cloud_messaging']['firebase_server_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Firebase Server Key'),
      '#description' => $this->t('This is the server key. <em>Do not confuse with API Key</em>'),
      '#default_value' => $this->config->get('firebase_server_key'),
      '#required' => TRUE,
    ];
    $form['cloud_messaging']['firebase_sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase sender id'),
      '#description' => $this->t('Sender id: Project Settings -> Cloud Messaging -> Project credentials -> Sender ID'),
      '#default_value' => $this->config->get('firebase_sender_id'),
      '#required' => TRUE,
    ];
    $form['cloud_messaging']['firebase_vap_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Web Push certificates'),
      '#rows' => 2,
      '#description' => $this->t('Web Push certificates: Project Settings -> Cloud Messaging -> Web configuration -> Key pair'),
      '#default_value' => $this->config->get('firebase_vap_id'),
    ];

    $form['your_apps'] = [
      '#type' => 'details',
      '#title' => $this->t('Your apps - Web apps Information'),
      '#description' => $this->t('https://console.firebase.google.com/u/0/project/[your-project]/settings/general/web'),
    ];
    $form['your_apps']['firebase_apiKey_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase API Key'),
      '#description' => $this->t('Your apps: Project Settings -> General -> Web API Key'),
      '#default_value' => $this->config->get('firebase_apiKey_id'),
    ];
    $form['your_apps']['firebase_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase app Key'),
      '#description' => $this->t('Your apps: Project Settings -> General -> Your apps / App ID'),
      '#default_value' => $this->config->get('firebase_app_id'),
    ];

    $form['your_apps']['firebase_measurement_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase analytics'),
      '#description' => $this->t('If you want to integrate google analytic. Google Firebase Project Settings -> General -> Your apps -> measurementId'),
      '#default_value' => $this->config->get('firebase_measurement_id'),
    ];
    $form['your_apps']['firebase_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Firebase version'),
      '#description' => $this->t('You should use the stable version x.xx.0'),
      '#default_value' => $this->config->get('firebase_version'),
    ];

    $form['firebase_enable_authorized'] = [
      '#type' => 'checkbox',
      '#title' => t('Use only with authorized user'),
      '#description' => t('Checked when project is behind HTTP auth'),
      '#default_value' => $this->config->get('firebase_enable_authorized'),
    ];

    $form['firebase_target'] = [
      '#type' => 'select',
      '#title' => t('Choose the target for all Firebase notifications'),
      '#description' => t('Topic messages are optimized for throughput rather than latency. For fast, secure delivery to single devices or small groups of devices, target messages to registration tokens, not topics. For more information, <a href="https://firebase.google.com/docs/cloud-messaging/js/topic-messaging?authuser=0" target="_blank">read the official docs</a>.'),
      '#default_value' => $this->config->get('firebase_target'),
      '#options' => [
        'TOKEN' => $this->t('User tokens'),
        'TOPIC' => $this->t('Topics'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $values) {

    $this->config
      ->set('firebase_endpoint', $values['firebase_endpoint'])
      ->set('firebase_project_id', $values['general']['firebase_project_id'])
      ->set('firebase_server_key', $values['cloud_messaging']['firebase_server_key'])
      ->set('firebase_sender_id', $values['cloud_messaging']['firebase_sender_id'])
      ->set('firebase_vap_id', $values['cloud_messaging']['firebase_vap_id'])
      ->set('firebase_apiKey_id', $values['your_apps']['firebase_apiKey_id'])
      ->set('firebase_app_id', $values['your_apps']['firebase_app_id'])
      ->set('firebase_measurement_id', $values['your_apps']['firebase_measurement_id'])
      ->set('firebase_version', $values['your_apps']['firebase_version'])
      ->set('firebase_enable_authorized', $values['firebase_enable_authorized'])
      ->set('firebase_target', $values['firebase_target'])
      ->save();

    parent::settingsFormSubmit($values);
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(UserInterface $user, array $notifications) {
    foreach ($notifications as $notification) {
      $this->sendFCM($user, $notification);
    }
  }

  /**
   * Send out notifications via Firebase
   *
   * @param \Drupal\user\UserInterface $user
   *   The recipient.
   * @param \Drupal\notification_system\model\NotificationInterface[] $notification
   *   Notification that should be sent.
   */
  protected function sendFCM(UserInterface $user, $notification) {
    $vars = $this->getVars($notification);

    // Send notification.
    try {
      switch ($this->config->get('firebase_target')) {
        case 'TOPIC':
          $topic = $this->fcmClient->getDefaultTopic();
          $this->fcmClient->sendToTopic($topic, $vars['subject'], $vars['body'], $vars['link']);
          break;
        default:
        $this->fcmClient->sendToUser($user->id(), $vars['subject'], $vars['body'], $vars['link']);
      }
      
      // If we made it thus far, it means everything went well, so:
      // - Grab original notification entity
      $notificationId = $notification->getEntityId();
      $notification_entity = $this->entityTypeManager->getStorage('notification')->load($notificationId);

      // - Update notification, set 'sent' flag to true and save
      $notification_entity->field_ln_notif_sent = true;
      $notification_entity->save();

    }
    catch (\ErrorException $e) {
      $this->logger->warning('Error while sending the notifications to ' . $user->label() . ' (UID ' . $user->id() . ') via FCM. ' . $e->getMessage());
    }
  }

  /**
   * Generates the subject and body of the notification.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user who this notification is for.
   * @param \Drupal\notification_system\model\Notification $notification
   *   Notification to send.
   *
   * @return array
   *   - subject: string
   *   - body: string
   */
  protected function getVars($notification) {

    $direct_link = $notification->getLink();

    if ($direct_link) {
      $direct_link = $direct_link->setAbsolute(TRUE)->toString();
    }

    // Convert body to plain text.
    $bodyText = $notification->getBody();
    $bodyHtml = new Html2Text($bodyText, [
      'width' => 0,
      'links' => 'none',
    ]);

    $title = $notification->getTitle();
    $body = $bodyHtml->getText();

    // Limit length of body and title.
    $maxLength = 200;
    foreach ([$body, $title] as &$item) {
      if (strlen($item) > $maxLength) {
        $item = substr($item, 0, $maxLength);
        
        if (strlen($item) > 0) {
          $item .= ' ...';
        }
      }
    }

    return [
      'subject' => $title,
      'body' => $body,
      'link' => $direct_link,
    ];
  }

}
