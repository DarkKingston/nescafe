<?php

namespace Drupal\field_timer\Plugin\Field\FieldFormatter;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base implementation of formatters that uses jQuery Countdown plugin.
 */
abstract class FieldTimerCountdownFormatterBase extends FieldTimerJsFormatterBase {

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * FieldTimerCountdownFormatterBase constructor.
   *
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param $label
   * @param $view_mode
   * @param array $third_party_settings
   * @param \Drupal\Component\Datetime\TimeInterface $time
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, TimeInterface $time) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('datetime.time'));
  }

  /**
   * {@inheritdoc}
   */
  const LIBRARY_NAME = 'jquery.countdown';

  /**
   * Formatter types.
   */
  const
    TYPE_AUTO = 'auto',
    TYPE_TIMER = 'timer',
    TYPE_COUNTDOWN = 'countdown';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'type' => static::TYPE_AUTO,
    ] + parent::defaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $this->typeOptions(),
      '#default_value' => $this->getSetting('type'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $type = $this->getSetting('type');
    $summary[] = $this->t('Type: @type', ['@type' => $this->typeOptions()[$type]]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePluginSettings(FieldItemInterface $item, $langcode) {
    $settings = $this->getSettings();
    $timestamp = $this->getTimestamp($item);
    $type = $this->getSetting('type');

    if ($type == 'timer' || ($type == 'auto' && $timestamp <= REQUEST_TIME)) {
      $settings['until'] = FALSE;
      $settings['since'] = TRUE;
    }
    elseif ($type == 'countdown' || ($type == 'auto' && $timestamp > REQUEST_TIME)) {
      $settings['until'] = TRUE;
      $settings['since'] = FALSE;
    }

    unset($settings['type']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function typeOptions() {
    return [
      static::TYPE_AUTO => $this->t('Auto'),
      static::TYPE_TIMER => $this->t('Timer'),
      static::TYPE_COUNTDOWN => $this->t('Countdown'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDocumentationLink(array $options = []) {
    return Url::fromUri('http://keith-wood.name/countdownRef.html', $options)->toString();
  }

}
