<?php

namespace Drupal\notification_system_dispatch_webpush\Plugin\NotificationSystemDispatcher;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
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
use Drupal\notification_system_dispatch_webpush\AppleWebPushClient;
use Drupal\notification_system_dispatch_webpush\WebPushClient;
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
 *   id = "webpush",
 *   label = @Translation("Push-Notification"),
 *   description = @Translation("Send notifications via web push.")
 * )
 */
class WebpushDispatcher extends NotificationSystemDispatcherPluginBase implements ContainerFactoryPluginInterface {

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
   * The WebPushClient.
   *
   * @var \Drupal\notification_system_dispatch_webpush\WebPushClient
   */
  protected WebPushClient $webPushClient;

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
   * The AppleWebPushClient.
   *
   * @var \Drupal\notification_system_dispatch_webpush\AppleWebPushClient
   */
  protected AppleWebPushClient $appleWebPushClient;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Drupal\notification_system_dispatch_webpush\WebPushClient $web_push_client
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info_manager
   * @param \Drupal\notification_system_dispatch_webpush\AppleWebPushClient $apple_web_push_client
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory,
                              TwigEnvironment $twig,
                              DateFormatterInterface $date_formatter,
                              WebPushClient $web_push_client,
                              FileSystemInterface $file_system,
                              LibraryDiscoveryInterface $library_discovery,
                              ElementInfoManagerInterface $element_info_manager,
                              AppleWebPushClient $apple_web_push_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->twig = $twig;
    $this->dateFormatter = $date_formatter;
    $this->webPushClient = $web_push_client;
    $this->fileSystem = $file_system;
    $this->libraryDiscovery = $library_discovery;
    $this->elementInfoManager = $element_info_manager;
    $this->appleWebPushClient = $apple_web_push_client;

    $this->logger = $logger_factory->get('notification_system_dispatch_webpush');
    $this->config = $config_factory->getEditable('notification_system_dispatch_webpush.settings');
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
      $container->get('notification_system_dispatch_webpush'),
      $container->get('file_system'),
      $container->get('library.discovery'),
      $container->get('plugin.manager.element_info'),
      $container->get('notification_system_dispatch_webpush.apple'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['help_text'] = [
      '#type' => 'markup',
      '#markup' => '
        <p>You can use twig to generate the notification title and body, which allows you to use if statements and for loops for the case that multiple notifications will be sent at once (notification bundling)</p>
        <p><strong>Variables:</strong></p>
        <ul>
          <li><em>notifications</em> - A list of notifications.</li>
          <ul>
            <li><em>title</em> - The title of the notification.</li>
            <li><em>body</em> - The body of the notification.</li>
            <li><em>timestamp</em> - The date and time when the notification was created. Uses the short date format.</li>
            <li><em>link</em> - A link to the notification. When clicking, the notification will be marked as read.</li>
            <li><em>direct_link</em> - A direct link to the notification without marking it as read.</li>
          </ul>
        </ul>
      ',
    ];

    $form['subject_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Title Template'),
      '#default_value' => $this->config->get('subject_template'),
      '#rows' => 8,
      '#required' => TRUE,
    ];

    $form['body_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body Template'),
      '#default_value' => $this->config->get('body_template'),
      '#rows' => 20,
      '#required' => TRUE,
    ];

    $form['body_max_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Body Max length (characters)'),
      '#default_value' => $this->config->get('body_max_length') !== NULL ? $this->config->get('body_max_length') : 200,
      '#required' => TRUE,
    ];

    $codeExample = new FormattableMarkup("<code><pre>openssl ecparam -genkey -name prime256v1 -out private.pem
openssl ec -in private.pem -pubout -outform DER|tail -c 65|base64|tr -d '=' |tr '/+' '_-' >> public.key
openssl ec -in private.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-' >> private.key</pre></code>", []);

    $form['vapid_keys'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('VAPID Keys'),
      '#description' => $this->t('Generate a key-pair for <a href="@url"
        target="_blank">Voluntary Application Server Identification (VAPID) for
        Web Push.</a><br>@codeExample', [
          '@url' => 'https://tools.ietf.org/id/draft-ietf-webpush-vapid-03.html',
          '@codeExample' => $codeExample,
        ]
      ),
    ];

    $form['vapid_keys']['vapid_public_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAPID Public Key'),
      '#default_value' => $this->config->get('vapid_public_key'),
      '#required' => TRUE,
    ];

    $form['vapid_keys']['vapid_private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAPID Private Key'),
      '#default_value' => $this->config->get('vapid_private_key'),
      '#required' => TRUE,
    ];

    $form['icon'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Website icon'),
      '#description' => $this->t('Specify an icon that represents your app.<br>
        It is recommended to be 192x192 px and a png file.'),
    ];

    $form['icon']['icon_path'] = [
      '#type' => 'textfield',
      '#title' => t('Website icon path'),
      '#description' => t('Fill in the path to the website icon file, starting from the Drupal root or an uri.'),
      '#default_value' => $this->config->get('icon_path'),
    ];

    $form['icon']['icon_upload'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload website icon'),
      '#description' => t("If you don't have direct file access to the server, use this field to upload your website icon."),
      '#upload_location' => 'public://webpush',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $form['badge'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Badge icon'),
      '#description' => $this->t('Specify an icon that represents your app.<br>
        It is recommended to be 128x128 px and a png file.<br>
        Also note that it may be converted to a monochrome style.'),
    ];

    $form['badge']['badge_path'] = [
      '#type' => 'textfield',
      '#title' => t('Badge icon path'),
      '#description' => t('Fill in the path to the badge icon file, starting from the Drupal root or an uri.'),
      '#default_value' => $this->config->get('badge_path'),
    ];

    $form['badge']['badge_upload'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload badge icon'),
      '#description' => t("If you don't have direct file access to the server, use this field to upload your badge icon."),
      '#upload_location' => 'public://webpush',
      '#upload_validators' => [
        'file_validate_is_image' => [],
      ],
    ];

    $form['apple_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Safari Web Push Support'),
      '#default_value' => $this->config->get('apple_enabled'),
    ];

    $form['apple'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Safari Web Push Support'),
      '#states' => [
        'visible' => [
          ':input[name="configure_dispatcher_webpush[apple_enabled]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['apple']['cert'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Safari Web Push Certificate'),
      '#description' => $this->t('Head over to <a href="@link" target="_blank">this website</a> to learn how to get a push certificate for your website.', [
        '@link' => 'https://pushalert.co/documentation/creating-safari-web-push-certificate',
      ]),
    ];

    $form['apple']['cert']['apple_cert_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to the Safari Web Push Certificate'),
      '#description' => $this->t('Fill in the path to the .p12 file, starting from the Drupal root or an uri.'),
      '#default_value' => $this->config->get('apple_cert_path'),
    ];

    $form['apple']['cert']['apple_cert_upload'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload Safari Web Push Certificate'),
      '#description' => t("If you don't have direct file access to the server, use this field to upload your certificate."),
      '#upload_location' => 'public://webpush',
      '#upload_validators' => [
        'file_validate_extensions' => ['p12'],
      ],
    ];

    $form['apple']['apple_cert_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Safari Web Push Certificate Password'),
      '#description' => $this->t('The password of the .p12 file.'),
      '#default_value' => $this->config->get('apple_cert_password'),
    ];

    $form['apple']['apple_website_push_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website Push ID'),
      '#description' => $this->t('The id you set in the Apple developer portal.'),
      '#default_value' => $this->config->get('apple_website_push_id'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $values) {
    if (is_array($values['icon']['icon_upload']) && count($values['icon']['icon_upload']) > 0) {
      $file = File::load($values['icon']['icon_upload'][0]);
      if ($file instanceof File) {
        $file->setPermanent();
        $file->save();

        $values['icon']['icon_path'] = $file->getFileUri();
      }
    }

    if (is_array($values['badge']['badge_upload']) && count($values['badge']['badge_upload']) > 0) {
      $file = File::load($values['badge']['badge_upload'][0]);
      if ($file instanceof File) {
        $file->setPermanent();
        $file->save();

        $values['badge']['badge_path'] = $file->getFileUri();
      }
    }

    if (is_array($values['apple']['cert']['apple_cert_upload']) && count($values['apple']['cert']['apple_cert_upload']) > 0) {
      $file = File::load($values['apple']['cert']['apple_cert_upload'][0]);
      if ($file instanceof File) {
        $file->setPermanent();
        $file->save();

        $values['apple']['cert']['apple_cert_path'] = $file->getFileUri();
      }
    }

    // Add a leading slash to icon and badge paths that are no uris and don't have one.
    if (strlen($values['icon']['icon_path']) > 0 &&
      !StreamWrapperManager::getScheme($values['icon']['icon_path']) &&
      substr($values['icon']['icon_path'], 0, 1) !== '/') {
      $values['icon']['icon_path'] = '/' . $values['icon']['icon_path'];
    }

    if (strlen($values['badge']['badge_path']) > 0 &&
      !StreamWrapperManager::getScheme($values['badge']['badge_path']) &&
      substr($values['badge']['badge_path'], 0, 1) !== '/') {
      $values['badge']['badge_path'] = '/' . $values['badge']['badge_path'];
    }

    // If some variables have been changed,
    // then need to rebuild site libraries and elements.
    $shouldRebuild = FALSE;

    if ($this->config->get('vapid_public_key') !== $values['vapid_keys']['vapid_public_key']) {
      $shouldRebuild = TRUE;
    }

    if ((bool) $this->config->get('apple_enabled') !== (bool) $values['apple_enabled']) {
      $shouldRebuild = TRUE;
    }

    if ($this->config->get('apple_website_push_id') !== $values['apple']['apple_website_push_id']) {
      $shouldRebuild = TRUE;
    }

    if ($shouldRebuild) {
      $this->libraryDiscovery->clearCachedDefinitions();
      $this->elementInfoManager->clearCachedDefinitions();
    }

    $this->config
      ->set('subject_template', $values['subject_template'])
      ->set('body_template', $values['body_template'])
      ->set('body_max_length', (int) $values['body_max_length'])
      ->set('vapid_public_key', $values['vapid_keys']['vapid_public_key'])
      ->set('vapid_private_key', $values['vapid_keys']['vapid_private_key'])
      ->set('icon_path', $values['icon']['icon_path'])
      ->set('badge_path', $values['badge']['badge_path'])
      ->set('apple_enabled', (bool) $values['apple_enabled'])
      ->set('apple_cert_path', $values['apple']['cert']['apple_cert_path'])
      ->set('apple_cert_password', $values['apple']['apple_cert_password'])
      ->set('apple_website_push_id', $values['apple']['apple_website_push_id'])
      ->save();

    parent::settingsFormSubmit($values);
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(UserInterface $user, array $notifications) {
    $vars = $this->getVars($user, $notifications);

    $this->sendWebPush($user, $notifications, $vars);
    $this->sendAppleWebPush($user, $notifications, $vars);
  }

  /**
   * Send out notifications via Web Push.
   *
   * @param \Drupal\user\UserInterface $user
   *   The recipient.
   * @param \Drupal\notification_system\model\NotificationInterface[] $notifications
   *   An array with one or more notifications that should be sent.
   *   If there are more notifications, bundling is enabled, and the user should
   *   receive only one notification with a summary.
   * @param array $vars
   *   An array containing the following keys:
   *   - subject: The title of the notification
   *   - body: The body of the notification.
   */
  protected function sendWebPush(UserInterface $user, array $notifications, array $vars) {
    // Build push notification.
    $notification = new WebPushNotification($vars['subject']);
    $notification->setLanguage($user->getPreferredLangcode());

    // Add body if exists.
    if (strlen($vars['body']) > 0) {
      $notification->setBody($vars['body']);
    }

    // Build link. When a notification bundle is sent, link to the frontpage.
    $link = NULL;

    if (count($notifications) > 1) {
      $link = new Url('<front>');
      $link->setAbsolute(TRUE);
    }
    else {
      $link = $notifications[0]->getLink();

      if ($link) {
        $link->setAbsolute(TRUE);
      }
    }

    if ($link) {
      $notification->setData(new WebPushData([
        'open_url' => $link->toString(),
      ]));
    }

    // Set badge.
    if ($this->config->get('badge_path') && strlen($this->config->get('badge_path')) > 0) {
      $badge_path = $this->config->get('badge_path');

      if (StreamWrapperManager::getScheme($badge_path)) {
        $notification->setBadge(file_create_url($badge_path));
      }
      else {
        $url = Url::fromUserInput($badge_path);
        $notification->setBadge($url->setAbsolute()->toString());
      }
    }

    // Set icon.
    if ($this->config->get('icon_path') && strlen($this->config->get('icon_path')) > 0) {
      $icon_path = $this->config->get('icon_path');

      if (StreamWrapperManager::getScheme($icon_path)) {
        $notification->setIcon(file_create_url($icon_path));
      }
      else {
        $url = Url::fromUserInput($icon_path);
        $notification->setIcon($url->setAbsolute()->toString());
      }
    }

    // Send notification.
    try {
      $this->webPushClient->sendToUser($user->id(), $notification);
    }
    catch (\ErrorException $e) {
      $this->logger->warning('Error while sending the notifications to ' . $user->label() . ' (UID ' . $user->id() . ') via web push. ' . $e->getMessage());
    }
  }

  /**
   * Send out notifications via Safari Web Push if enabled.
   *
   * @param \Drupal\user\UserInterface $user
   *   The recipient.
   * @param \Drupal\notification_system\model\NotificationInterface[] $notifications
   *   An array with one or more notifications that should be sent.
   *   If there are more notifications, bundling is enabled, and the user should
   *   receive only one notification with a summary.
   * @param array $vars
   *   An array containing the following keys:
   *   - subject: The title of the notification
   *   - body: The body of the notification.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  protected function sendAppleWebPush(UserInterface $user, array $notifications, array $vars) {
    if (!$this->appleWebPushClient->isEnabled()) {
      return;
    }

    $notificationBody = '';
    if (strlen($vars['body']) > 0) {
      $notificationBody = $vars['body'];
    }

    if (count($notifications) > 1) {
      $link = new Url('<front>');
      $link->setAbsolute(TRUE);
    }
    else {
      $link = $notifications[0]->getLink();

      if ($link) {
        $link->setAbsolute(TRUE);
      }
    }

    $notificationLink = NULL;
    if ($link) {
      $notificationLink = urlencode($link->toString());
    }

    $this->appleWebPushClient->sendToUser($user->id(), $vars['subject'], $notificationBody, $notificationLink);
  }

  /**
   * Generates the subject and body of the notification.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user who this notification is for.
   * @param \Drupal\notification_system\model\Notification[] $notifications
   *   An array of notifications to send.
   *
   * @return array
   *   - subject: string
   *   - body: string
   */
  protected function getVars(UserInterface $user, array $notifications) {
    $langcode = $user->getPreferredLangcode();

    $variables = [
      'notifications' => [],
    ];

    foreach ($notifications as $notification) {
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
      $bodyText = $bodyHtml->getText();

      $notificationVariables = [
        'title' => $notification->getTitle(),
        'body' => $bodyText,
        'timestamp' => $this->dateFormatter->format($notification->getTimestamp(), 'medium', '', NULL, $langcode),
        'link' => 'http://example.com',
        'direct_link' => $direct_link,
      ];

      $variables['notifications'][] = $notificationVariables;
    }

    $subjectTemplate = '{% spaceless %}' . $this->config->get('subject_template') . '{% endspaceless %}';
    $bodyTemplate = $this->config->get('body_template');

    $subject = htmlspecialchars_decode($this->twig->renderInline($subjectTemplate, $variables));
    $body = htmlspecialchars_decode($this->twig->renderInline($bodyTemplate, $variables));

    // Limit length of body.
    $maxLength = $this->config->get('body_max_length') !== NULL ? $this->config->get('body_max_length') : 200;
    if (strlen($body) > $maxLength) {
      $body = substr($body, 0, $maxLength);

      if (strlen($body) > 0) {
        $body .= ' ...';
      }
    }

    return [
      'subject' => $subject,
      'body' => $body,
    ];
  }

}
