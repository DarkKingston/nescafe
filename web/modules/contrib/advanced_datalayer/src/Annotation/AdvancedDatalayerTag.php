<?php

namespace Drupal\advanced_datalayer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a AdvancedDatalayerTag annotation object.
 *
 * @Annotation
 */
class AdvancedDatalayerTag extends Plugin {

  /**
   * The datalayer tag ID, in machine name format.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the datalayer tag.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Description of the tag.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The group of the datalayer tag (AdvancedDatalayerTag plugin).
   *
   * @var string
   */
  public $group;

  /**
   * If this tag is global.
   *
   * Global tags can be editable only on main settings page
   * and added in tags on any page.
   *
   * @var bool
   */
  public $global;

  /**
   * If tag with empty value should be in datalayer.
   *
   * @var bool
   */
  public $show_empty;

  /**
   * Weight of the datalayer tag.
   *
   * @var int
   */
  public $weight;

  /**
   * Is the datalayer tag required.
   *
   * @var bool
   */
  public $required;

  /**
   * Is the datalayer tag translatable.
   *
   * @var bool
   */
  public $translatable;

}
