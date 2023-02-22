<?php

namespace Drupal\ln_srh\Services;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ctools\Plugin\Block\EntityView;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\ParagraphsType;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\File\FileSystemInterface;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Provides methods to rewrite configuration.
 */
class ConfigReplace implements ConfigReplaceInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /** @var \Drupal\Core\File\FileSystemInterface */
  protected $fileSystem;

  /**
   * ConfigReplace constructor.
   * @param FileSystemInterface $fileSystem
   * @param ConfigFactoryInterface $config_factory
   * @param ModuleHandlerInterface $module_handler
   * @param LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(FileSystemInterface $fileSystem, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->loggerFactory = $logger_factory->get('ln_srh');
    $this->fileSystem = $fileSystem;
  }

  /**
   * @param $module
   *
   */
  public function rewriteModuleConfig($module,$dir='rewrite') {
    // Load the module extension.
    $extension = $this->moduleHandler->getModule($module);

    // Config rewrites are stored in 'modulename/config/rewrite_dir'.
    $dir_base = $extension->getPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $dir;

    $this->rewriteDirectoryConfig($extension, $dir_base);
  }

  /**
   * @param $extension
   * @param $rewrite_dir
   *
   */
  protected function rewriteDirectoryConfig($extension, $rewrite_dir) {
    // Scan the rewrite directory for rewrites.
    if (file_exists($rewrite_dir) && $files = $this->fileScanDirectory($rewrite_dir, '/^.*\.yml$/i', ['recurse' => FALSE])) {
      foreach ($files as $file) {
        // Parse the rewrites and retrieve the original config.
        $rewrite = Yaml::parse(file_get_contents($rewrite_dir . DIRECTORY_SEPARATOR . $file->name . '.yml'));
        $config = $this->configFactory->getEditable($file->name);
        $original_data = $config->getRawData();
        if(empty($original_data)){
          $original_data = $rewrite;
        }
        $rewrite = $this->rewriteConfig($original_data, $rewrite, $file->name, $extension->getName());

        // Retain the original 'uuid' and '_core' keys if it's not explicitly
        // asked to rewrite them.
        if (isset($rewrite['config_replace_uuids'])) {
          unset($rewrite['config_replace_uuids']);
        }
        else {
          foreach (['_core', 'uuid'] as $key) {
            if (isset($original_data[$key])) {
              $rewrite[$key] = $original_data[$key];
            }
          }
        }

        // Save the rewritten configuration data.
        $result = $config->setData($rewrite)->save() ? 'rewritten' : 'not rewritten';

        // Log a message indicating whether the config was rewritten or not.
        $this->loggerFactory->notice('@config @result by @module', ['@config' => $file->name, '@result' => $result, '@module' => $extension->getName()]);
      }
    }
  }

  /**
   * @param array $original_config
   * @param array $rewrite
   * @param string $configName
   * @param string $extensionName
   *
   * @return array
   */
  public function rewriteConfig($original_config, $rewrite, $configName, $extensionName) {
    if (empty($original_config)) {
      $log = 'Tried to replace config @config by @module module without initial config.';
      $this->loggerFactory->error($log, ['@config' => $configName, '@module' => $extensionName]);
      throw new \Exception("Tried to replace config $configName by $extensionName module without initial config.");
    }

    if (isset($rewrite['config_replace']) && $rewrite['config_replace'] == 'replace') {
      return $rewrite;
    }
    return NestedArray::mergeDeep($original_config, $rewrite);
  }

  /**
   * Wraps file_scan_directory().
   *
   * @param $dir
   *   The base directory or URI to scan, without trailing slash.
   * @param $mask
   *   The preg_match() regular expression for files to be included.
   * @param $options
   *   An associative array of additional options.
   *
   * @return array
   *   An associative array (keyed on the chosen key) of objects with 'uri',
   *   'filename', and 'name' properties corresponding to the matched files.
   */
  protected function fileScanDirectory($dir, $mask, $options = array()) {
    return $this->fileSystem->scanDirectory($dir, $mask, $options);
  }

  /**
   * @param $ymlPath
   */
  public function createParagraphTypeConfigByYml($ymlPath){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if(!ParagraphsType::load($yml['id'])){
      try {
        ParagraphsType::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The @bundle Paragraph type has been created successfully.',['@bundle' => $yml['label']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addError(t('An error occurred while creating the @bundle paragraph type.',['@bundle' => $yml['label']]));
      }
    }
  }

  /**
   * @param $ymlPath
   */
  public function createVocabularyConfigByYml($ymlPath) {
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!Vocabulary::load($yml['vid'])) {
      try {
        Vocabulary::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The @bundle vocabulary has been created successfully.', ['@bundle' => $yml['name']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addError(t('An error occurred while creating the @bundle vocabulary.', ['@bundle' => $yml['name']]));
      }
    }
  }

  /**
   * @param $ymlPath
   * @param $fieldName
   * @param $configName
   */
  public function addFieldToEntityFormDisplayConfigByYml($ymlPath, $fieldName, $configName){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!$entityFormDisplay = EntityFormDisplay::load($yml['id'])) {
      try {
        EntityFormDisplay::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The Entity Form Display @id has been created successfully.',['@id' => $yml['id']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addStatus(t('An error occurred while creating the @id Entity Form Display.',['@id' => $yml['id']]));
      }
    }else{
      $config = $entityFormDisplay->get('config');
      if(!isset($config['dependencies'][$configName]) && isset($yml['config']['dependencies'][$configName])){
        $config['dependencies'][$configName] = $yml['config']['dependencies'][$configName];
        $entityFormDisplay->set('config',$config);
      }
      $content = $entityFormDisplay->get('content');
      if(!isset($content[$fieldName]) && isset($yml['content'][$fieldName])){
        $content[$fieldName] = $yml['content'][$fieldName];
        $entityFormDisplay->set('content',$content);
      }
      try {
        $entityFormDisplay->save();
        \Drupal::messenger()->addStatus(t('The @field_name field has been added to the @entityFormDisplay form',['@field_name' => $fieldName, '@entityFormDisplay' => $yml['id']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addError(t('It was not possible to add the @field_name field to the @entityFormDisplay form',['@field_name' => $fieldName, '@entityFormDisplay' => $yml['id']]));
      }
    }
  }

  /**
   * @param $ymlPath
   */
  public function createFieldStorageConfigByYml($ymlPath){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!FieldStorageConfig::loadByName($yml['entity_type'], $yml['field_name'])) {
      try {
        FieldStorageConfig::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The @field_name field has been created successfully.',['@field_name' => $yml['field_name']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addError(t('It was not possible to create the @field_name field.',['@field_name' => $yml['field_name']]));
      }
    }
  }

  /**
   * @param $ymlPath
   */
  public function createFieldConfigByYml($ymlPath){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!FieldConfig::loadByName($yml['entity_type'], $yml['bundle'], $yml['field_name'])) {
      try {
        FieldConfig::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The @field_name field has been included in the @bundle @entity_type successfully.',['@field_name' => $yml['field_name'], '@entity_type' => $yml['entity_type'], '@bundle' => $yml['bundle']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addError(t('An error occurred when including the @field_name field in the @bundle @entity_type.',['@field_name' => $yml['field_name'], '@entity_type' => $yml['entity_type'], '@bundle' => $yml['bundle']]));
      }
    }
  }

  /**
   * @param $ymlPath
   */
  public function createEntityFormDisplayByYml($ymlPath){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!$entityFormDisplay = EntityFormDisplay::load($yml['id'])) {
      try {
        EntityFormDisplay::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The Entity Form Display @id has been created successfully.',['@id' => $yml['id']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addStatus(t('An error occurred while creating the @id Entity Form Display.',['@id' => $yml['id']]));
      }
    }
  }

  /**
   * @param $ymlPath
   */
  public function createEntityViewDisplayByYml($ymlPath){
    $yml = Yaml::parse(file_get_contents($ymlPath));
    if (!$entityViewDisplay = EntityViewDisplay::load($yml['id'])) {
      try {
        EntityViewDisplay::create($yml)->save();
        \Drupal::messenger()->addStatus(t('The Entity View Display @id has been created successfully.',['@id' => $yml['id']]));
      } catch (EntityStorageException $e) {
        \Drupal::messenger()->addStatus(t('An error occurred while creating the @id Entity View Display.',['@id' => $yml['id']]));
      }
    }
  }

}
