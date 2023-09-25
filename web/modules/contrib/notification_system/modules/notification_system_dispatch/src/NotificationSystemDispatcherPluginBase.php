<?php

namespace Drupal\notification_system_dispatch;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for notification_system_dispatcher plugins.
 */
abstract class NotificationSystemDispatcherPluginBase extends PluginBase implements NotificationSystemDispatcherInterface {

  /**
   * {@inheritdoc}
   */
  public function id() {
    return (string) $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function description() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormValidate(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $values) {
  }

}
