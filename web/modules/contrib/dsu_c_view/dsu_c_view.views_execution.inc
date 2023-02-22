<?php

/**
 * @file
 * Contains dsu_c_view.views_execution.inc file.
 */

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_query_alter().
 */
function dsu_c_view_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {
  if ($view->id() == 'presentations' && $view->current_display == 'block_presentations') {
    $type = \Drupal::request()->get('type', NULL);
    $map = [
      '1' => 'node__field_video.field_video_target_id',
      '2' => 'node__field_audio.field_audio_target_id',
      '3' => 'node__field_presentation.field_presentation_target_id',
      '4' => 'node__field_transcript.field_transcript_target_id',
    ];

    foreach ($query->where[1]['conditions'] as $index => $condition) {
      if (isset($type) && isset($map[$type]) && ($condition['field'] == $map[$type])) {
        continue;
      }
      if (in_array($condition['field'], array_values($map))) {
        unset($query->where[1]['conditions'][$index]);
      }
    }
  }
  if ($view->id() == 'article_list' && ($view->current_display == 'block_press_releases' || $view->current_display == 'search_all_news')) {
    $year = $view->exposed_data['year'];
    // Check if argument condition is available.
    foreach ($query->where[1]['conditions'] as $i => $condition) {
      if (strpos($condition['field'], 'node_field_data.published_at') > -1) {
        if (isset($year) && $year == 'All') {
          unset($query->where[1]['conditions'][$i]);
        }
        else {
          if (empty($year)) {
            if ($view->current_display == 'search_all_news') {
              $date_range = \Drupal::state()
                ->get('nestle_global_configurations.search_all_news');
            }
            else {
              $date_range = \Drupal::state()
                ->get('nestle_global_configurations.press_releases');
            }
            $year_filter = $date_range['end_year'];
          }
          else {
            $year_filter = $year ?? date('Y', time());
          }
          $query->where[1]['conditions'][$i] = [
            'field'    => 'node_field_data.published_at',
            'operator' => 'between',
            'value'    => [
              'min' => mktime(0, 0, 0, 1, 1, $year_filter),
              'max' => mktime(23, 59, 59, 12, 31, $year_filter),
            ],
          ];
        }
      }
    }
  }

  if ($view->id() == 'events' && $view->current_display == 'block_events') {
    $start_date = $view->filter['field_start_date_of_the_event_value']->value['value'];
    if ($start_date == NULL || $start_date == 'now') {
      // Traverse through the 'where' part of the query.
      foreach ($query->where as &$condition_group) {
        foreach ($condition_group['conditions'] as &$condition) {
          // Replace the granuality with 'day' <=> 'year' for upcoming filter.
          if (strpos($condition['field'], 'node__field_start_date_of_the_event.field_start_date_of_the_event_value') !== FALSE) {
            $condition['field'] = str_replace('%Y', '%Y-%m-%d', $condition['field']);
          }
        }
      }
      $query->orderby[0]['direction'] = 'ASC';
    }
  }

  if ($view->id() == 'news_list' && $view->current_display == 'block_news_without_search') {

    $term_name = 'News';
    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $term_name]);
    reset($term);
    $tid = key($term);
    foreach ($query->where as &$condition_group) {
      foreach ($condition_group['conditions'] as $i => $condition) {
        if (strpos($condition['field'], 'node__field_article_type.field_article_type_target_id') > -1) {
          $query->where[1]['conditions'][$i] = [
            'field'    => 'node__field_article_type.field_article_type_target_id',
            'operator' => '=',
            'value'    => $tid,
          ];
        }
      }
    }
  }
}
