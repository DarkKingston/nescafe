<?php

namespace Drupal\notification_system_dispatch\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface;

/**
 * Provides a Notification System Dispatch form.
 */
class UserSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'notification_system_dispatch_usersettings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\notification_system_dispatch\Service\UserSettingsService $userSettingsService */
    $userSettingsService = Drupal::service('notification_system_dispatch.user_settings');

    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager */
    $notificationSystemDispatcherPluginManager = Drupal::service('plugin.manager.notification_system_dispatcher');

    $pluginDefinitions = $notificationSystemDispatcherPluginManager->getDefinitions();

    foreach ($pluginDefinitions as $definition) {
      /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
      $dispatcher = $notificationSystemDispatcherPluginManager->createInstance($definition['id']);

      $form['dispatcher_' . $dispatcher->id()] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Receive notifications via @dispatcher', [
          '@dispatcher' => $this->t($dispatcher->label(), [], [
            'context' => 'notification_system dispatcher label',
          ]),
        ]),
        '#default_value' => $userSettingsService->dispatcherEnabled($dispatcher->id()),
        '#ajax' => [
          'callback' => '::autosave',
          'event' => 'change',
          'wrapper' => 'ajax_placeholder',
          'progress' => [
            'type' => 'throbber',
            'message' => '',
          ],
        ],
      ];
    }

    $enableBundling = $this->config('notification_system_dispatch.settings')->get('enable_bundling');

    if ($enableBundling) {
      $form['send_mode'] = [
        '#type' => 'select',
        '#title' => $this->t('When do you want to receive the notifications?'),
        '#options' => [
          NotificationSystemDispatcherInterface::SEND_MODE_IMMEDIATELY => $this->t('Immediately'),
          NotificationSystemDispatcherInterface::SEND_MODE_DAILY => $this->t('Daily summary'),
          NotificationSystemDispatcherInterface::SEND_MODE_WEEKLY => $this->t('Weekly summary'),
        ],
        '#default_value' => $userSettingsService->getSendMode(),
        '#ajax' => [
          'callback' => '::autosave',
          'event' => 'change',
          'wrapper' => 'ajax_placeholder',
          'progress' => [
            'type' => 'throbber',
            'message' => '',
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * Autosave callback for the form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return string[]
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function autosave(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\notification_system_dispatch\Service\UserSettingsService $userSettingsService */
    $userSettingsService = Drupal::service('notification_system_dispatch.user_settings');

    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager */
    $notificationSystemDispatcherPluginManager = Drupal::service('plugin.manager.notification_system_dispatcher');

    $triggering_element = $form_state->getTriggeringElement();

    if (str_starts_with($triggering_element['#name'], 'dispatcher_')) {
      $pluginId = str_replace('dispatcher_', '', $triggering_element['#name']);

      /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
      $dispatcher = $notificationSystemDispatcherPluginManager->createInstance($pluginId);

      $userSettingsService->setDispatcherEnabled($dispatcher->id(), $triggering_element['#value']);
    }

    if ($triggering_element['#name'] === 'send_mode') {
      $enableBundling = $this->config('notification_system_dispatch.settings')->get('enable_bundling');

      if ($enableBundling) {
        $sendMode = $triggering_element['#value'];
        $userSettingsService->setSendMode($sendMode);
      }
    }

    return [
      '#markup' => '<div class="hidden">Saved</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
