<?php

namespace Drupal\ln_alkemics\Controller;

use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\search_api\Entity\Index;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Importer.
 *
 * @package Drupal\ln_alkemics\Form
 */
class Importer extends ControllerBase {

  /**
   * Configuration state Drupal Site.
   *
   * @var array
   */
  protected $configFactory;

  /**
   * Configuration state Drupal Site.
   *
   * @var array
   */
  protected $serialization;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $configFactory, Json $serialization) {
    $this->configFactory = $configFactory;
    $this->serialization = $serialization;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('serialization.json')
    );
  }

  /**
   * Connect with API and get alkemics token.
   */
  public static function getAlkemicsTokenAuth() {
    // Checking endpoint details with client token.
    $config = Drupal::config('ln_alkemics.settings');
    $endpoint = $config->get('ln_alkemics.alkemics_api_endpoint_token_url');
    $client_id = $config->get('ln_alkemics.alkemics_api_client_id');
    $client_secret = $config->get('ln_alkemics.alkemics_api_client_secret');

    // Initialize client request.
    $httpClient = new Client();
    try {
      // Send request.
      $response = $httpClient->request('POST', $endpoint, [
        'body'        => '{
                        "client_secret": "' . $client_secret . '",
                        "client_id": "' . $client_id . '",
        "grant_type": "client_credentials"
                      }',
        'http_errors' => TRUE,
        'headers'     => [
          'Content-Type' => 'application/json',
        ],
      ]);
      // Get response and body.
      // 200.
      $code = $response->getStatusCode();
      $content = JSON::decode($response->getBody()->getContents());
      if ($code == '200') {
        Drupal::messenger()
          ->addStatus('Alkemics Tokens authentication connected successfully.');
      }
    } catch (RequestException $e) {
      if ($e->getCode() == '403') {
        Drupal::messenger()->addError('Exception Error on requesting data.');
      }
      else {
        Drupal::messenger()->addError('Networking error, check the URL.');
      }
    }
    return isset($content) ? $content : '';
  }

  /**
   * Main IMPORTATION Function.
   * This function receive an array
   * $values = array('values' => [the ids], 'market' => [market info])
   * This function call Alkemics with the market information,
   * and gets one by one the specified recipes.
   * Check if a recipe has to be imported, updates
   * or unpublished following the main configuration.
   */
  public static function syncroProducts($product) {
    // Get product unique ID.
    if (is_array($product)) {
      $ids = $product['gtin'];
    }
    else {
      $ids = [$product['gtin']];
    }

    // For each result we check if exists,
    // if needs to update or if needs to be unpublished.
    if (isset($product['gtin'])) {
      // Get Nid if recipe is exist or not.
      $entity_ids = Drupal::entityQuery('node')
        ->condition('type', 'dsu_product')
        ->condition('field_al_gtin', $ids)
        ->execute();
      $node_exists = !empty($entity_ids) ? Node::load($entity_ids[key($entity_ids)]) : NULL;
      // $updateDate = strtotime($product['updatedAt']);
      if ($node_exists == NULL) {
        // The Recipe doesn't exists locally. Importing it.
        Importer::saveProduct($product, $node_exists);
      }
      else {
        Importer::saveProduct($product, $node_exists);
        $node_exists->save();
      }
    }

  }

  /**
   * Save product data.
   *
   * @param array $data
   *   Product data array.
   *
   * @param null $node
   *   Node object.
   *
   *   FUNCTION: Preflight call to get All id of specific market & specific
   *   brands configured in the UI by the admin.
   *
   * @return string|void
   *   return string or void.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function saveProduct($data, $node = NULL) {
    $langcode = Drupal::languageManager()->getCurrentLanguage()->getId();
    // Check for old nodes.
    if (is_object($node) && isset($node)) {
      $node = $node;
    }
    elseif ($node == NULL) {
      /* Generating node entity, and setting the content from the recipe*/
      $node = Node::create(['type' => 'dsu_product']);
      $node->enforceIsNew();
    }

    if (isset($data['assets']['pictures']) && is_array($data['assets']['pictures'])) {
      $product_images = [];
      foreach ($data['assets']['pictures'] as $url) {
        $product_images[] = $url['url'];
      }
    }

    // Get synonyms.
    if (isset($data['synonyms']) && is_array($data['synonyms'])) {
      foreach ($data['synonyms'] as $synonym) {
        $synonyms[] = $synonym['text']['0']['data'];
      }
    }
    // Get health Allegations.
    if (isset($data['healthAllegations']) && is_array($data['healthAllegations'])) {
      foreach ($data['healthAllegations'] as $healthAllegation) {
        $healthAllegations[] = $healthAllegation['text']['0']['data'];
      }
    }
    // Get target consumer age.
    if (isset($data['targetConsumerAgeTextList']) && is_array($data['targetConsumerAgeTextList'])) {
      foreach ($data['targetConsumerAgeTextList'][0] as $consumerAge) {
        $consumerAges[] = $consumerAge[0]['data'];
      }
    }
    // Get target consumer gender list.
    if (isset($data['targetConsumerGenderList']) && is_array($data['targetConsumerGenderList'])) {
      foreach ($data['targetConsumerGenderList'] as $consumerGender) {
        $consumerGenders[] = $consumerGender['targetConsumerGenderCode']['label'];
      }
    }
    // Get product flavors.
    if (isset($data['flavors']) && is_array($data['flavors'])) {
      foreach ($data['flavors'] as $flavor) {
        $coffeeflavor[] = $flavor['flavor']['label'];
      }
    }
    // Get food and beverage ingredients.
    if (isset($data['foodAndBeverageIngredientDescriptionList']) && is_array($data['foodAndBeverageIngredientDescriptionList'])) {
      foreach ($data['foodAndBeverageIngredientDescriptionList'] as $ingredient) {
        $ingredients[] = $ingredient['ingredientNameText'][0]['data'];
      }
    }
    // Get number of servings pre package.
    if (isset($data['numberOfServingsPerPackageList']) && is_array($data['numberOfServingsPerPackageList'])) {
      foreach ($data['numberOfServingsPerPackageList'] as $numberOfServing) {
        $numberOfServings[] = $numberOfServing['measurementPrecisionOfNumberOfServingsPerPackageCode']['label'] . ': ' . $numberOfServing['numberOfServingsPerPackageNumber'];
      }
    }

    $node->set('title', isset($data['namePublicShort'][0]['data']) ? $data['namePublicShort'][0]['data'] : 'No Title');
    $node->set('path', '/product/' . $data['gtin']);
    $node->set('langcode', $langcode);
    $node->set('changed', strtotime($data['updatedAt']));
    $node->set('field_dsu_product_desc', isset($data['namePublicLong'][0]['data']) ? $data['namePublicLong'][0]['data'] : '');
    $node->set('field_al_pictures_f', isset($product_images) ? $product_images : '');
    $node->set('field_al_gtin', isset($data['gtin']) ? $data['gtin'] : '');
    $node->set('field_al_supplier_code', isset($data['supplierCode']) ? $data['supplierCode'] : '');
    $node->set('field_al_sub_brand_text', isset($data['subBrandText']) ? $data['subBrandText'] : '');
    $node->set('field_al_name_public_long', isset($data['namePublicLong'][0]['data']) ? $data['namePublicLong'][0]['data'] : '');
    $node->set('field_al_name_public_short', isset($data['namePublicShort'][0]['data']) ? $data['namePublicShort'][0]['data'] : '');
    $node->set('field_al_product_benefits', isset($data['productBenefits'][0]['text'][0]['data']) ? $data['productBenefits'][0]['text'][0]['data'] : '');
    $node->set('field_al_nutrients', isset($data['nutrients']) ? $data['nutrients'] : '');
    $node->set('field_al_allergens', isset($data['allergens']['0']['data']) ? $data['allergens']['0']['data'] : '');
    $node->set('field_al_coffee_range', isset($data['coffeeRangeText'][0]['data']) ? $data['coffeeRangeText'][0]['data'] : '');
    $node->set('field_al_coffee_format', isset($data['coffeeFormatCode']['label']) ? $data['coffeeFormatCode']['label'] : '');
    $node->set('field_al_good_to_know', isset($data['goodToKnow'][0]['data']) ? $data['goodToKnow'][0]['data'] : '');
    $node->set('field_al_good_question', isset($data['goodQuestion'][0]['data']) ? $data['goodQuestion'][0]['data'] : '');
    $node->set('field_al_good_to_remember', isset($data['goodToRemember'][0]['data']) ? $data['goodToRemember'][0]['data'] : '');
    $node->set('field_al_coffee_variety', isset($coffeeflavor) ? $coffeeflavor : '');
    $node->set('field_al_health_allegations', isset($healthAllegations) ? $healthAllegations : '');
    $node->set('field_al_keywords', isset($synonyms) ? $synonyms : '');
    $node->set('field_al_consumer_age', isset($consumerAges) ? $consumerAges : '');
    $node->set('field_al_consumer_gender', isset($consumerGenders) ? $consumerGenders : '');
    $node->set('field_al_number_of_servings', isset($numberOfServings) ? $numberOfServings : '');

    // hook_alter for adding products from outside of module.
    \Drupal::moduleHandler()->alter('ln_alkemics_import', $node, $data);

    try {
      $node->save();
    } catch (Exception $e) {
      Drupal::logger('Importer')
        ->notice('Cannot save node: ' . $e->getMessage());
      return ('ok');
    }

  }

  /**
   * Get all ids.
   *
   * @param array $token
   *   Token array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public static function getAllId($token) {
    // Checking endpoint details with client token.
    $config = Drupal::config('ln_alkemics.settings');
    $product_endpoint = $config->get('ln_alkemics.alkemics_api_endpoint_url');

    // Get market GTIN and code filter query.
    $gtin = !empty($config->get('ln_alkemics.alkemics_api_market_gtin')) ? $config->get('ln_alkemics.alkemics_api_market_gtin') : FALSE;
    $market = !empty($config->get('ln_alkemics.alkemics_api_target_market_id')) ? $config->get('ln_alkemics.alkemics_api_target_market_id') : FALSE;
    $market_brand_keyword = !empty($config->get('ln_alkemics.alkemics_api_target_market_keyword')) ? $config->get('ln_alkemics.alkemics_api_target_market_keyword') : '';
    $product_search_endpoint = !empty($config->get('ln_alkemics.alkemics_api_product_search_endpoint')) ? $config->get('ln_alkemics.alkemics_api_product_search_endpoint') : 'https://apis.alkemics.com/public/v1/products/list';
    if ((is_numeric($gtin)) or !empty($market)) {
      $product_endpoint .= '?filter_target_market=' . $market;
    }

    // Endpoints header.
    $options = [
      'method'      => 'GET',
      'http_errors' => TRUE,
      'headers'     => [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $token['access_token'],
      ],
    ];

    // hook_alter for alter header options with tokens.
    \Drupal::moduleHandler()
      ->alter('ln_alkemics_header_options', $options, $token);

    // Initialize client request.
    $httpClient = new Client();
    try {
      // Send request.
      $response = $httpClient->request('POST', $product_search_endpoint, [
        'body'        => '{
						               "advanced_search": {
                            "must": [
                                { "query": "' . $market_brand_keyword . '","fields": ["brand.label"]}
                                ]
                              },
                            "offset": 0,
                            "limit": 500
						               }',
        'http_errors' => TRUE,
        'headers'     => [
          'Content-Type'  => 'application/json',
          'Authorization' => 'Bearer ' . $token['access_token'],
        ],
      ]);

      // Get response and body.
      // 200.
      $code = $response->getStatusCode();
      $content = JSON::decode($response->getBody()->getContents());
      if ($code == '200') {
        foreach ($content['data'] as $value) {
          $product_data[] = ['gtin' => $value['gtin'], 'product' => $value];
        }
      }
    } // Handle exception with log in db.
    catch (RequestException $e) {
      Drupal::logger('Importer')
        ->notice('Exception with message:  ' . $e->getMessage());
    }
    return isset($product_data) ? $product_data : '';
  }


  /**
   * Synchronised finish and reset apache index config.
   */
  public static function syncroAlkemicsFinish($success, $operations) {
    // Run finish operation after completions of batch.
    if ($success) {

      // Get save history of indexing in config variables and convert
      // json_decode.
      \Drupal\ln_alkemics\Controller\Importer::toggleSolrSearchIndexingServer(TRUE);
      // Set message.
      Drupal::messenger()
        ->addMessage('Synchronization released. Check in admin/content to see the alkemics products.');
    }
    else {
      Drupal::messenger()->addMessage('Finished with an error.');
    }
  }


  /**
   * Main IMPORTATION Function.
   * This function receive an array $values = array('values' => [the ids],
   * 'market' => [market info]) This function call alkemics with the market
   * information, and gets one by one the specified recipes Check if a recipe
   * has to be imported, updates or unpublished following the main
   * configuration.
   */
  public static function toggleSolrSearchIndexingServer($status) {
    // Get default config from settings file.
    $config = Drupal::service('config.factory')
      ->getEditable('ln_alkemics.settings');

    // Get indexing for existing database server.
    $moduleService = Drupal::service('module_handler');
    if ($moduleService->moduleExists('search_api')) {
      $indexList = Index::loadMultiple();

      // Set and enable for indexing options.
      if ($status) {
        // Check if indexing server having list of index id.
        $indexing_server = $config->get('ln_alkemics.alkemics_indexing_server');
        $indexHistory = json_decode($indexing_server);
        if (!empty($indexHistory)) {
          foreach ($indexList as $index) {
            if (in_array($index->id(), $indexHistory)) {
              $index->setOption('index_directly', $status);
              $index->save();
            }
          }
        }
      }
      // Disable and keep history of indexing server for indexing options.
      elseif ($status === FALSE) {
        $indexList = Index::loadMultiple();
        foreach ($indexList as $index) {
          if ($index->getOption('index_directly')) {
            $indexHistory[] = $index->id();
            $index->setOption('index_directly', $status);
            $index->save();
          }
        }

        // Set if variable is exist.
        if (!empty($indexHistory) && isset($indexHistory)) {
          $config->set('ln_alkemics.alkemics_indexing_server', json_encode(array_unique($indexHistory)))
            ->save();
        }
      }
    }
  }


}
