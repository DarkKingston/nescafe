<?php

namespace Drupal\field_timer\Plugin\Field\FieldFormatter;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeTimeAgoFormatter;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Plugin implementation of the 'field_timer_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "field_timer_simple_text",
 *   label = @Translation("Text timer or countdown"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class FieldTimerSimpleTextFormatter extends DateTimeTimeAgoFormatter {

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * FieldTimerSimpleTextFormatter constructor.
   *
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param $label
   * @param $view_mode
   * @param array $third_party_settings
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Component\Datetime\TimeInterface $time
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DateFormatterInterface $date_formatter, Request $request, TimeInterface $time) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $date_formatter, $request);

    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('date.formatter'), $container->get('request_stack')->getCurrentRequest(), $container->get('datetime.time'));
  }

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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $type = $this->getSetting('type');

    foreach ($items as $delta => $item) {
      switch ($type) {
        case static::TYPE_TIMER:
          if ($item->date->getTimestamp() >= $this->time->getRequestTime()) {
            unset($elements[$delta]);
          }
          break;

        case static::TYPE_COUNTDOWN:
          if ($item->date->getTimestamp() < $this->time->getRequestTime()) {
            unset($elements[$delta]);
          }
          break;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#default_value' => $this->getSetting('type'),
      '#options' => $this->typeOptions(),
      '#description' => $this->t('Switch timer/countdown automatically or disable it.'),
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
  protected function typeOptions() {
    return [
      static::TYPE_AUTO => $this->t('Auto'),
      static::TYPE_TIMER => $this->t('Timer'),
      static::TYPE_COUNTDOWN => $this->t('Countdown'),
    ];
  }

}
