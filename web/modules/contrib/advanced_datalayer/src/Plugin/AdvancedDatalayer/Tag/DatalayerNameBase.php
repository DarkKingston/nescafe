<?php

namespace Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Each datalayer tag will extend this base.
 */
abstract class DatalayerNameBase extends PluginBase {

  use StringTranslationTrait;

  /**
   * Machine name of the datalayer tag plugin.
   *
   * @var string
   */

  protected $id;

  /**
   * The title of the datalayer tag plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  protected $label;

  /**
   * The description of the datalayer tag plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  protected $description;

  /**
   * The group for the datalayer tag.
   *
   * @var string
   */
  protected $group;

  /**
   * If this datalayer tag global.
   *
   * @var bool
   */
  protected $global;

  /**
   * If tag with empty value should be in datalayer.
   *
   * @var bool
   */
  protected $showEmpty;

  /**
   * Weight of the datalayer tag.
   *
   * @var int
   */
  protected $weight;

  /**
   * Is the datalayer tag required.
   *
   * @var bool
   */
  protected $required;

  /**
   * Is the datalayer tag translatable.
   *
   * @var bool
   */
  protected $translatable;

  /**
   * The value of the datalayer tag in this instance.
   *
   * @var mixed
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Set the properties from the annotation.
    $this->id = $plugin_definition['id'] ?? '';
    $this->label = $plugin_definition['label'] ?? '';
    $this->description = $plugin_definition['description'] ?? '';
    $this->group = $plugin_definition['group'] ?? '';
    $this->global = $plugin_definition['global'] ?? '';
    $this->showEmpty = $plugin_definition['show_empty'] ?? FALSE;
    $this->required = $plugin_definition['required'] ?? FALSE;
    $this->weight = $plugin_definition['weight'] ?? '';
    $this->translatable = $plugin_definition['translatable'] ?? FALSE;
  }

  /**
   * The datalayer tag's internal ID.
   *
   * @return string
   *   This datalayer tag's internal ID.
   */
  public function id() {
    return $this->id;
  }

  /**
   * The datalayer tag label.
   *
   * @return string
   *   This datalayer tag label.
   */
  public function label() {
    return $this->label;
  }

  /**
   * The datalayer tag description.
   *
   * @return bool
   *   This datalayer tag description.
   */
  public function description() {
    return $this->description;
  }

  /**
   * The datalayer tag group.
   *
   * @return string
   *   The datalayer tag group name.
   */
  public function group() {
    return $this->group;
  }

  /**
   * The datalayer tag weight.
   *
   * @return int|float
   *   The form API weight for tag.
   */
  public function weight() {
    return $this->weight;
  }

  /**
   * Whether or not this datalayer tag is globa.
   *
   * @return bool
   *   Whether this datalayer tag is global.
   */
  public function isGlobal() {
    return $this->global;
  }

  /**
   * Whether or not this datalayer tag should be in datalayer event if empty.
   *
   * @return bool
   *   Whether this datalayer tag is global.
   */
  public function showEmpty() {
    return $this->showEmpty;
  }

  /**
   * Whether or not this datalayer tag is globa.
   *
   * @return bool
   *   Whether this datalayer tag is global.
   */
  public function required() {
    return $this->required;
  }

  /**
   * Whether or not this datalayer tag is translatable.
   *
   * @return bool
   *   Whether this datalayer tag has been enabled.
   */
  public function isTranslatable() {
    return $this->translatable;
  }

  /**
   * Whether or not this datalayer tag is active.
   *
   * @return bool
   *   Whether this datalayer tag has been enabled.
   */
  public function isActive() {
    return TRUE;
  }

  /**
   * Generate a form element for this tag.
   *
   * @param array $element
   *   The existing form element to attach to.
   *
   * @return array
   *   The completed form element.
   */
  public function form(array $element = []) {
    return [
      '#type' => 'textfield',
      '#title' => $this->label(),
      '#default_value' => $this->value(),
      '#maxlength' => 255,
      '#description' => $this->description(),
      '#required' => $this->required(),
    ];
  }

  /**
   * Obtain the current tag's raw value.
   *
   * @return string
   *   The current raw tag value.
   */
  public function value() {
    return $this->value;
  }

  /**
   * Assign the current tag a value.
   *
   * @param string $value
   *   The value to assign this tag.
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Process value of tag.
   *
   * Useful for dynamically manipulate with tag value.
   *
   * @param string $value
   *   The value to assign this tag.
   *
   * @return mixed
   *   The processed tag value.
   */
  public function processValue($value) {
    // Nothing to do by default.
    return $value;
  }

  /**
   * Make the string presentable.
   *
   * @param string $value
   *   The raw string to process.
   *
   * @return string
   *   The tag value after processing.
   */
  private function tidy($value) {
    return trim(Html::escape($value));
  }

  /**
   * Generate output array for a tag.
   *
   * @return array|string
   *   A render array or an empty string.
   */
  public function output() {

    $processedValue = $this->processValue($this->value());
    $value = $this->tidy($processedValue);

    if (empty($this->value) && !$this->showEmpty()) {
      return '';
    }

    return [
      'tag' => $this->id(),
      'group' => $this->group(),
      'value' => $value,
    ];
  }

}
