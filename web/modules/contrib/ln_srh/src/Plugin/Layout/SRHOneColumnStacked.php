<?php

namespace Drupal\ln_srh\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * Provides an annotated layout plugin for srh content.
 *
 * @Layout(
 *   id = "srh_one_column_stacked",
 *   label = @Translation("SRH One Column Stacked"),
 *   category = @Translation("SRH"),
 *   description = @Translation("SRH One Column Stacked"),
 *   template = "templates/srh-one-column-stacked",
 *   regions = {
 *     "header" = {
 *       "label" = @Translation("Header")
 *     },
 *     "body_top" = {
 *       "label" = @Translation("Body Top")
 *     },
 *     "body_middle" = {
 *       "label" = @Translation("Body Middle")
 *     },
 *     "body_bottom" = {
 *       "label" = @Translation("Body Bottom")
 *     },
 *     "footer" = {
 *       "label" = @Translation("Footer")
 *     }
 *   },
 * )
 */
class SRHOneColumnStacked extends LayoutDefault {

}
