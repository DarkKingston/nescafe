<?php

namespace Drupal\notification_system_dispatch_webpush\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\notification_system_dispatch\Service\UserSettingsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a pushpopup block.
 *
 * @Block(
 *   id = "notification_system_dispatch_webpush_popup",
 *   admin_label = @Translation("Web Push Popup"),
 *   category = @Translation("Notification System")
 * )
 */
class PopupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The UserSettingsService.
   *
   * @var \Drupal\notification_system_dispatch\Service\UserSettingsService
   */
  protected UserSettingsService $userSettingsService;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, UserSettingsService $user_settings_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userSettingsService = $user_settings_service;
  }


  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('notification_system_dispatch.user_settings'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'info_value' => '<p>' . $this->t('Would you like to enable push notifications in this browser?') . '</p>',
      'info_format' => 'full_html',
      'button_enable' => $this->t('Enable'),
      'button_later' => $this->t('Remind me later'),
      'button_cancel' => $this->t("Don't ask again"),
      'ask_later_days' => 1,
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['info'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Content'),
      '#default_value' => $this->configuration['info_value'],
      '#format' => $this->configuration['info_format'],
      '#required' => TRUE,
    ];

    $form['button_enable'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button: Enable'),
      '#default_value' => $this->configuration['button_enable'],
      '#required' => TRUE,
    ];

    $form['button_later'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button: Ask later'),
      '#default_value' => $this->configuration['button_later'],
      '#required' => TRUE,
    ];

    $form['button_cancel'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Button: Don't ask again"),
      '#default_value' => $this->configuration['button_cancel'],
      '#required' => TRUE,
    ];

    $form['ask_later_days'] = [
      '#type' => 'number',
      '#title' => $this->t("How many days to wait when user clicks later"),
      '#default_value' => $this->configuration['ask_later_days'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['info_value'] = $form_state->getValue('info')['value'];
    $this->configuration['info_format'] = $form_state->getValue('info')['format'];
    $this->configuration['button_enable'] = $form_state->getValue('button_enable');
    $this->configuration['button_later'] = $form_state->getValue('button_later');
    $this->configuration['button_cancel'] = $form_state->getValue('button_cancel');
    $this->configuration['ask_later_days'] = $form_state->getValue('ask_later_days');
  }
  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($this->userSettingsService->dispatcherEnabled('webpush', $account->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    return [
      '#theme' => 'notification_system_dispatch_webpush_popup',
      '#info' => check_markup($config['info_value'], $config['info_format']),
      '#button_enable' => $config['button_enable'],
      '#button_later' => $config['button_later'],
      '#button_cancel' => $config['button_cancel'],
      '#ask_later_days' => $config['ask_later_days'],
      '#attached' => [
        'library' => [
          'notification_system_dispatch_webpush/popup_block',
        ],
      ],
    ];
  }

}
