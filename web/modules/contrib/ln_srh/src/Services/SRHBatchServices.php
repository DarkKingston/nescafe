<?php

namespace Drupal\ln_srh\Services;

use Drupal\ln_srh\SRHException;
use Drupal\node\NodeInterface;

class SRHBatchServices{

  public static function synchronize_recipes($locale, $srh_recipes, $onlyPublished, $total_pages, $page, $batch_pages, $batch_recipes, $clearCronQueue, &$context) {
    $srhUtils = \Drupal::service('ln_srh.utils');
    $page_end = $total_pages < ($batch_pages + $page) ? $total_pages : $batch_pages + $page - 1;
    $context['message'] = t('Synchronizing recipes(@batch_recipes each time) from page @page_start to @page_end of locale @locale...',['@locale' => $locale,'@page_start' => $page,'@page_end' => $page_end,'@batch_recipes' => $batch_recipes]);
    // Keep locale & clearCronQueue in context results to use it in finished function.
    $context['results']['locale'] = $locale;
    $context['results']['clearCronQueue'] = $clearCronQueue;
    foreach ($srh_recipes as $srh_recipe){
      try{
        if (!$onlyPublished || ($onlyPublished && $srh_recipe['status']['id'] == 1)) {
          /** @var NodeInterface $recipe */
          if ($recipe = $srhUtils->syncRecipe($srh_recipe)) {
            \Drupal::messenger()->addMessage(t('The recipe @title (@id) has been successfully synced', ['@title' => $recipe->label(), '@id' => $srh_recipe['id']]), 'status');
          } else {
            \Drupal::messenger()->addMessage(t('An error has occurred synchronizing the recipe @title (@id). It may be that the recipe is not published on the server', ['@title' => $srh_recipe['name'] ?? t('Unname'), '@id' => $srh_recipe['id']]), 'warning');
          }
        }
      }catch (SRHException $e){
        \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
      }catch (\Exception $e){
        \Drupal::messenger()->addMessage(t('An error has occurred synchronizing the recipe @title (@id). Make sure that the recipe contains valid values to be synchronized',['@title' => $srh_recipe['name'] ?? t('Unname'), '@id' => $srh_recipe['id']]), 'warning');
      }
    }
  }

  public static function buildBatchRecipesLocale($locale, $srh_recipes_ids, $onlyPublished, $page_size, $batch_pages, $batch_recipes, $from, $clearCronQueue, &$context) {
    /** @var \Drupal\ln_srh\Services\SRH $srh */
    $srh = \Drupal::service('srh');
    $totalPages = $srh->countPagesRecipesByLocale($from, $locale, $srh_recipes_ids, $page_size);
    $batch = [
      'title' => t('Building batch of @total_pages pages of the locale @locale...', [
        '@locale' => $locale,
        '@total_pages' => $totalPages,
      ]),
      'operations' => [],
    ];
    for ($i = 1; $i <= $totalPages; $i = $i + $batch_pages) {
      $batch['operations'][] = [
        '\Drupal\ln_srh\Services\SRHBatchServices::buildBatchRecipesPage',
        [
          $i,
          $locale,
          $srh_recipes_ids,
          $onlyPublished,
          $totalPages,
          $page_size,
          $batch_pages,
          $batch_recipes,
          $from,
          $clearCronQueue
        ],
      ];
    }
    batch_set($batch);
  }

  public static function fullSyncBatchFinished($success, $results, array $operations) {
    if ($success) {
      if (!empty($results['locale'])) {
        // Update locale sync to current time and page index 1.
        $locale = $results['locale'];
        $state = \Drupal::state();
        $localesSync = $state->get('ln_srh_recipes_locales_sync', []);
        $localesSync[$locale] = [
          'page_index' => 1,
          'last_sync' => \Drupal::time()->getRequestTime(),
        ];
        $state->set('ln_srh_recipes_locales_sync', $localesSync);
      }
      if (!empty($results['clearCronQueue'])) {
        // Clean up any recipe sync item from cron queue .
        /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
        $queue_factory = \Drupal::service('queue');
        $queue = $queue_factory->get('srh_recipe_syncronizer_queue');
        if ($queue) {
          $queue->deleteQueue();
        }
      }
    }
  }

  public static function buildBatchRecipesPage($page, $locale, $srh_recipes_ids, $onlyPublished, $total_pages, $page_size, $batch_pages, $batch_recipes, $from, $clearCronQueue, &$context) {
    /** @var \Drupal\ln_srh\Services\SRH $srh */
    $srh = \Drupal::service('srh');
    $srh_recipes = $srh->getRecipesToSync($from, $locale, $batch_pages, $page, $total_pages, $srh_recipes_ids, $page_size);
    $context['message'] = t('Building batch of @total_pages pages of the locale @locale...',['@locale' => $locale, '@total_pages' => $total_pages]);
    $batch = [
      'title' => t('Synchronizing recipes...'),
      'operations' => []
    ];
    foreach (array_chunk($srh_recipes, $batch_recipes, TRUE) as $chunk) {
      $batch['operations'][] = [
        '\Drupal\ln_srh\Services\SRHBatchServices::synchronize_recipes',
        [
          $locale,
          $chunk,
          $onlyPublished,
          $total_pages,
          $page,
          $batch_pages,
          $batch_recipes,
          $clearCronQueue
        ],
      ];
    }

    if (!$srh_recipes_ids) {
      // It is a full sync.
      $batch['finished'] = '\Drupal\ln_srh\Services\SRHBatchServices::fullSyncBatchFinished';
    }

    batch_set($batch);
  }
}
