<?php

namespace Drupal\ln_srh_full\Commands;

use Drupal;
use Drupal\ln_srh_full\SRHFullConstants;
use Drush\Commands\DrushCommands;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh_full\Services\SRHComplementUtilsInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;


/**
 * Class SynchronizeComplements
 */
class SynchronizeComplements extends DrushCommands {

  use StringTranslationTrait;

  /**
   * @var SRHInterface
   */
  protected $srhConnector;

  /**
   * @var SRHComplementUtilsInterface
   */
  protected $srhUtils;



  public function __construct(SRHInterface $srhConnector, SRHComplementUtilsInterface $srhUtils) {
    $this->srhConnector = $srhConnector;
    $this->srhUtils = $srhUtils;
  }

  /**
   * Get recipes from Smart Recipe Hub.
   *
   * @param string $srh_ids
   *   The ids of the recipes to be imported.
   *
   * @option only_published Set to 1 to synchronice published recipes only.
   * @option page_size Define how many complements per page.
   * @option batch_pages Define how many pages will be synchronized in the same step.
   * @option batch_recipes Define how many complements will be synchronized in the same step.
   * @command ln_srh_full:synchronize-complements
   * @aliases srh-sync-complements
   */
  public function synchronizeComplements($srh_ids = NULL, $options = [
    'only_published' => FALSE,
    'page_size' => 10,
    'batch_pages' => 1,
    'batch_complements' => 1,
  ]) {
    $srh_complements_ids = $srh_ids ? explode(',', $srh_ids) : NULL;
    $onlyPublished = $options['only_published'] ?? FALSE;
    $pageSize = min(max(($options['page_size'] ?? 1), 1), 100);
    $batchPages = max(($options['batch_pages'] ?? 1), 1);
    $batchComplements = max(($options['batch_complements'] ?? 1), 1);
    $localesSettings = $this->srhConnector->getConnectorSettings()
      ->get('locales');
    foreach ($localesSettings as $localesSetting) {
      $locale = $localesSetting['connect_markets'];
      $this->io()->title($this->t('Build batch complements pages from locale @locale...', ['@locale' => $locale]));
      drush_op([
        $this,
        'buildBatchComplementsLocale',
      ], $locale, $srh_complements_ids, $onlyPublished, $pageSize, $batchPages, $batchComplements);
    }
    $this->logger()->success(dt('Import process successfully completed!'));
  }

  public function buildBatchComplementsLocale($locale, $srh_complements_ids, $onlyPublished, $pageSize, $batchPages, $batchComplements) {
    $totalPages = $this->srhConnector->countPagesComplementsByLocale(0, $locale, $srh_complements_ids, $pageSize);
    for ($i = 1; $i <= $totalPages; $i = $i + $batchPages) {
      $page_end = $totalPages < ($batchPages + $i) ? $totalPages : $batchPages + $i - 1;
      $this->io()
        ->text($this->t('Synchronizing complements(@batch_complements each time) from page @page_start to @page_end of locale @locale...', [
          '@locale' => $locale,
          '@page_start' => $i,
          '@page_end' => $page_end,
          '@batch_complements' => $batchComplements,
        ]));
      drush_op([
        $this,
        'buildBatchComplementsPage',
      ], $i, $locale, $srh_complements_ids, $onlyPublished, $totalPages, $pageSize, $batchPages, $batchComplements);
    }
  }

  public function buildBatchComplementsPage($page, $locale, $srh_complements_ids, $onlyPublished, $totalPages, $pageSize, $batchPages, $batchComplements) {
    $srh_complements = $this->srhConnector->getComplementsToSync(0, $locale, $batchPages, $page, $totalPages, $srh_complements_ids, $pageSize);
    $this->io->progressStart(count($srh_complements));
    foreach (array_chunk($srh_complements, $batchComplements, TRUE) as $chunk) {
      drush_op([$this, 'synchronizeBatchComplements'], $chunk, $onlyPublished);
      $this->io->progressAdvance($batchComplements);
    }
    $this->io->progressFinish();
  }

  public function synchronizeBatchComplements($srh_complements, $onlyPublished){
    foreach ($srh_complements as $srh_complement){
      try{
        if (!$onlyPublished || ($onlyPublished && $srh_complement['status'] == SRHFullConstants::SRH_COMPLEMENT_STATUS_PUBLISHED)) {
          /** @var Drupal\node\NodeInterface $complement */
          if($complement = $this->srhUtils->syncComplement($srh_complement)){
            \Drupal::messenger()
              ->addMessage($this->t('The complement @title (@id) has been successfully synced', [
                '@title' => $complement->label(),
                '@id' => $srh_complement['id'],
              ]), 'status');
          }else{
            \Drupal::messenger()
              ->addMessage($this->t('An error has occurred synchronizing the complement @title (@id). It may be that the complement is not published on the server', [
                '@title' => $srh_complement['name'] ?? $this->t('Unname'),
                '@id' => $srh_complement['id'],
              ]), 'warning');
          }
        }
      }catch (Drupal\ln_srh\SRHException $e){
        \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
      }catch (\Exception $e){
        \Drupal::messenger()
          ->addMessage($this->t('An error has occurred synchronizing the complement @title (@id). Make sure that the complement contains valid values to be synchronized', [
            '@title' => $srh_complement['name'] ?? $this->t('Unname'),
            '@id' => $srh_complement['id'],
          ]), 'warning');
      }
    }
  }

}
