<?php

namespace Drupal\ln_srh\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * Provides an annotated layout plugin for srh content.
 *
 * @Layout(
 *   id = "srh_two_columns_staked_footer",
 *   label = @Translation("SRH Two Columns Staked Footer"),
 *   category = @Translation("SRH"),
 *   description = @Translation("SRH Two Columns Staked Footer"),
 *   template = "templates/srh-two-colums-staked-footer",
 *   regions = {
 *     "top" = {
 *       "label" = @Translation("Top")
 *     },
 *     "left" = {
 *       "label" = @Translation("Left")
 *     },
 *     "right" = {
 *       "label" = @Translation("Right")
 *     },
 *     "footer" = {
 *       "label" = @Translation("Footer")
 *     }
 *   },
 * )
 */
class SRHTwoColumnsStackedFooter extends LayoutDefault {

}
