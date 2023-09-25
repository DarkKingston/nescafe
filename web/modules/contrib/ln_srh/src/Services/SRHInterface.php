<?php

namespace Drupal\ln_srh\Services;

interface SRHInterface {

  /**
   * Get language id for a locale.
   *
   * @param $locale
   *
   * @return string
   *  language id
   */
  public function getLangCodeFromLocale($locale);

  /**
   * @param $timeSync
   *  The timestamp of the last synchronization.
   * @param array $recipeIds
   * @return array
   */
  public function getRecipes($timeSync, $recipeIds = NULL);

  /**
   * @param int $timeSync
   *  The timestamp of the last synchronization.
   * @param $locale
   *  The srh locale code
   * @param $pages
   * @param int $offset
   * @param int $totalPages
   * @param array $recipeIds
   * @param int $page_size
   * @return mixed
   */
  public function getRecipesToSync($timeSync, $locale, $pages, $offset = 1, $totalPages = 0, $recipeIds = NULL, $page_size = 10);

  /**
   * @param int $timeSync
   *  The timestamp of the last synchronization.
   * @return integer
   */
  public function countRecipes($timeSync);

  /**
   * @param int $timeSync
   *  The timestamp of the last synchronization.
   * @param $locale
   *  The srh locale code
   * @return integer
   */
  public function countRecipesByLocale($timeSync,$locale);

  /**
   * @param int $timeSync
   * @param $locale
   * @param array $recipeIds
   * @param int $page_size
   * @return integer
   */
  public function countPagesRecipesByLocale($timeSync,$locale,$recipeIds = NULL,$page_size = 10);

  /**
   * @param $locale
   * @param null $from
   * @param null $to
   * @param array $recipeIds
   * @param int $page_size
   * @return mixed
   */
  public function getSyncRecipeStats($locale, $from = 0, $to = NULL, $recipeIds = NULL, $page_size = 10);

  /**
   * @param $page
   * @param $locale
   * @param null $from
   * @param null $to
   * @param null $recipeIds
   * @param int $page_size
   * @return mixed
   */
  public function getSyncRecipePage($page, $locale, $from = 0, $to = NULL, $recipeIds = NULL, $page_size = 10);

  /**
   * @param $url
   * @param $apikey
   * @param $channel_id
   * @param $market_code
   * @param $locale
   * @return mixed
   */
  public function checkStatusSRH($url, $apikey, $channel_id, $market_code, $locale);

  /**
   * @param $srh_id
   * @param $locale
   * @param $langcode
   * @return mixed
   */
  public function getRecipe($srh_id,$locale,$langcode);

  /**
   * @param $srh_id
   * @return mixed
   */
  public function getRecipeVersions($srh_id);

  /**
   * @param $srh_id
   * @return mixed
   */
  public function getRecipeSideDishes($srh_id);

  /**
   * @param $nutrients
   * @return mixed
   */
  public function getScore($nutrients);

  /**
   * @param $srh_id
   * @return mixed
   */
  public function getRecipeTranslations($srh_id);

  /**
   * @param $srh_id
   * @return mixed
   */
  public function getRecipeRecommendations($srh_id);

  /**
   * @return array
   */
  public function getPublishedRecipesIds();

  /**
   * @param $timeSync
   *  The timestamp of the last synchronization.
   * @param array $complementIds
   * @return array
   */
  public function getComplements($timeSync, array $complementIds = NULL);

  /**
   * @param int $timeSync
   *  The timestamp of the last synchronization.
   * @param $locale
   *  The srh locale code
   * @param $pages
   * @param int $offset
   * @param int $totalPages
   * @param array $recipeIds
   * @param int $page_size
   * @return mixed
   */
  public function getComplementsToSync($timeSync, $locale, $pages, $offset = 1, $totalPages = 0, $complementIds = NULL, $page_size = 10);

  /**
   * @param int $timeSync
   * @param $locale
   * @param array $complementIds
   * @param int $page_size
   * @return integer
   */
  public function countPagesComplementsByLocale($timeSync, $locale, $complementIds = NULL, $page_size = 10);

  /**
   * @param $locale
   * @param null $from
   * @param null $to
   * @param array $complementIds
   * @param int $page_size
   * @return mixed
   */
  public function getSyncComplementsStats($locale, $from = 0, $to = NULL, $complementIds = NULL, $page_size = 10);


  /**
   * @param $complementId
   * @return array
   */
  public function getComplement($complementId);

  /**
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  public function getConnectorSettings();

}
