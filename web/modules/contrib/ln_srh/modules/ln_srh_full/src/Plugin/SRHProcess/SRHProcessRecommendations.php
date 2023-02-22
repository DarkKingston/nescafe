<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHException;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessTag;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_recommendations",
 *   field_name = "field_srh_recommendations",
 *   label = @Translation("Recommendations")
 * )
 */

class SRHProcessRecommendations extends SRHProcessParagraph {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * @var SRHInterface
   */
  protected $srhConnerctor;

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils,SRHInterface $srhConnerctor, SRHProcessManager $srhProcessManager, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhUtils = $srhUtils;
    $this->srhConnerctor = $srhConnerctor;
    $this->srhProcessManager = $srhProcessManager;
    $this->loggerFactory = $loggerFactory->get('ln_srh');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('ln_srh.utils'),
      $container->get('srh'),
      $container->get('plugin.manager.srh_process'),
      $container->get('logger.factory')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    $srh_recommendations = [];
    if($srh_id = $srh_data['id'] ?? FALSE){
      $srh_recommendations = $this->srhConnerctor->getRecipeRecommendations($srh_id);
    }

    return $srh_recommendations;
  }

  public function getValues($srh_data, $langcode) {
    if(empty($srh_data['recipes'])){
      return NULL;
    }
    /** @var SRHProcessTag $srhProcessTagRecommendations */
    $srhProcessTagRecommendations = $this->srhProcessManager->createInstance('srh_process_tag',[
      'srh_source_field' => 'recommendationType',
      'vocabulary_id' => 'srh_recommendation_type',
    ]);
    $values =  [
      'type' => 'srh_recommendation',
    ];
    /** @var ParagraphInterface $recommendation */
    $recommendation = $this->paragraphStorage->create($values);
    $values += [
      'field_srh_recommendation_type' => $srhProcessTagRecommendations->process($recommendation,$srh_data,'field_srh_recommendation_type'),
      'field_srh_recipes' => $this->provideRecommendationRecipes($srh_data),
    ];

    return $values;
  }

  private function provideRecommendationRecipes($srh_data){
    $recomendationRecipes = $srh_data['recipes'] ?? [];
    $recipes = [];
    foreach ($recomendationRecipes as $recomendationRecipe){
      if($srh_id = $recomendationRecipe['id'] ?? FALSE){
        /** @var NodeInterface $recipe */
        if(!$recipe = $this->srhUtils->getRecipeBySRHId($srh_id)){
          // If neither the parent recipe nor its recommendation exists, the parent recipe is saved
          if($this->entity->isNew()){
            $this->entity->save();
          }
          if($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)){
            try{
              $recipe = $this->srhUtils->syncRecipe($srh_recipe);
            }catch (SRHException $e){
              $this->loggerFactory->warning($e->getMessage());
              continue;
            }
          }else{
            continue;
          }
        }else{
          if($this->needResync($recipe)){
            if ($srh_recipe = $this->srhUtils->getSRHRecipe($srh_id)) {
              $queue = \Drupal::service('queue')->get('srh_recipe_syncronizer_queue');
              $queue->createItem($srh_recipe);
            }
          }
        }
        if($recipe){
          $recipes[] = $recipe;
        }
      }
    }

    return $recipes;
  }

  private function getSRHRecommendationByRecipe(NodeInterface $recipe){
    $srhRecommendations = [];
    $recipeRecommendations = $recipe->get('field_srh_recommendations')->referencedEntities();
    /** @var ParagraphInterface $recipeRecommendation */
    foreach ($recipeRecommendations as $recipeRecommendation){
      if(!$recipeRecommendation->get('field_srh_recipes')->isEmpty()){
        if(!$recipeRecommendation->get('field_srh_recommendation_type')->isEmpty()){
          /** @var TermInterface $recommendationType */
          $recommendationType = $recipeRecommendation->get('field_srh_recommendation_type')->entity;
          $nodesRecipe = $recipeRecommendation->get('field_srh_recipes')->referencedEntities();
          foreach ($nodesRecipe as $nodeRecipe){
            if(!$nodeRecipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
              $srhRecommendations[$recommendationType->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString()] = $nodeRecipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString();
            }
          }
        }
      }
    }
    return $srhRecommendations;
  }

  private function needResync(NodeInterface $recipe){
    if($srh_id = $recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString()){
      $srh_recommendations = $this->srhConnerctor->getRecipeRecommendations($srh_id);
      $recipe_recommendations = $this->getSRHRecommendationByRecipe($recipe);
      foreach ($srh_recommendations as $srh_recommendation){
        if(!empty($srh_recommendation['recommendationType']['id']) && !empty($srh_recommendation['recipes'])){
          $srh_recommendations_ids = array_column($srh_recommendation['recipes'],'id');
          $intersect = array_intersect($srh_recommendations_ids,$recipe_recommendations[$srh_recommendation['recommendationType']['id']]);
          if((!empty($recipe_recommendations[$srh_recommendation['recommendationType']['id']]) && count($intersect) != count($recipe_recommendations[$srh_recommendation['recommendationType']['id']])) || count($recipe_recommendations[$srh_recommendation['recommendationType']['id']]) != count($srh_recommendations_ids)){
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

}
