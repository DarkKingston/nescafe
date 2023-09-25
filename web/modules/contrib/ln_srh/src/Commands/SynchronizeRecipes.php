<?php

namespace Drupal\ln_srh\Commands;

use Drupal;
use Drush\Commands\DrushCommands;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;


/**
 * Class SynchronizeRecipes
 */
class SynchronizeRecipes extends DrushCommands {

  use StringTranslationTrait;

  /**
   * @var SRHInterface
   */
  protected $srhConnector;

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;



  public function __construct(SRHInterface $srhConnector, SRHUtilsInterface $srhUtils) {
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
   * @option page_size Define how many recipes per page.
   * @option batch_pages Define how many pages will be synchronized in the same step.
   * @option batch_recipes Define how many recipes will be synchronized in the same step.
   * @command ln_srh:synchronize
   * @aliases srh-sync-recipes
   */
  public function synchronizeRecipes($srh_ids = NULL, $options = ['only_published' => FALSE, 'page_size' => 10, 'batch_pages' => 1, 'batch_recipes' => 1]) {
    $srh_recipes_ids = $srh_ids ? explode(',',$srh_ids) : NULL;
    $onlyPublished = $options['only_published'] ?? FALSE;
    $pageSize = min(max(($options['page_size'] ?? 1),1),100);
    $batchPages = max(($options['batch_pages'] ?? 1),1);
    $batchRecipes = max(($options['batch_recipes'] ?? 1),1);
    $localesSettings = $this->srhConnector->getConnectorSettings()->get('locales');
    foreach ($localesSettings as $localesSetting){
      $locale = $localesSetting['connect_markets'];
      $this->io()->title($this->t('Build batch recipes pages from locale @locale...',['@locale' => $locale]));
      drush_op([$this, 'buildBatchRecipesLocale'],$locale,$srh_recipes_ids,$onlyPublished,$pageSize,$batchPages,$batchRecipes);
    }
    $this->logger()->success(dt('Import process successfully completed!'));
  }

  public function buildBatchRecipesLocale($locale,$srh_recipes_ids,$onlyPublished,$pageSize,$batchPages,$batchRecipes){
    $totalPages = $this->srhConnector->countPagesRecipesByLocale(0,$locale,$srh_recipes_ids,$pageSize);
    for($i=1; $i<=$totalPages; $i = $i + $batchPages){
      $page_end = $totalPages < ($batchPages + $i) ? $totalPages : $batchPages + $i - 1;
      $this->io()->text($this->t('Synchronizing recipes(@batch_recipes each time) from page @page_start to @page_end of locale @locale...',['@locale' => $locale,'@page_start' => $i,'@page_end' => $page_end,'@batch_recipes' => $batchRecipes]));
      drush_op([$this, 'buildBatchRecipesPage'], $i,$locale,$srh_recipes_ids,$onlyPublished,$totalPages,$pageSize,$batchPages,$batchRecipes);
    }
  }

  public function buildBatchRecipesPage($page,$locale,$srh_recipes_ids,$onlyPublished,$totalPages,$pageSize,$batchPages,$batchRecipes){
    $srh_recipes = $this->srhConnector->getRecipesToSync(0,$locale,$batchPages,$page,$totalPages,$srh_recipes_ids,$pageSize);
    $this->io->progressStart(count($srh_recipes));
    foreach (array_chunk($srh_recipes, $batchRecipes, true) as $chunk) {
      drush_op([$this, 'synchronizeBatchRecipes'],$chunk,$onlyPublished);
      $this->io->progressAdvance($batchRecipes);
    }
    $this->io->progressFinish();
  }

  public function synchronizeBatchRecipes($srh_recipes, $onlyPublished){
    foreach ($srh_recipes as $srh_recipe){
      try{
        if (!$onlyPublished || ($onlyPublished && $srh_recipe['status']['id'] == 1)) {
          /** @var Drupal\node\NodeInterface $recipe */
          if($recipe = $this->srhUtils->syncRecipe($srh_recipe)){
            \Drupal::messenger()->addMessage($this->t('The recipe @title (@id) has been successfully synced',['@title' => $recipe->label(), '@id' => $srh_recipe['id']]), 'status');
          }else{
            \Drupal::messenger()->addMessage($this->t('An error has occurred synchronizing the recipe @title (@id). It may be that the recipe is not published on the server',['@title' => $srh_recipe['name'] ?? $this->t('Unname'), '@id' => $srh_recipe['id']]), 'warning');
          }
        }
      }catch (Drupal\ln_srh\SRHException $e){
        \Drupal::messenger()->addMessage($e->getMessage(), 'warning');
      }catch (\Exception $e){
        \Drupal::messenger()->addMessage($this->t('An error has occurred synchronizing the recipe @title (@id). Make sure that the recipe contains valid values to be synchronized',['@title' => $srh_recipe['name'] ?? $this->t('Unname'), '@id' => $srh_recipe['id']]), 'warning');
      }
    }
  }

}
