<?php

namespace Drupal\notification_system_dispatch\Form;

use Drupal;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Notification System Dispatch settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'notification_system_dispatch_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'notification_system_dispatch.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = Drupal::service('state');

    /** @var \Drupal\user\UserStorageInterface $userStorage */
    $userStorage = Drupal::service('entity_type.manager')->getStorage('user');

    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager */
    $notificationSystemDispatcherPluginManager = Drupal::service('plugin.manager.notification_system_dispatcher');


    $form['enable_bundling'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable bundling'),
      '#description' => $this->t('If enabled, users can select if they want to receive their notifications immediately, daily or weekly.'),
      '#default_value' => $this->config('notification_system_dispatch.settings')->get('enable_bundling') ?: FALSE,
    ];

    $form['enable_whitelist'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable whitelist'),
      '#description' => $this->t('For testing purposes you can enable a whitelist. Then notifications will only be sent for a given list of users.'),
      '#default_value' => $state->get('notification_system_dispatch.enable_whitelist'),
    ];

    $users = [];
    $whitelist = $state->get('notification_system_dispatch.whitelist');
    if ($whitelist) {
      $users = $userStorage->loadMultiple($whitelist);
    }

    $form['whitelist'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Allowed users'),
      '#description' => $this->t('Enter a comma separated list uf users who should receive notifications.'),
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#default_value' => $users,
      '#states' => [
        'visible' => [
          ':input[name="enable_whitelist"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];


    $dispatcherDefinitions = $notificationSystemDispatcherPluginManager->getDefinitions();

    $options = [];

    foreach ($dispatcherDefinitions as $dispatcherDefinition) {
      $options[$dispatcherDefinition['id']] = $dispatcherDefinition['label'];
    }

    $form['default_enabled_dispatchers'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default Dispatchers'),
      '#description' => $this->t('Select the dispatchers that should be enabled by default.'),
      '#default_value' => $this->config('notification_system_dispatch.settings')->get('default_enabled_dispatchers') ?: [],
      '#options' => $options,
    ];

    // Add descriptions.
    foreach ($options as $key => $option) {
      $form['default_enabled_dispatchers'][$key]['#description'] = $dispatcherDefinitions[$key]['description'];
    }

    $options = array_merge(['' => $this->t('- Disabled -')], $options);

    $form['forced_dispatcher'] = [
      '#type' => 'select',
      '#title' => $this->t('Dispatcher for forced notifications'),
      '#description' => $this->t('Notifications can be marked as "forced". Forced notifications will bypass the user settings and will always be sent to the dispatcher selected here, immediately. Regardless if the user has disabled the dispatcher. If no dispatcher is selected here, the notifications will be sent like any other notification.'),
      '#default_value' => $this->config('notification_system_dispatch.settings')->get('forced_dispatcher') ?: '',
      '#options' => $options,
    ];


    foreach ($dispatcherDefinitions as $dispatcherDefinition) {
      try {
        /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
        $dispatcher = $notificationSystemDispatcherPluginManager->createInstance($dispatcherDefinition['id']);

        $dispatcherForm = $dispatcher->settingsForm();

        if ($dispatcherForm && count($dispatcherForm) > 0) {
          $form['configure_dispatcher_' . $dispatcher->id()] = [
            '#type' => 'details',
            '#title' => $this->t('Configure %name dispatcher', [
              '%name' => $dispatcher->label(),
            ]),
            '#collapsible' => TRUE,
            '#tree' => TRUE,
          ];

          $form['configure_dispatcher_' . $dispatcher->id()] = $form['configure_dispatcher_' . $dispatcher->id()] + $dispatcherForm;
        }
      }
      catch (PluginException $e) {
        // Plugin instance could not be created...
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager */
    $notificationSystemDispatcherPluginManager = Drupal::service('plugin.manager.notification_system_dispatcher');

    $dispatcherDefinitions = $notificationSystemDispatcherPluginManager->getDefinitions();

    foreach ($dispatcherDefinitions as $dispatcherDefinition) {
      try {
        /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
        $dispatcher = $notificationSystemDispatcherPluginManager->createInstance($dispatcherDefinition['id']);

        $dispatcherForm = $dispatcher->settingsForm();

        if ($dispatcherForm && count($dispatcherForm) > 0) {
          $dispatcher->settingsFormValidate($form, $form_state);
        }
      }
      catch (PluginException $e) {
        // Plugin instance could not be created...
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = Drupal::service('state');

    /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherPluginManager $notificationSystemDispatcherPluginManager */
    $notificationSystemDispatcherPluginManager = Drupal::service('plugin.manager.notification_system_dispatcher');

    $userIds = [];
    if (is_array($form_state->getValue('whitelist'))) {
      foreach ($form_state->getValue('whitelist') as $item) {
        $userIds[] = $item['target_id'];
      }
    }

    $state->set('notification_system_dispatch.enable_whitelist', $form_state->getValue('enable_whitelist'));
    $state->set('notification_system_dispatch.whitelist', $userIds);

    $defaultEnabledDispatchers = [];
    foreach ($form_state->getValue('default_enabled_dispatchers') as $key => $value) {
      if ($value !== 0) {
        $defaultEnabledDispatchers[] = $key;
      }
    }

    $this->config('notification_system_dispatch.settings')
      ->set('enable_bundling', $form_state->getValue('enable_bundling'))
      ->set('default_enabled_dispatchers', $defaultEnabledDispatchers)
      ->set('forced_dispatcher', $form_state->getValue('forced_dispatcher'))
      ->save();

    $dispatcherDefinitions = $notificationSystemDispatcherPluginManager->getDefinitions();

    foreach ($dispatcherDefinitions as $dispatcherDefinition) {
      try {
        /** @var \Drupal\notification_system_dispatch\NotificationSystemDispatcherInterface $dispatcher */
        $dispatcher = $notificationSystemDispatcherPluginManager->createInstance($dispatcherDefinition['id']);

        $dispatcherForm = $dispatcher->settingsForm();

        if ($dispatcherForm && count($dispatcherForm) > 0) {
          $values = $form_state->getValues();
          if (array_key_exists('configure_dispatcher_' . $dispatcher->id(), $values)) {
            $dispatcher->settingsFormSubmit($values['configure_dispatcher_' . $dispatcher->id()]);
          }
        }
      }
      catch (PluginException $e) {
        // Plugin instance could not be created...
      }
    }


    parent::submitForm($form, $form_state);
  }

}
