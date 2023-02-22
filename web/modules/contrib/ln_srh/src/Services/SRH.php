<?php

namespace Drupal\ln_srh\Services;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_srh\SRHConstants;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SRH implements SRHInterface
{

  use StringTranslationTrait;

  /**
   * The SRH connector settings
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $connectorSettings;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerFactory;

  /**
   * @var TimeInterface
   */
  protected $time;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var array
   *
   * Language codes assoc array as defined inside locales config.
   */
  protected $localeLangCodes = [];

  /**
   * SRH constructor.
   * @param ConfigFactoryInterface $configFactory
   * @param LoggerChannelFactoryInterface $logger_factory
   * @param TimeInterface $time
   * @param LanguageManagerInterface $languageManager
   */
  public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $logger_factory, TimeInterface $time, LanguageManagerInterface $languageManager) {
    $this->connectorSettings = $configFactory->get('ln_srh.settings');
    $this->loggerFactory = $logger_factory->get('ln_srh');
    $this->time = $time;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangCodeFromLocale($locale) {
    if (!$this->localeLangCodes) {
      /** @var array $locales */
      $locales = $this->connectorSettings->get('locales');
      $this->localeLangCodes = array_column($locales, 'langcode', 'connect_markets');
    }
    if (!isset($this->localeLangCodes[$locale])) {
      $this->localeLangCodes[$locale] = $this->languageManager->getCurrentLanguage()->getId();
    }
    return $this->localeLangCodes[$locale];
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipes($timeSync, $recipeIds = NULL){
    $locales = $this->connectorSettings->get('locales');
    $recipes = [];
    foreach ($locales as $locale){
      $locale_code = $locale['connect_markets'];
      if($stats = $this->getSyncRecipeStats($locale_code,$timeSync,NULL,$recipeIds)){
        $pages = $stats['numberOfPages'] ?? 0;
        $recipes += $this->getRecipesToSync($timeSync,$locale_code,$pages,1,$pages,$recipeIds);
      }
    }
    return $recipes;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipesToSync($timeSync, $locale, $pages, $offset = 1, $totalPages = 0, $recipeIds = NULL, $page_size = 10) {
    $recipes = [];
    $limit = $pages + $offset - 1;
    $limit = $totalPages >= $limit ? $limit : $totalPages;
    for ($i = $offset; $i <= $limit; $i++) {
      if ($result = $this->getSyncRecipePage($i, $locale, $timeSync, NULL, $recipeIds, $page_size)) {
        foreach ($result as $item) {
          if (!empty($item['id'])) {
            $recipes[$item['id']] = $item;
            $recipes[$item['id']]['langcode'] = $this->getLangCodeFromLocale($locale);
          }
        }
      }
    }
    return $recipes;
  }

  /**
   * {@inheritdoc}
   */
  public function countRecipes($timeSync){
    $locales = $this->connectorSettings->get('locales');
    $count = 0;
    foreach ($locales as $locale){
      $locale_code = $locale['connect_markets'];
      $count += $this->countRecipesByLocale($timeSync,$locale_code);
    }
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function countRecipesByLocale($timeSync,$locale){
    $count = 0;
    if($stats = $this->getSyncRecipeStats($locale,$timeSync)){
      $count = $stats['total'] ?? 0;
    }
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function countPagesRecipesByLocale($timeSync,$locale,$recipeIds = NULL,$page_size = 10){
    $count = 0;
    if($stats = $this->getSyncRecipeStats($locale,$timeSync,NULL,$recipeIds,$page_size)){
      $count = $stats['numberOfPages'] ?? 0;
    }
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getComplements($timeSync, $complementIds = NULL) {
    $locales = $this->connectorSettings->get('locales');
    $complements = [];
    foreach ($locales as $locale) {
      $locale_code = $locale['connect_markets'];
      if ($stats = $this->getSyncComplementsStats($locale_code, $timeSync, NULL, $complementIds)) {
        $pages = $stats['numberOfPages'] ?? 0;
        $complements += $this->getComplementsToSync($timeSync, $locale_code, $pages, 1, $pages, $complementIds);
      }
    }
    return $complements;
  }

  /**
   * {@inheritdoc}
   */
  public function getComplementsToSync($timeSync, $locale, $pages, $offset = 1, $totalPages = 0, $complementIds = NULL, $page_size = 10) {
    $complements = [];
    $limit = $pages + $offset - 1;
    $limit = $totalPages >= $limit ? $limit : $totalPages;
    for ($i = $offset; $i <= $limit; $i++) {
      if ($result = $this->getSyncComplementsPage($i, $locale, $timeSync, NULL, $complementIds, $page_size)) {
        foreach ($result as $item) {
          if (!empty($item['id'])) {
            $langCode = $this->getLangCodeFromLocale($locale);
            $key = $item['id'] . ':' . $langCode;
            $complements[$key] = $item;
            $complements[$key]['langcode'] = $this->getLangCodeFromLocale($locale);
          }
        }
      }
    }
    return $complements;
  }

  /**
   * {@inheritdoc}
   */
  public function countPagesComplementsByLocale($timeSync, $locale, $complementIds = NULL, $page_size = 10) {
    $count = 0;
    if ($stats = $this->getSyncComplementsStats($locale, $timeSync, NULL, $complementIds, $page_size)) {
      $count = $stats['numberOfPages'] ?? 0;
    }
    return $count;
  }

  protected function post($endpoint,$body,$query=[]){
    $url_base = $this->connectorSettings->get('url') . '/' .SRHConstants::SRH_API_VERSION;
    $client = new Client([
      'headers' => [
        'x-api-key' => $this->connectorSettings->get('apikey'),
        'x-channel-id' => $this->connectorSettings->get('channel_id'),
      ],
      'body' => Json::encode($body),
    ]);
    try {
      $response = $client->request('POST', "{$url_base}/{$endpoint}",['query' => $query]);
      $code = $response->getStatusCode();
      $data = JSON::decode($response->getBody()->getContents());
      if($code == 200){
        return $data;
      }else{
        $message = $data['userMessage'] ?? $this->t('An error occurred while making a request to the server');
        $this->loggerFactory->warning($message);
        return FALSE;
      }
    }
    catch (GuzzleException $e) {
      $this->loggerFactory->warning($e->getMessage());
      return FALSE;
    }
  }

  /**
   * @param $endpoint
   * @param $query
   * @return false|mixed
   */
  protected function get($endpoint,$query){
    $url_base = $this->connectorSettings->get('url') . '/' .SRHConstants::SRH_API_VERSION;
    $client = new Client([
      'headers' => [
        'x-api-key' => $this->connectorSettings->get('apikey'),
        'x-channel-id' => $this->connectorSettings->get('channel_id'),
      ],
    ]);
    try {
      $response = $client->request('GET', "{$url_base}/{$endpoint}", ['query' => $query]);
      $code = $response->getStatusCode();
      $data = JSON::decode($response->getBody()->getContents());
      if($code == 200){
        return $data;
      }else{
        $message = $data['userMessage'] ?? $this->t('An error occurred while making a request to the server');
        $this->loggerFactory->warning($message);
        return FALSE;
      }
    }
    catch (GuzzleException $e) {
      $this->loggerFactory->warning($e->getMessage());
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkStatusSRH($url, $apikey, $channel_id, $market_code, $locale){
    $query = [
      'from' => date('Y-m-d H:i:s', 0),
      'locale' => $locale
    ];
    $client = new Client([
      'headers' => [
        'x-api-key' => $apikey,
        'x-channel-id' => $channel_id,
      ],
    ]);
    try {
      $response = $client->request('GET', $url . '/sync/recipes/stats', ['query' => $query]);
      $code = $response->getStatusCode();
      $content = JSON::decode($response->getBody()->getContents());
      $message = '';
      switch ($code) {
        case '200':
          $message = $content['total'] ?? '0';
          break;
        case '400':
          $message = $content['userMessage'] ?? '0';
          break;
      }
    } catch (GuzzleException $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
    }
    return ['code' => $code, 'message' => $message];
  }

  /**
   * Format query params used by sync recipes stats and page.
   */
  protected function getSyncRecipesQueryParams($locale, $from = 0, $to = NULL, $recipeIds = NULL, $page_size = 10) {
    $dateTimeFrom = new \DateTime('@' . $from, new \DateTimeZone('UTC'));
    $query = [
      'locale'      => $locale,
      'from'        => $dateTimeFrom->format('Y-m-d H:i:s'),
      'pageSize'    => $page_size
    ];
    if ($to) {
      $dateTimeTo = new \DateTime('@' . $from, new \DateTimeZone('UTC'));
      $query += ['to' => $dateTimeTo->format('Y-m-d H:i:s'),];
    }
    if ($recipeIds) {
      $query += ['recipeId' => implode(',', $recipeIds)];
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getSyncRecipeStats($locale, $from = 0, $to = NULL, $recipeIds = NULL, $page_size = 10){
    $query = $this->getSyncRecipesQueryParams($locale, $from, $to, $recipeIds, $page_size);
    return $this->get('sync/recipes/stats',$query);
  }

  /**
   * {@inheritdoc}
   */
  public function getSyncRecipePage($page, $locale, $from = 0, $to = NULL, $recipeIds = NULL, $page_size = 10){
    $query = $this->getSyncRecipesQueryParams($locale, $from, $to, $recipeIds, $page_size);
    return  $this->get("sync/recipes/{$page}",$query);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipe($srh_id,$locale,$langcode){
    $market = $this->connectorSettings->get('market_code');
    $include = [
      'detailedIngredients',
      'detailedNutrition',
      'tagging',
      'howToBurnIt'
    ];
    $query = [
      'locale'      => $locale,
      'market'      => $market,
      'include'     => implode(',',$include)
    ];
    if($srh_recipe = $this->get("recipe/{$srh_id}",$query)){
      $srh_recipe['langcode'] = $langcode ?? $this->languageManager->getCurrentLanguage()->getId();
    }
    return $srh_recipe;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipeVersions($srh_id){
    return $this->get("recipe/{$srh_id}/versions",[]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipeSideDishes($srh_id){
    return $this->get("recipe/{$srh_id}/sidedishes",[]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipeRecommendations($srh_id){
    return $this->get("recipe/{$srh_id}/recommendations",[]);
  }

  /**
   * {@inheritdoc}
   */
  public function getScore($nutrients){
    return $this->post('mealscore',$nutrients,[]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipeTranslations($srh_id){
    return $this->get("recipe/{$srh_id}/translations",[]);
  }

  /**
   * {@inheritdoc}
   */
  public function getPublishedRecipesIds(){
    $market = $this->connectorSettings->get('market_code');
    $locales = $this->connectorSettings->get('locales');
    $chanelId = $this->connectorSettings->get('channel_id');
    $recipeIds = [];
    foreach ($locales as $locale){
      $query = [
        'market'        => $market,
        'locale'        => $locale['connect_markets'],
        'x-channel-id'  => $chanelId,
      ];
      $initialResult = $this->post("recipes/filter",[],$query);
      $totalResults = $initialResult['totalResults'] ?? 0;
      for($i = 0; $i < $totalResults; $i = $i + 50 ){
        if($results = $this->post("recipes/filter",['limit' => 50, 'offset' => $i],$query)){
          $srhRecipes = $results['results'] ?? [];
          foreach ($srhRecipes as $srhRecipe){
            if(!empty($srhRecipe['id'])){
              $recipeIds[] = $srhRecipe['id'];
            }
          }
        }
      }
    }
    return $recipeIds;
  }

  /**
   * Format query params used by sync complements stats and page.
   */
  protected function getSyncComplementQueryParams($locale, $from = 0, $to = NULL, $complementIds = NULL, $page_size = 10) {
    $dateTimeFrom = new \DateTime('@' . $from, new \DateTimeZone('UTC'));
    $query = [
      'locale'      => $locale,
      'from'        => $dateTimeFrom->format('Y-m-d H:i:s'),
      'pageSize'    => $page_size
    ];
    if ($to) {
      $dateTimeTo = new \DateTime('@' . $from, new \DateTimeZone('UTC'));
      $query += ['to' => $dateTimeTo->format('Y-m-d H:i:s'),];
    }
    if ($complementIds) {
      $query += ['complementId' => implode(',', $complementIds)];
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getSyncComplementsStats($locale, $from = 0, $to = NULL, $complementIds = NULL, $page_size = 10) {
    $query = $this->getSyncComplementQueryParams($locale, $from, $to, $complementIds, $page_size);
    return $this->get('sync/complements/stats', $query);
  }

  /**
   * {@inheritdoc}
   */
  public function getSyncComplementsPage($page, $locale, $from = 0, $to = NULL, $complementIds = NULL, $page_size = 10) {
    $query = $this->getSyncComplementQueryParams($locale, $from, $to, $complementIds, $page_size);
    return $this->get("sync/complements/{$page}", $query);
  }

  /**
   * {@inheritdoc}
   */
  public function getComplement($complementId) {
    $locales = $this->connectorSettings->get('locales');
    foreach ($locales as $locale) {
      if ($complement = $this->get("complement/{$complementId}", ['locale' => $locale['connect_markets']])) {
        return $complement;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConnectorSettings(){
    return $this->connectorSettings;
  }
}
