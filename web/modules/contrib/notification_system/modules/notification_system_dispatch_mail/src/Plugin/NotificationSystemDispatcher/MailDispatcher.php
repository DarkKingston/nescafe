<?php

namespace Drupal\notification_system_dispatch_mail\Plugin\NotificationSystemDispatcher;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginBase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the notification_system_dispatcher.
 *
 * @NotificationSystemDispatcher(
 *   id = "mail",
 *   label = @Translation("Mail"),
 *   description = @Translation("Send notifications via mail.")
 * )
 */
class MailDispatcher extends NotificationSystemDispatcherPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The configuration for this module.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mailManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailManager = $mailManager;
    $this->logger = $loggerChannelFactory->get('notification_system_dispatch_mail');
    $this->config = \Drupal::configFactory()->getEditable('notification_system_dispatch_mail.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail'),
      $container->get('logger.factory')
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
        <p>You can use twig to generate the mail subject and body, which allows you to use if statements and for loops for the case that multiple notifications will be sent at once (notification bundling)</p>
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
      '#title' => $this->t('Subject Template'),
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $values) {
    $this->config
      ->set('subject_template', $values['subject_template'])
      ->set('body_template', $values['body_template'])
      ->save();

    parent::settingsFormSubmit($values);
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(UserInterface $user, array $notifications) {
    $module = 'notification_system_dispatch_mail';
    $key = 'new_notification';
    $to = $user->getEmail();
    $params['notifications'] = $notifications;
    $langcode = $user->getPreferredLangcode();
    $send = TRUE;

    if (!$to) {
      return;
    }

    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    if ($result['result'] !== TRUE) {
      $this->logger->warning('Error while sending the notifications to ' . $to . ' via email.');
    }
  }

}
