<?php

namespace Drupal\ln_srh\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * Provides an annotated layout plugin for srh content.
 *
 * @Layout(
 *   id = "srh_two_columns_bis_footer",
 *   label = @Translation("SRH Two Columns Bis Footer"),
 *   category = @Translation("SRH"),
 *   description = @Translation("SRH Two Columns Bis Footer"),
 *   template = "templates/srh-two-colums-bis-footer",
 *   regions = {
 *     "left_first" = {
 *       "label" = @Translation("Left First Row")
 *     },
 *     "right_first" = {
 *       "label" = @Translation("Right First Row")
 *     },
 *    "left_second" = {
 *       "label" = @Translation("Left Second Row")
 *     },
 *     "right_second" = {
 *       "label" = @Translation("Right Second Row")
 *     },
 *     "footer" = {
 *       "label" = @Translation("Footer")
 *     }
 *   },
 * )
 */
class SRHTwoColumnsBisFooter extends LayoutDefault {

}
