<?php

namespace Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Group;

use Drupal\Component\Plugin\PluginBase;

/**
 * Each group will extend this base.
 */
abstract class GroupBase extends PluginBase {

  /**
   * Machine name of the datalayer tag group plugin.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the group.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  protected $label;

  /**
   * Description of the group.
   *
   * @var string
   */
  protected $description;

  /**
   * Weight of the group.
   *
   * @var int
   */
  public $weight;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Set the properties from the annotation.
    $this->id = $plugin_definition['id'] ?? '';
    $this->label = $plugin_definition['label'] ?? '';
    $this->description = $plugin_definition['description'] ?? '';
    $this->weight = $plugin_definition['weight'] ?? '';
  }

  /**
   * Get this group's internal ID.
   *
   * @return string
   *   This group's ID.
   */
  public function id() {
    return $this->id;
  }

  /**
   * Get this group's human-friendly name.
   *
   * @return string
   *   This group's human-friendly name.
   */
  public function label() {
    return $this->label;
  }

  /**
   * This group description.
   *
   * @return string
   *   This group's ID.
   */
  public function description() {
    return $this->description;
  }

  /**
   * The group weight.
   *
   * @return int|float
   *   The form API weight for tag.
   */
  public function weight() {
    return $this->weight;
  }

}
