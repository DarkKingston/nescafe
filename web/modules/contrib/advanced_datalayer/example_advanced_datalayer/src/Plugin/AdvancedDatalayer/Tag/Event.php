<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "event" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "event",
 *   label = @Translation("Main tag event"),
 *   description = @Translation("Main tag event."),
 *   group = "root",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = TRUE,
 *   weight = 0,
 * )
 */
class Event extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
