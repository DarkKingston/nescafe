<?php

namespace Drupal\advanced_datalayer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a AdvancedDatalayerGroup annotation object.
 *
 * @Annotation
 */
class AdvancedDatalayerGroup extends Plugin {

  /**
   * The group ID, in machine name format.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the group.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Description of the group.
   *
   * @var string
   */
  public $description;

  /**
   * Weight of the group.
   *
   * @var int
   */
  public $weight;

}
