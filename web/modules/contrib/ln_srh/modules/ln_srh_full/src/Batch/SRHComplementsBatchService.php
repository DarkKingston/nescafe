<?php

namespace Drupal\ln_srh_full\Batch;

use Drupal\ln_srh_full\SRHFullConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh_full\SRHComplementsBatchConfiguration;
use Drupal\node\NodeInterface;

class SRHComplementsBatchService {

  public static function synchronize_complements($locale, $srh_complements, $total_pages, $page, SRHComplementsBatchConfiguration $batchConfig, &$context) {
    /** @var \Drupal\ln_srh_full\Services\SRHComplementUtilsInterface $srhUtils */
    $srhUtils = \Drupal::service('ln_srh_full.complement_utils');
    $batch_pages = $batchConfig->getBatchPages();
    $batch_complements = $batchConfig->getBatchComplements();
    $page_end = $total_pages < ($batch_pages + $page) ? $total_pages : $batch_pages + $page - 1;
    $context['message'] = t('Synchronizing complements (@batch_complements each time) from page @page_start to @page_end of locale @locale...', [
      '@locale' => $locale,
      '@page_start' => $page,
      '@page_end' => $page_end,
      '@batch_complements' => $batch_complements,
    ]);
    // Keep locale & clearCronQueue in context results to use it in finished function.
    $context['results']['locale'] = $locale;
    $context['results']['clearCronQueue'] = $batchConfig->isClearCronQueue();
    $onlyPublished = $batchConfig->isOnlyPublished();
    foreach ($srh_complements as $srh_complement){
      try{
        if (!$onlyPublished || ($onlyPublished && $srh_complement['status'] == SRHFullConstants::SRH_COMPLEMENT_STATUS_PUBLISHED)) {
          /** @var NodeInterface $complement */
          if ($complement = $srhUtils->syncComplement($srh_complement)) {
            \Drupal::messenger()
              ->addMessage(t('The complement @title (@id) has been successfully synced', [
                '@title' => $complement->label(),
                '@id' => $srh_complement['id'],
              ]), 'status');
          }
          else {
            \Drupal::messenger()
              ->addMessage(t('An error has occurred synchronizing the complement @title (@id). It may be that the complement is not published on the server', [
                '@title' => $srh_complement['name'] ?? t('Unname'),
                '@id' => $srh_complement['id'],
              ]), 'warning');
          }
        }
      }catch (SRHException $e){
        \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
      }catch (\Exception $e){
        \Drupal::messenger()
          ->addMessage(t('An error has occurred synchronizing the complement @title (@id). Make sure that the complement contains valid values to be synchronized', [
            '@title' => $srh_complement['name'] ?? t('Unname'),
            '@id' => $srh_complement['id'],
          ]), 'warning');
      }
    }
  }

  public static function buildBatchComplementsLocale(SRHComplementsBatchConfiguration $batchConfig, $locale, &$context) {
    /** @var \Drupal\ln_srh\Services\SRH $srh */
    $srh = \Drupal::service('srh');
    $totalPages = $srh->countPagesComplementsByLocale($batchConfig->getFromDate(), $locale, $batchConfig->getComplementIds(), $batchConfig->getPageSize());
    $batch = [
      'title' => t('Building batch of @total_pages pages of the locale @locale...', [
        '@locale' => $locale,
        '@total_pages' => $totalPages,
      ]),
      'operations' => [],
    ];
    $batch_pages = $batchConfig->getBatchPages();
    for ($i = 1; $i <= $totalPages; $i = $i + $batch_pages) {
      $batch['operations'][] = [
        [static::class, 'buildBatchComplementsPage'],
        [
          $i,
          $locale,
          $totalPages,
          $batchConfig
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
        $localesSync = $state->get('ln_srh_complements_locales_sync', []);
        $localesSync[$locale] = [
          'page_index' => 1,
          'last_sync' => \Drupal::time()->getRequestTime(),
        ];
        $state->set('ln_srh_complements_locales_sync', $localesSync);
      }
      if (!empty($results['clearCronQueue'])) {
        // Clean up any recipe sync item from cron queue .
        /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
        $queue_factory = \Drupal::service('queue');
        $queue = $queue_factory->get('srh_complement_syncronizer_queue');
        if ($queue) {
          $queue->deleteQueue();
        }
      }
    }
  }

  public static function buildBatchComplementsPage($page, $locale, $total_pages, SRHComplementsBatchConfiguration $batchConfig, &$context) {
    /** @var \Drupal\ln_srh\Services\SRH $srh */
    $srh = \Drupal::service('srh');
    $srh_complements = $srh->getComplementsToSync($batchConfig->getFromDate(), $locale, $batchConfig->getBatchPages(), $page, $total_pages, $batchConfig->getComplementIds(), $batchConfig->getPageSize());
    $context['message'] = t('Building batch of @total_pages pages of the locale @locale...',['@locale' => $locale, '@total_pages' => $total_pages]);
    $batch = [
      'title' => t('Synchronizing complements...'),
      'operations' => []
    ];
    $batchComplements = $batchConfig->getBatchComplements();
    foreach (array_chunk($srh_complements, $batchComplements, TRUE) as $chunk) {
      $batch['operations'][] = [
        [static::class, 'synchronize_complements'],
        [
          $locale,
          $chunk,
          $total_pages,
          $page,
          $batchConfig
        ],
      ];
    }

    if (!$batchConfig->getComplementIds()) {
      // It is a full sync.
      $batch['finished'] = [static::class, 'fullSyncBatchFinished'];
    }

    batch_set($batch);
  }
}
