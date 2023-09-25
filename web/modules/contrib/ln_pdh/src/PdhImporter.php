<?php

namespace Drupal\ln_pdh;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\Element;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Error;
use Drupal\file\FileInterface;
use Drupal\ln_pdh\Form\PdhMappingForm;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\search_api\Entity\Index;

/**
 * PDH Importer service.
 *
 * @package Drupal\ln_pdh
 */
class PdhImporter implements PdhImporterInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Configuration state Drupal Site.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The PDH config  settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The PDH connector service.
   *
   * @var \Drupal\ln_pdh\PdhConnectorInterface
   */
  protected $pdhConnector;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * PdhImporter constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Configuration state Drupal Site.
   * @param \Drupal\ln_pdh\PdhConnectorInterface $pdh_connector
   *   The PDH connector service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File System interface.
   */
  public function __construct(ConfigFactory $config_factory, PdhConnectorInterface $pdh_connector, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, ModuleHandlerInterface $module_handler, StateInterface $state, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('ln_pdh.settings');
    $this->pdhConnector = $pdh_connector;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->state = $state;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function testConnection() {
    return $this->pdhConnector->testConnection();
  }

  /**
   * {@inheritdoc}
   */
  public function syncProduct($product) {
    // For each result we check if exists,
    // if needs to update or if needs to be created.
    if (isset($product->gtin)) {
      $product_info = $this->pdhConnector->getProductInfo($product->gtin, $product->label_version);
      // Avoid creating empty nodes.
      if ($product_info === FALSE) {
        $this->getLogger('pdh_connector')->error($this->t('Product @gtin could not be saved.', ['@gtin' => $product->gtin]));
        return NULL;
      }
      $storage = $this->entityTypeManager->getStorage('node');
      $entities = $storage->loadByProperties([
        'type' => 'dsu_product',
        'field_al_gtin' => $product->gtin,
      ]);

      if (!empty($entities)) {
        /** @var \Drupal\node\NodeInterface $node */
        $node = reset($entities);
        $this->getLogger('pdh_connector')->debug($this->t('Saved product @gtin', ['@gtin' => $product->gtin]));
        return $this->saveProduct($product_info, $node);
      }
      else {
        // The Product doesn't exists locally. Importing it.
        $this->getLogger('pdh_connector')->debug($this->t('Saved product @gtin', ['@gtin' => $product->gtin]));
        return $this->saveProduct($product_info);
      }
    }

    $this->getLogger('pdh_connector')->error($this->t('Product @gtin could not be saved.', ['@gtin' => $product->gtin]));
    return NULL;
  }

  /**
   * Saves product data given the full product info object and the Drupal node.
   *
   * @param \SimpleXMLElement $data
   *   Product data array.
   * @param \Drupal\node\NodeInterface|null $node
   *   (Optional) The node object. When provided, the node will be updated,
   *   otherwise a new node will be created.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Node if correctly saved or null if not.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  protected function saveProduct(\SimpleXMLElement $data, NodeInterface $node = NULL) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // For checking empty objects.
    $empty_object = new \stdClass();
    $item_langcode = $this->config->get('auth.langcode');

    // Check for old nodes.
    if (!isset($node)) {
      /* Generating node entity, and setting the content from the recipe*/
      $node = Node::create(['type' => 'dsu_product']);
      $node->enforceIsNew();
      $node->setUnpublished();
    }

    // Agency.
    $agency_text = '';
    foreach ($data->xpath('//tradeItem//alternateItemIdentification') as $item) {
      if (isset($item->agency) && $item->agency == '90') {
        $agency_text = (string) $item->id;
        break;
      }
    }

    // Get Synonyms.
    $synonyms = [];
    foreach ($data->xpath('//marketingInformationModule//multi') as $item) {
      if (isset($item->tradeItemKeyWords)) {
        $synonyms[] = (string) $item->tradeItemKeyWords->{$item_langcode};
      }
    }
    $synonyms_text = implode(' ', $synonyms);

    // Get Benefits.
    $feature_benefits = [];
    foreach ($data->xpath('//marketingInformationModule//tradeItemFeatureBenefit') as $item) {
      if (isset($item->featureBenefit->{$item_langcode}) && $item->featureBenefit->{$item_langcode} != $empty_object) {
        $feature_benefits[] = (string) $item->featureBenefit->{$item_langcode};
      }
    }

    // Get Marketing Message (it will be an array).
    $marketing_messages = [];
    foreach ($data->xpath('//marketingInformationModule//marketingMessage') as $item) {
      if (isset($item->tradeItemMarketingMessage->{$item_langcode})) {
        $marketing_messages[] = (string) $item->tradeItemMarketingMessage->{$item_langcode};
      }
    }

    // Get the images.
    $product_images = [];
    $text_images = [];
    $i = 0;
    foreach ($data->xpath('//referencedFileDetailInformationModule//externalFileLink//uniformResourceIdentifier') as $image) {
      $i++;
      $txt_img = (string) $image;
      $product_images[] = $txt_img;
      if (strlen($txt_img) > 255) {
        continue;
      }
      $text_images[] = $txt_img;

    }

    // Get list of fields.
    $map = $this->getMandatoryFieldMapping($item_langcode);
    $optional_map = $this->getOptionalFieldMapping($item_langcode);
    foreach ($map as $field => $config) {
      $values = $this->getDescendent($data, $config['path'], isset($config['value']) ? $config['value'] : '');
      if (!empty($values) && count($values) === 1) {
        $node->set($field, $values[0]);
        if ($field === 'title' && empty($values[0])) {
          $node->set($field, 'No title: ' . $data->GTIN);
        }
      }
      elseif (!empty($values)) {
        $node->set($field, $values);
      }
    }

    // Optional fields should be there after saving form.
    $optional_fields_config = $this->config->get('map');
    foreach ($optional_map as $field => $config) {
      $target_field = $optional_fields_config[$field][PdhMappingForm::CONFIG_FIELD_DESTINATION];
      if (empty($optional_fields_config[$field][PdhMappingForm::CONFIG_FIELD_ENABLED]) || !empty($config['manual'])) {
        continue;
      }
      if (!empty($config['paragraph'])) {
        $this->saveParagraphField($target_field, $item_langcode, $data, $node, $config['path']);
      }
      else {
        $this->saveProductField($optional_fields_config[$field][PdhMappingForm::CONFIG_FIELD_DESTINATION], $data, $config, $node);
      }
    }

    // Get number of servings per package (Now optional).
    if (!empty($optional_fields_config['field_al_number_of_servings'][PdhMappingForm::CONFIG_FIELD_ENABLED])) {
      if (isset($data->foodAndBeveragePreparationServingModule->numberOfServingsPerPackage) && isset($data->foodAndBeveragePreparationServingModule->measurementPrecisionOfNumberOfServingsPerPackage)) {
        $numberOfServings = (string)$data->foodAndBeveragePreparationServingModule->measurementPrecisionOfNumberOfServingsPerPackage .
          ':' . (string)$data->foodAndBeveragePreparationServingModule->numberOfServingsPerPackage;
        $node->set('field_al_number_of_servings', isset($numberOfServings) ? $numberOfServings : '');
      }
    }
    // Get target consumer gender list.
    $consumerGenders = [];
    if (!empty($optional_fields_config['field_al_consumer_gender'][PdhMappingForm::CONFIG_FIELD_ENABLED])) {
      $genderList = $data->xpath('//marketingInformationModule//targetConsumerGender');
      foreach ($genderList as $item) {
        $consumerGenders[] = (string)$item;
        $node->set('field_al_consumer_gender', isset($consumerGenders) ? $consumerGenders : '');
      }
    }
    // Get food and beverage ingredients.
    if (!empty($optional_fields_config['field_al_ingredient_description'][PdhMappingForm::CONFIG_FIELD_ENABLED])) {
      $ingredients = $data->xpath('//foodAndBeverageIngredientModule//foodAndBevIngredient');
      if (isset($data->foodAndBeverageIngredientModule->foodAndBevIngredient->ingredientName->{$item_langcode}) /*&& is_array($data->foodAndBeverageIngredientModule->foodAndBevIngredient->ingredientName)*/) {
        $ingredients = $data->foodAndBeverageIngredientModule->foodAndBevIngredient->ingredientName->{$item_langcode};
      }
      $node->set('field_al_ingredient_description', isset($ingredients) ? $ingredients : '');
    }

    $node->set('path', '/product/' . $data->GTIN);
    $node->set('langcode', $langcode);
    $node->set('field_al_pictures_f', isset($text_images) ? $text_images : '');
    $node->set('field_al_supplier_code', isset($agency_text) ? $agency_text : '');
    $node->set('field_al_keywords', isset($synonyms_text) ? $synonyms_text : '');

    $node->set('field_al_description', isset($marketing_messages) ? $marketing_messages : '');
    $node->set('field_al_consumerbenefits', isset($feature_benefits) ? $feature_benefits : '');

    // hook_alter for adding products from outside of module.
    $this->moduleHandler->alter('ln_pdh_import', $node, $data);

    try {
      $node->save();

      // Get the images from the API response using node id for the subfolders.
      if (!empty($product_images)) {
        $images = [];

        $i = 0;
        foreach ($product_images as $image) {
          $i++;
          // If the image is not already in the system we
          // download and create the Media Entity.
          if (!empty($image) && !$this->imageExists($image, $node->id())) {
            $file = $this->downloadImage($image, $node->id(), $i);
            if ($file) {
              $images[] = [
                'target_id' => $this->mediaCreate(
                  $file,
                  $langcode, $node->getTitle()
                )->id(),
              ];
            }
          }
        }
        if (!empty($images)) {
          $current_images = $node->get('field_dsu_image')->getValue();
          $images = array_merge($current_images, $images);

          $node->set('field_dsu_image', $images);
          $node->save();
          return $node;
        }
      }
      return $node;
    }
    catch (\Exception $e) {
      $this->getLogger('pdh_importer')
        ->notice('Cannot save node: ' . $e->getMessage());
      throw $e;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getProducts(\DateTime $since_date = NULL, $gtin = '', $label_version = '') {
    return $this->pdhConnector->getProducts($since_date, $gtin, $label_version);
  }

  /**
   * {@inheritdoc}
   */
  public function toggleSolrSearchIndexingServer($status) {
    // Get indexing for existing database server.
    if ($this->moduleHandler->moduleExists('search_api')) {
      $indexList = Index::loadMultiple();

      // Set and enable for indexing options.
      if ($status) {
        // Check if indexing server having list of index id.
        $indexHistory = $this->state->get('ln_pdh.search_indexes', []);
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
          $this->state->set('ln_pdh.search_indexes', array_unique($indexHistory));
        }
      }
    }
  }

  /**
   * Gets a nested value in product object or a default value if does not exist.
   *
   * @param \SimpleXMLElement $product
   *   The product info object.
   * @param string $path
   *   The product object path in XPath format.
   * @param mixed $null_value
   *   Default value to return if the expected path is not valid.
   *
   * @return mixed
   *   The value stored in the given path.
   */
  protected function getDescendent(\SimpleXMLElement $product, string $path, $null_value = NULL) {
    $values = $product->xpath($path);
    $result = [];
    if (is_array($values)) {
      foreach ($values as $value) {
        if (isset($value[0])) {
          $result[] = (string) $value[0];
        }
      }
    }
    else {
      $result[] = isset($product->xpath($path)[0]) ? (string) $product->xpath($path)[0] : '';
    }

    return (empty($result)) ? $null_value : $result;
  }

  /**
   * Downloads the given image to the right product folder.
   *
   * @param string $url
   *   The url of the image.
   * @param string $id
   *   The node id, to sort the images in subfolders.
   * @param $i
   *   counter to rename the file if needed.
   *
   * @return false|\Drupal\file\FileInterface
   *   The File entity.
   */
  protected function downloadImage(string $url, $id, $i) {
    $target_dir = $this->configFactory->get('system.file')->get('default_scheme') . '://pdh_product_images/' . $id;

    if ($this->fileSystem->prepareDirectory($target_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $info = pathinfo($url);
      $filename = (strlen($info['basename']) > 255) ? 'image_' . $i . '.' . $info['extension'] : $info['basename'];
      $destination = $target_dir . DIRECTORY_SEPARATOR . $filename;

      return system_retrieve_file($url, $destination, TRUE, FileSystemInterface::EXISTS_REPLACE);
    }

    return FALSE;
  }

  /**
   * Checks if an image exists already.
   *
   * @param string $url
   *   The url of the image to be downloaded.
   * @param string $id
   *   The node id to know the subfolder.
   *
   * @return bool
   *   TRUE if the image is in the system, FALSE otherwise.
   */
  protected function imageExists(string $url, $id) {
    $target_dir = $this->configFactory->get('system.file')->get('default_scheme') . '://pdh_product_images/' . $id;

    return file_exists($target_dir . DIRECTORY_SEPARATOR . pathinfo($url, PATHINFO_BASENAME));
  }

  /**
   * Creates the Media entity with the File.
   *
   * @param \Drupal\file\FileInterface $file
   *   File entity referenced by the Media.
   * @param string $langcode
   *   Langcode of the content.
   * @param string $title
   *   Node title to use it.
   *
   * @return \Drupal\media\Entity\Media|false
   *   The Media entity.
   */
  protected function mediaCreate(FileInterface $file, string $langcode, string $title) {
    $media = Media::create([
      'bundle' => 'image',
      'langcode' => $langcode,
      'name' => $file->label(),
    ]);
    // We saw different versions of the entity with different fields.
    if ($media->hasField('field_media_image')) {
      $media->set('field_media_image', [
        'target_id' => $file->id(),
        'alt' => $this->t('Picture of %title', ['%title' => $title]),
        'title' => $this->t('Picture of %title', ['%title' => $title]),
      ]);
    }
    elseif ($media->hasField('image')) {
      $media->set('image', [
        'target_id' => $file->id(),
        'alt' => $this->t('Picture of %title', ['%title' => $title]),
        'title' => $this->t('Picture of %title', ['%title' => $title]),
      ]);
    }
    $media->setPublished();
    try {
      $media->save();
    }
    catch (EntityStorageException $e) {
      $variables = Error::decodeException($e);
      $this->getLogger('pdh_importer')->log(isset($variables['severity']) ? $variables['severity'] : 'error', 'Cannot save image. %type: @message in %function (line %line of %file). <br /> @backtrace_string', $variables);

      return FALSE;
    }

    return $media;
  }

  /**
   * Returns the list of mandatory fields and their XML location.
   *
   * @param string $item_langcode
   *   Langcode with usual iso xx_XX format.
   *
   * @return \string[][]
   *   Array of field info keyed by name.
   */
  public function getMandatoryFieldMapping($item_langcode) {
    /*
     * Map array to simplify the field mapping between PDH & Drupal.
     * This is the format
     *   'field_name_drupal' => [
     *     'path/to/property/in/pdh',
     *     "value if not set, default to ''",
     *   ]
     */
    $map = [
      'title' => [
        'path' => '//tradeItemDescriptionModule//shortDescription//' . $item_langcode,
        'value' => 'No title',
      ],
      'field_dsu_product_desc' => [
        'path' => '//tradeItemDescriptionModule//productDescription//' . $item_langcode,
      ],
      'field_al_gtin' => [
        'path' => '//GTIN',
      ],
      'field_dsu_sku' => [
        'path' => '//GTIN',
      ],
      'field_pdh_label_version' => [
        'path' => '//PDHID',
      ],
      'field_al_sub_brand_text' => [
        'path' => '//tradeItemDescriptionModule//subBrand',
      ],
      'field_al_name_public_long' => [
        'path' => '//tradeItemDescriptionModule//productDescription//' . $item_langcode,
      ],
      'field_al_name_public_short' => [
        'path' => '//tradeItemDescriptionModule//shortDescription//' . $item_langcode,
      ],
      'field_al_product_benefits' => [
        'path' => '//tradeItemDescriptionModule//tradeItemDescriptionInformation//additionalDescription//' . $item_langcode,
      ],
    ];

    return $map;
  }

  /**
   * Returns the list of optional fields and their XML location.
   *
   * @param string $item_langcode
   *   Langcode in usual iso format xx_XX.
   *
   * @return \string[][]
   *   Array of field info keyed by name.
   */
  public function getOptionalFieldMapping($item_langcode) {
    /*
     * Map array to simplify the field mapping between PDH & Drupal.
     * This is the format
     *   'field_name_drupal' => [
     *     'path/to/property/in/pdh',
     *     "value if not set, default to ''",
     *   ]
     */
    $map = [
      // Previous mandatory fields.
      'field_al_ingredient_description' => [
        'label' => 'Description of the ingredient / Ingredient name',
        // Unused, manual handling.
        'path' => '//foodAndBeverageIngredientModule//foodAndBevIngredient',
        'id' => 434,
        'manual' => TRUE,
      ],
      'field_al_health_allegations' => [
        'label' => 'Health Claim Description',
        'path' => '//healthRelatedInformationModule//healthClaimDescription//' . $item_langcode,
        'id' => 488,
        'length' => 3000,
      ],
      'field_al_coffee_variety' => [
        'label' => 'Flavor',
        'path' => '//tradeItemDescriptionModule//tradeItemVariant//tradeItemVariantValue//' . $item_langcode,
        'id' => 140,
      ],
      'field_al_consumer_age' => [
        'label' => 'Target Consumer age',
        'path' => '//marketingInformationModule//targetConsumerAge//' . $item_langcode,
        'id' => 241,
      ],
      'field_al_consumer_gender' => [
        'label' => 'Target consumer gender',
        // Unused, manual handling.
        'path' => '//marketingInformationModule//targetConsumerGender',
        'id' => 242,
        'manual' => TRUE,
        'cardinality' => -1,
      ],
      'field_al_number_of_servings' => [
        'label' => 'Number of servings',
        // Unused, manual handling.
        'path' => '//foodAndBeveragePreparationServingModule//numberOfServingsPerPackage',
        'id' => 457,
        'manual' => TRUE,
      ],
      'field_al_nutrients' => [
        'label' => 'Nutrients/Substance',
        // Unused, manual handling.
        'path' => '//nutritionalInformationModule//multi//descriptionOnANutrient//' . $item_langcode,
        'id' => 549,
        'length' => 3000,
      ],
      'field_al_good_to_know' => [
        'label' => 'Good To Know',
        'path' => '//nonGDSN//label//compass//goodToKnow//' . $item_langcode,
        'id' => 518,
        'length' => 3000,
      ],
      'field_al_good_to_remember' => [
        'label' => 'Good To Remember',
        'path' => '//nonGDSN//label//compass//goodToRemember//' . $item_langcode,
        'id' => 519,
        'length' => 3000,
      ],
      'field_al_good_question' => [
        'label' => 'Good Question',
        'id' => 517,
        'path' => '//nonGDSN//label//compass//goodQuestion//' . $item_langcode,
        'length' => 3000,
      ],
      'field_al_allergens' => [
        'label' => 'Allergen Statement',
        'id' => 429,
        'path' => '//allergenInformationModule//allergenRelatedInformation//allergenStatement//' . $item_langcode,
      ],
      // New optional fields.
      'field_pdh_brand_name' => [
        'label' => 'Brand Name',
        'id' => 11,
        'path' => '//tradeItemDescriptionModule//brandName',
      ],
      'field_pdh_ctlg_price' => [
        'label' => 'Catalogue Price',
        'id' => 325,
        'path' => '//salesInformationModule//cataloguePrice//price',
      ],
      'field_pdh_ctlg_price_currency' => [
        'label' => 'Catalogue Price Currency',
        'id' => 326,
        'path' => '//salesInformationModule//cataloguePrice//currency',
      ],
      'field_pdh_cert_agency' => [
        'label' => 'Certification Agency',
        'id' => 478,
        'path' => '//certificationInformationModule//certificationInformation//certificationAgency',
      ],
      'field_pdh_cert_standard' => [
        'label' => 'Certification Standard',
        'id' => 479,
        'path' => '//certificationInformationModule//certificationInformation//certificationStandard',
      ],
      'field_pdh_cert_value' => [
        'label' => 'Certification Value',
        'id' => 480,
        'path' => '//certificationInformationModule//certificationInformation//certification//certificationValue',
      ],
      'field_pdh_comm_address' => [
        'label' => 'Communication Address',
        'id' => 106,
        'path' => '//tradeItem//tradeItemContactInfo//contactAddress',
      ],
      'field_pdh_comp_additives_label' => [
        'label' => 'Compulsory Additives Label Information',
        'id' => 486,
        'path' => '//healthRelatedInformationModule//compulsoryAdditivesLabelInformation//' . $item_langcode,
      ],
      'field_pdh_cons_storage_instr' => [
        'label' => 'Consumer Storage Instructions',
        'id' => 470,
        'path' => '//consumerInstructionsModule//multi//consumerStorageInstructions//' . $item_langcode,
      ],
      'field_pdh_cons_usage_instr' => [
        'label' => 'Consumer Usage Instructions',
        'id' => 471,
        'path' => '//consumerInstructionsModule//multi//consumerUsageInstructions//' . $item_langcode,
      ],
      'field_pdh_contact_name' => [
        'label' => 'Contact Name',
        'id' => 117,
        'path' => '//tradeItem//tradeItemContactInfo//contactName',
      ],
      'field_pdh_contact_type' => [
        'label' => 'Contact Type',
        'id' => 91,
        'path' => '//tradeItem//tradeItemContactInfo//contactType',
      ],
      'field_pdh_depth' => [
        'label' => 'Depth',
        'id' => 56,
        'path' => '//tradeItemMeasurementsModule//depth//value',
      ],
      'field_pdh_depth_uom' => [
        'label' => 'Depth UoM',
        'id' => 57,
        'path' => '//tradeItemMeasurementsModule//depth//uom',
      ],
      'field_pdh_diet_type_code' => [
        'label' => 'Diet Type Code',
        'id' => 452,
        'path' => '//dietInformationModule//foodAndBevDietTypeInfo//dietTypeCode',
        'cardinality' => -1,
      ],
      'field_pdh_functional_name' => [
        'label' => 'Functional Name',
        'id' => 134,
        'path' => '//tradeItemDescriptionModule//functionalName//' . $item_langcode,
      ],
      'field_pdh_gross_weight' => [
        'label' => 'Gross Weight',
        'id' => 66,
        'path' => '//tradeItemMeasurementsModule//grossWeight//value',
      ],
      'field_pdh_gross_weight_uom' => [
        'label' => 'Gross Weight UoM',
        'id' => 67,
        'path' => '//tradeItemMeasurementsModule//grossWeight//uom',
      ],
      'field_pdh_height' => [
        'label' => 'Height',
        'id' => 58,
        'path' => '//tradeItemMeasurementsModule//height//value',
      ],
      'field_pdh_height_uom' => [
        'label' => 'Height UoM',
        'id' => 59,
        'path' => '//tradeItemMeasurementsModule//height//uom',
      ],
      'field_pdh_ingr_definition' => [
        'label' => 'Ingredient Definition',
        'id' => 436,
        'path' => '//foodAndBeverageIngredientModule//foodAndBevIngredient//ingredientDefinition//' . $item_langcode,
      ],
      'field_pdh_ingr_statement' => [
        'label' => 'Ingredient Statement',
        'id' => 421,
        'path' => '//foodAndBeverageIngredientModule//ingredientStatement//' . $item_langcode,
      ],
      'field_pdh_allergen_type_level' => [
        'label' => 'Allergen Type Code and Level of Containment',
        'id' => 430,
        'paragraph' => TRUE,
        'manual' => TRUE,
        'cardinality' => -1,
        'path' => '//allergenInformationModule//allergenRelatedInformation//allergen',
      ],
      // Second batch.
      'field_pdh_net_content' => [
        'label' => 'Net Content',
        'id' => 4,
        'path' => '//tradeItemMeasurementsModule//netContent//value',
      ],
      'field_pdh_net_content_statement' => [
        'label' => 'Net Content Statement',
        'id' => 72,
        'path' => '//tradeItemMeasurementsModule//netContentStatement//' . $item_langcode,
      ],
      'field_pdh_net_content_uom' => [
        'label' => 'Net Content UoM',
        'id' => 5,
        'path' => '//tradeItemMeasurementsModule//netContent//uom',
      ],
      'field_pdh_nutr_basis_quantity' => [
        'label' => 'Nutrient Basis Quantity',
        'id' => 196,
        'path' => '//nutritionalInformationModule//nutrientInformation//nutrientBasisQuantity//value',
        'cardinality' => -1,
      ],
      'field_pdh_nutr_b_qtty_type' => [
        'label' => 'Nutrient Basis Quantity Type Code',
        'id' => 195,
        'path' => '//nutritionalInformationModule//nutrientInformation//nutrientBasisQuantityTypeCode',
        'cardinality' => -1,
      ],
      'field_pdh_nutr_b_qtty_uom_p' => [
        'label' => 'Nutrient Basis Quantity UoM',
        'id' => 537,
        'path' => '//nutritionalInformationModule//nutrientInformation//nutrientBasisQuantity//uom',
        'cardinality' => -1,
      ],
      'field_pdh_nutritient_info' => [
        'label' => 'Nutrient Information',
        'id' => 557,
        'paragraph' => TRUE,
        'cardinality' => -1,
        'path' => '//nutritionalInformationModule//nutrientInformation',
      ],
      'field_pdh_regulatory_info' => [
        'label' => 'Regulatory info',
        'id' => 335,
        'paragraph' => TRUE,
        'cardinality' => -1,
        'path' => '//regulatedTradeItemModule//regulatoryInformation',
      ],
      'field_pdh_nutr_claim' => [
        'label' => 'Nutritional Claim',
        'id' => 424,
        'path' => '//nutritionalInformationModule//nutritionalClaim//' . $item_langcode,
      ],
      'field_pdh_organic_claim' => [
        'label' => 'Organic Claim',
        'id' => 476,
        'paragraph' => TRUE,
        'cardinality' => -1,
        'path' => '//farmingAndProcessingInformationModule//organicClaim'
      ],
      'field_pdh_pack_accr_code' => [
        'label' => 'Packaging Marked Label Accreditation Code',
        'id' => 154,
        'path' => '//packagingMarkingModule//packagingMarkedLabelAccreditationCode',
      ],
      'field_pdh_org_prod_farming' => [
        'label' => 'Organic Product Place of Farming',
        'id' => 474,
        'path' => '//farmingAndProcessingInformationModule//organicProductPlaceOfFarmingCode',
      ],
      'field_pdh_pack_type_code' => [
        'label' => 'Packaging Type Code',
        'id' => 162,
        'path' => '//packagingInformationModule//packagingInformation//packagingTypeCode',
      ],
      'field_pdh_prep_instructions' => [
        'label' => 'Preparation Instructions',
        'id' => 463,
        'path' => '//foodAndBeveragePreparationServingModule//foodAndBevPreparationInfo//preparationInstructions//' . $item_langcode,
      ],
      'field_pdh_prod_act_region' => [
        'label' => 'Product Activity Region Description',
        'id' => 441,
        'path' => '//foodAndBeverageIngredientModule//foodAndBevIngredient//ingredientPlaceOfActivity//productActivityDetails//productActivityRegionDescription//' . $item_langcode,
      ],
      'field_pdh_prod_act_type_code' => [
        'label' => 'Product Activity Type Code',
        'id' => 515,
        'path' => '//foodAndBeverageIngredientModule//foodAndBevIngredient//ingredientPlaceOfActivity//productActivityDetails//productActivityTypeCode',
      ],
      'field_pdh_sap_material' => [
        'label' => 'SAP Material',
        'id' => 408,
        'path' => '//sapMaterialNumber',
      ],
      'field_pdh_serv_size' => [
        'label' => 'Serving Size',
        'id' => 198,
        'path' => '//nutritionalInformationModule//nutrientInformation//servingSize//value',
        'cardinality' => -1,
      ],
      'field_pdh_serv_size_uom' => [
        'label' => 'Serving Size UoM',
        'id' => 199,
        'path' => '//nutritionalInformationModule//nutrientInformation//servingSize//uom',
        'cardinality' => -1,
      ],
      'field_pdh_width' => [
        'label' => 'Width',
        'id' => 60,
        'path' => '//tradeItemMeasurementsModule//width//value',
      ],
      'field_pdh_width_uom' => [
        'label' => 'Width UoM',
        'id' => 61,
        'path' => '//tradeItemMeasurementsModule//width//uom',
      ],
      'field_pdh_warn_copy_descr' => [
        'label' => 'Warning Copy Description',
        'id' => 161,
        'path' => '//packagingMarkingModule//warningCopyDescriptions//warningCopyDescription//' . $item_langcode,
      ],
      'field_pdh_au_health_star_rating' => [
        'label' => 'AU health star rating',
        'id' => 657,
        'path' => '//tradeItem//healthStarRating//healthStarRatingValue',
      ],
    ];

    return $map;
  }

  /**
   * Returns field names and XML locations for complex fields store together.
   *
   * @param string $name
   *   Name of the complex field.
   *
   * @param string $item_langcode
   *   Langcode in usual iso format xx_XX.
   *
   * @return \string[][]
   *   Array of field info keyed by name.
   */
  public function getParagraphDefinition(string $name, string $item_langcode) {
    $definitions = [
      'field_pdh_allergen_type_level' => [
        '#paragraph_type' => 'pdh_allergen_type_level',
        'field_pdh_allergen_type_code' => [
          'label' => 'Allergen Type Code',
          'id' => 430,
          'path' => 'allergenTypeCode',
          'cardinality' => -1,
        ],
        'field_pdh_level_containment' => [
          'label' => 'Level of Containment',
          'id' => 431,
          'path' => 'levelofContainmentCode',
          'cardinality' => -1,
        ],
      ],
      'field_pdh_organic_claim' => [
        '#paragraph_type' => 'pdh_organic_claim',
        'field_pdh_org_claim_agency' => [
          'label' => 'Organic Claim Agency',
          'id' => 476,
          'path' => '//farmingAndProcessingInformationModule//organicClaim//organicClaimAgencyCode',
        ],
        'field_pdh_trade_item_code' => [
          'label' => 'Organic Trade Item Code',
          'id' => 477,
          'path' => 'organicTradeItemCode',
        ],
      ],
      'field_pdh_regulatory_info' => [
        '#paragraph_type' => 'pdh_regulatory_info',
        'field_pdh_reg_type_code' => [
          'label' => 'Regulation Type Code',
          'id' => 335,
          'path' => 'regulationTypeCode',
        ],
        'field_pdh_reg_act' => [
          'label' => 'Regulatory Act',
          'id' => 336,
          'path' => 'regulatoryAct',
        ],
        'field_pdh_reg_agency' => [
          'label' => 'Regulatory Agency',
          'id' => 337,
          'path' => 'regulatoryAgency',
        ],
      ],
      'field_pdh_nutritient_info' => [
        '#paragraph_type' => 'pdh_nutritient_info',
        'field_pdh_preparation_state' => [
          'label' => 'Preparation State',
          'id' => 557,
          'path' => 'preparationStateCode',
          'cardinality' => 1,
        ],
        'field_pdh_daily_value_intake' => [
          'label' => 'Daily Value Intake Reference',
          'id' => 194,
          'path' => 'dailyValueIntakeReference//' . $item_langcode,
        ],
        'field_pdh_serv_size_descr' => [
          'label' => 'Serving Size Description',
          'id' => 197,
          'path' => 'servingSizeDescription//' . $item_langcode,
        ],
        'field_pdh_nutritient_detail' => [
          'label' => 'Nutrient Detail',
          'id' => 200,
          '#paragraph' => TRUE,
          'cardinality' => -1,
          'path' => 'nutrientDetail',
        ],
      ],
      'field_pdh_nutritient_detail' => [
        '#paragraph_type' => 'pdh_nutritient_detail',
        'field_pdh_nutr_type_code' => [
          'label' => 'Nutrient Type Code',
          'id' => 200,
          'path' => 'nutrientTypeCode',
          'cardinality' => 1,
        ],
        'field_pdh_qtty_precision_code' => [
          'label' => 'Measurement Precision Code',
          'id' => 203,
          'path' => 'measurementPrecisionCode',
          'cardinality' => 1,
        ],
        'field_pdh_nutritient_quant' => [
          'label' => 'Quantity contained',
          'id' => 205,
          'cardinality' => -1,
        ],
        'field_pdh_perc_daily_intake' => [
          'label' => 'Percentage of Daily Value Intake',
          'id' => 201,
          'path' => 'dailyValueIntakePercent',
          'cardinality' => 1,
        ],
      ],

    ];

    if (isset($definitions[$name])) {
      return $definitions[$name];
    }
    return [];
  }

  /**
   * For standard fields extract them from the XML and store them in the entity.
   *
   * @param $target_field
   * @param \SimpleXMLElement $data
   * @param array $config
   * @param $entity
   */
  protected function saveProductField($target_field, \SimpleXMLElement $data, array $config, $entity): void {
    $values = $this->getDescendent($data, $config['path'], $config['value'] ?? '');
    if (!empty($values) && $entity->hasField($target_field)) {
      if (count($values) === 1) {
        $entity->set($target_field, $values[0]);
      }
      else {
        $entity->set($target_field, $values);
      }
    }
  }

  /**
   * Extract XML data and stores it in paragraphs.
   *
   * @param $target_field
   * @param $item_langcode
   * @param \SimpleXMLElement $data
   * @param $entity
   * @param $path
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function saveParagraphField($target_field, $item_langcode, \SimpleXMLElement $data, $entity, $path): void {
    // Get the subfields of the paragraph and the paragraph type.
    $paragraph_fields_map = $this->getParagraphDefinition($target_field, $item_langcode);

    // Extract the paragraph values from the XML.
    $paragraph_data = $data->xpath($path);
    // Get the subfields data from the subset of data.
    $values = $this->extractComplexValues($paragraph_fields_map, $paragraph_data);

    $this->storeParagraphData($values, $target_field, $entity);
  }


  /**
   * Given a complex field definition it extracts the tree of values.
   *
   * @param array $paragraph_fields_map
   *   The structure of the complex field.
   * @param array $data
   *   The XML data imported.
   *
   * @return array $paragraph_field_values
   *   Array to store the results.
   */
  protected function extractComplexValues(array $paragraph_fields_map, array $data): array {
    $paragraph_field_values = [];

    for ($i = 0; $i < count($data); $i++) {
      foreach (Element::children($paragraph_fields_map) as $paragraph_field) {
        $config = $paragraph_fields_map[$paragraph_field];
        if (isset($config['#paragraph']) && $config['#paragraph']) {
          $paragraph_subfields_map = $this->getParagraphDefinition($paragraph_field, '');

          $subtree = $data[$i]->xpath($config['path']);
          $values = $this->extractComplexValues($paragraph_subfields_map, $subtree);
        }
        elseif ($paragraph_field === 'field_pdh_nutritient_quant') {
          $num_values = $this->getDescendent($data[$i], 'quantityContained//value', $config['value'] ?? '');
          $uom_values = $this->getDescendent($data[$i], 'quantityContained//uom', $config['value'] ?? '');
          $values = [];
          if (is_array($num_values)) {
            $total_nutritient_quant = count($num_values);
          }
          elseif (is_array($uom_values)) {
            $total_nutritient_quant = count($uom_values);
          }
          else {
            $total_nutritient_quant = 0;
          }
          for ($j = 0; $j < $total_nutritient_quant; $j++) {
            $values[] = $num_values[$j] . ' ' . $uom_values[$j];
          }
        }
        else {
          $values = $this->getDescendent($data[$i], $config['path'], $config['value'] ?? '');
        }

        if (!empty($values)) {
          $paragraph_field_values[$i][$paragraph_field] = $values;
        }
      }
    }

    return $paragraph_field_values;
  }

  /**
   * Stores a set of data into an entity for a specific field/paragraph.
   *
   * @param $values
   * @param $target_field
   * @param $entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function storeParagraphData($values, $target_field, $entity) {
    // Get the subfields of the paragraph and the paragraph type.
    $paragraph_fields_map = $this->getParagraphDefinition($target_field, '');

    // Extract the current values to delete paragraphs if needed.
    $current_values = $entity->get($target_field)->getValue();
    $count_new_values = count($values);
    $paragraph_type = $paragraph_fields_map['#paragraph_type'];

    if (!empty($values)) {
      $new_values = [];

      // Create the paragraph if needed or load if there's one already.
      for ($i = 0; $i < $count_new_values; $i++) {
        if (!isset($current_values[$i])) {
          $paragraph = Paragraph::create(['type' => $paragraph_type]);
          $paragraph->enforceIsNew();
        }
        else {
          $paragraph = Paragraph::load($current_values[$i]['target_id']);
        }
        foreach (array_keys($values[$i]) as $paragraph_field) {
          if (isset($paragraph_fields_map[$paragraph_field]['#paragraph']) && $paragraph_fields_map[$paragraph_field]['#paragraph']) {
            $this->storeParagraphData($values[$i][$paragraph_field], $paragraph_field, $paragraph);
          }
          else {
            $paragraph->set($paragraph_field, $values[$i][$paragraph_field]);
          }
        }
        $paragraph->save();
        $new_values[$i] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->get('revision_id')
            ->getValue()[0]['value'],
        ];
      }
      $entity->set($target_field, $new_values);
    }

    // Remove the paragraphs that won't be part of the node any longer.
    if (count($current_values) > $count_new_values) {
      $orphan_paragraphs = array_slice($current_values, $count_new_values);

      foreach ($orphan_paragraphs as $orphan_paragraph) {
        $paragraph = Paragraph::load($orphan_paragraph['target_id']);
        $paragraph->delete();
      }
    }
  }
}
