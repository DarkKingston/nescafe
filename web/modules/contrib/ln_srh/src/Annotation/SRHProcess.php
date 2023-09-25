<?php

namespace Drupal\ln_srh\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a SRHProcess annotation object.
 *
 * @Annotation
 */
class SRHProcess extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;


  /**
   * The human-readable name of the SRHProcess plugin.
   *
   * @var Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The drupal recipe field_name.
   *
   * @var string
   */
  public $field_name;

}
