<?php

namespace Drupal\ln_srh_menuiq\Services;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ln_srh\Form\SRHConnectionSettings;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

class SRHMyMenuIQHelper{

  /**
   * @var SRHInterface
   */
  protected $srh;

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var CacheBackendInterface
   */
  protected $cache;

  /**
   * @param SRHInterface $srh
   * @param ConfigFactoryInterface $configFactory
   */
  public function __construct(SRHInterface $srh, ConfigFactoryInterface $configFactory, CacheBackendInterface $cache) {
    $this->srh = $srh;
    $this->configFactory = $configFactory;
    $this->cache = $cache;
  }

  /**
   * @param NodeInterface $recipe
   * @return \string[][]
   */
  public function getRecipeEnergy(NodeInterface $recipe){
    $enertyNutrients = SRHMyMenuIQConstants::MYMENUIQ_ENERGY_NUTRIENTS;
    if($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_NUTRIENTS_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_NUTRIENTS_FIELD)->isEmpty()){
      $paragraphsNutrients = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_NUTRIENTS_FIELD)->referencedEntities();
      /** @var ParagraphInterface $paragraphsNutrient */
      foreach ($paragraphsNutrients as $paragraphsNutrient){
        if($paragraphsNutrient->hasField(SRHMyMenuIQConstants::SRH_NUTRIENT_TERM_FIELD) && !$paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_NUTRIENT_TERM_FIELD)->isEmpty()){
          /** @var TermInterface $termNutrient */
          $termNutrient = $paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_NUTRIENT_TERM_FIELD)->entity;
          if($termNutrient->hasField(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD) && !$termNutrient->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
            $nutrientId = $termNutrient->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString();
            if(array_key_exists($nutrientId,SRHMyMenuIQConstants::MYMENUIQ_ENERGY_NUTRIENTS)){
              if($paragraphsNutrient->hasField(SRHMyMenuIQConstants::SRH_SIDEDISHES_SCORE_FIELD) && !$paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_SCORE_FIELD)->isEmpty()){
                $percentage = $paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_SCORE_FIELD)->getString();
                if($paragraphsNutrient->hasField(SRHMyMenuIQConstants::SRH_NUTRIENT_QUANTITY_FIELD) && !$paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_NUTRIENT_QUANTITY_FIELD)->isEmpty()){
                  $quantity = $paragraphsNutrient->get(SRHMyMenuIQConstants::SRH_NUTRIENT_QUANTITY_FIELD)->getString();
                  $enertyNutrients[$nutrientId] = [
                    'percentage' => $percentage,
                    'quantity' => $quantity,
                    'name' => $termNutrient->label(),
                  ];
                }
              }
            }
          }
        }
      }
    }
    return $enertyNutrients;
  }

  /**
   * @param NodeInterface $recipe
   * @return array
   */
  public function getRecipeMealscoreNutrients(NodeInterface $recipe){
    $mealscoreNutrients = SRHMyMenuIQConstants::MYMENUIQ_MEALSCORE_NUTRIENTS;
    $recipes = [];
    if (!$recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()) {
      $connection_settings = $this->configFactory->get(SRHConnectionSettings::SETTINGS);
      $locales = $connection_settings->get('locales');
      foreach ($locales as $locale) {
        if ($srh_recipe = $this->srh->getRecipe($recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString(), $locale['connect_markets'], $locale['langcode'])) {
          $recipes[] = $srh_recipe;
        }
      }
      if ($recipes) {
        $srh_recipe = reset($recipes);
        if (isset($srh_recipe['nutrients']) && !empty($srh_recipe['nutrients'])) {
          foreach ($srh_recipe['nutrients'] as $nutrient) {
            if (array_key_exists($nutrient['id'], SRHMyMenuIQConstants::MYMENUIQ_MEALSCORE_NUTRIENTS)) {
              if (isset($nutrient['quantity']) && !empty($nutrient['quantity'])) {
                $mealscoreNutrients[$nutrient['id']]['value'] = $nutrient['quantity'];
              }
            }
          }
        }
      }
    }
    $recipeNutrients = [];
    foreach ($mealscoreNutrients as $mealscoreNutrient){
      $recipeNutrients[$mealscoreNutrient['name']] = floatval($mealscoreNutrient['value']);
    }
    return $recipeNutrients;
  }

  /**
   * @param $complementId
   * @return array
   */
  public function getComplementMealscoreNutrients(ParagraphInterface $sideDish){
    $nutrients = [];
    if($complementId = $sideDish->hasField(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD) ? $sideDish->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() : FALSE){
      $mealscoreNutrients = SRHMyMenuIQConstants::MYMENUIQ_MEALSCORE_NUTRIENTS;
      if($srhComplement = $this->srh->getComplement($complementId)){
        $srhNutrients = $srhComplement['nutrients'] ?? [];
        foreach ($srhNutrients as $srhNutrient){
          $nutrientId = $srhNutrient['id'] ?? FALSE;
          if($nutrientId && array_key_exists($nutrientId,SRHMyMenuIQConstants::MYMENUIQ_MEALSCORE_NUTRIENTS)){
            $quantity = $srhNutrient['quantity'] ?? 0;
            $mealscoreNutrients[$nutrientId]['value'] = $quantity;
          }
        }
      }
      foreach ($mealscoreNutrients as $mealscoreNutrient){
        $nutrients[$mealscoreNutrient['name']] = floatval($mealscoreNutrient['value']);
      }
    }

    return $nutrients;
  }

  /**
   * @param $nutrientsA
   * @param $nutrientsB
   * @return array
   */
  public function combineMealscoreNutrients($nutrientsA, $nutrientsB){
    $combineNutrients = [];
    foreach ($nutrientsA as $nutrientId => $nutrient){
      $combineNutrients[$nutrientId] = $nutrientsA[$nutrientId] + ($nutrientsB[$nutrientId] ?? 0);
    }

    return $combineNutrients;
  }

  /**
   * @param NodeInterface $recipe
   * @param $sidedishes
   * @return int|mixed
   */
  public function calculateScore(NodeInterface $recipe,$sidedishes){
    $cacheID = 'srh_recipe:' . $recipe->id();
    if ($cache = $this->cache->get($cacheID)) {
      $data = $cache->data;
      $mealscoreNutrients = $data['nutrients'] ?? [];
    } else {
      $mealscoreNutrients = $this->getRecipeMealscoreNutrients($recipe);
      $this->cache->set($cacheID, ['nutrients' => $mealscoreNutrients], CacheBackendInterface::CACHE_PERMANENT, ['node:' . $recipe->id()]);
    }
    /** @var ParagraphInterface $sidedish */
    foreach ($sidedishes as $sidedish){
      $sidedishNutrients = $this->getSideDishMealscoreNutrients($sidedish);
      $mealscoreNutrients = $this->combineMealscoreNutrients($mealscoreNutrients,$sidedishNutrients);
    }
    $score = $this->srh->getScore($mealscoreNutrients);

    return $score['score'] ?? 0;
  }

  /**
   * @param ParagraphInterface $sideDish
   * @return array
   */
  public function getSideDishMealscoreNutrients(ParagraphInterface $sideDish){
    $mainRecipe = $sideDish->getParentEntity();
    $cacheID = 'srh_recipe:' . $mainRecipe->id() . ':srh_sidedish:' . $sideDish->id();
    if($cache = $this->cache->get($cacheID)) {
      $data = $cache->data;
      return $data['nutrients'] ?? [];
    }
    $nutrients = [];
    $type = $this->getSideDishType($sideDish);
    if($type == 'recipe'){
      if($recipe = $this->getSideDishRecipe($sideDish)){
        $nutrients = $this->getRecipeMealscoreNutrients($recipe);
      }
    }else if($type == 'complement'){
      $nutrients = $this->getComplementMealscoreNutrients($sideDish);
    }
    $this->cache->set($cacheID, ['nutrients' => $nutrients], CacheBackendInterface::CACHE_PERMANENT, ['node:' . $mainRecipe->id()]);

    return $nutrients;
  }

  /**
   * @param ParagraphInterface $sideDish
   * @return string
   */
  public function getSideDishType(ParagraphInterface $sideDish){
    $sideDishRecipe = $this->getSideDishRecipe($sideDish);

    return $sideDishRecipe ? 'recipe' : 'complement';
  }

  /**
   * @param ParagraphInterface $sideDish
   * @return string
   */
  public function getSideDishCategory(ParagraphInterface $sideDish){
    $categoryType = '';
    if($sideDish->hasField(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD) && !$sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD)->isEmpty()){
      /** @var TermInterface $category */
      $category = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD)->entity;
      if($category->hasField(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD) && !$category->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
        $category_srh_id = $category->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString();
        $sideDisyTypes = SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPES;
        $categoryType = $sideDisyTypes[$category_srh_id] ?? $category_srh_id;
      }
    }

    return $categoryType;
  }

  /**
   * @param ParagraphInterface $sideDish
   * @return NodeInterface|false
   */
  public function getSideDishRecipe(ParagraphInterface $sideDish){
    $sideDishRecipe = FALSE;
    if($sideDish->hasField(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD) && !$sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->isEmpty()){
      /** @var NodeInterface $sideDishRecipe */
      $sideDishRecipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity;
    }

    return $sideDishRecipe;
  }
}
