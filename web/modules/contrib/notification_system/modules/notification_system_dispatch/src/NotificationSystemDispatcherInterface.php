<?php

namespace Drupal\notification_system_dispatch;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;

/**
 * Interface for notification_system_dispatcher plugins.
 */
interface NotificationSystemDispatcherInterface {

  public const SEND_MODE_IMMEDIATELY = 1;
  public const SEND_MODE_DAILY = 2;
  public const SEND_MODE_WEEKLY = 3;

  /**
   * Returns the plugin id.
   *
   * @return string
   *   The dispatcher id.
   */
  public function id();

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated label.
   */
  public function label();

  /**
   * Returns the translated plugin description.
   *
   * @return string
   *   The translated description.
   */
  public function description();

  /**
   * Return the form fields that can be used to configure this dispatcher.
   *
   * @return array
   *   An array of form fields for the form api.
   */
  public function settingsForm();

  /**
   * Validate function for the settings form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function settingsFormValidate(array &$form, FormStateInterface $form_state);

  /**
   * Submit function for the settings form.
   *
   * @param array $values
   *   An array of the values for the form fields that were provided.
   */
  public function settingsFormSubmit(array $values);

  /**
   * Send out a specific notification to one user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The recipient.
   * @param \Drupal\notification_system\model\NotificationInterface[] $notifications
   *   An array with one or more notifications that should be sent.
   *   If there are more notifications, bundling is enabled, and the user should
   *   receive only one notification with a summary.
   */
  public function dispatch(UserInterface $user, array $notifications);

}
