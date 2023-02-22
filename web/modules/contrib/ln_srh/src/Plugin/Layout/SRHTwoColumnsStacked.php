<?php

namespace Drupal\ln_srh\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * Provides an annotated layout plugin for srh content.
 *
 * @Layout(
 *   id = "srh_two_columns_staked",
 *   label = @Translation("SRH Two Columns Staked"),
 *   category = @Translation("SRH"),
 *   description = @Translation("SRH Two Columns Staked"),
 *   template = "templates/srh-two-colums-staked",
 *   regions = {
 *     "top" = {
 *       "label" = @Translation("Top")
 *     },
 *     "left" = {
 *       "label" = @Translation("Left")
 *     },
 *     "right" = {
 *       "label" = @Translation("Right")
 *     }
 *   },
 * )
 */
class SRHTwoColumnsStacked extends LayoutDefault {

}
