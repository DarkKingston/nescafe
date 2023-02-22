<?php

namespace Drupal\dsu_core\Services;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\File\FileSystemInterface;

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
   * The file system service
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * ConfigReplace constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, FileSystemInterface $fileSystem, LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $fileSystem;
    $this->loggerFactory = $logger_factory->get('dsu_core');
  }


  /**
   * {@inheritdoc}
   */
  public function rewriteModuleConfig($module, $dir = 'install'){
    // Load the module extension.
    $extension = $this->moduleHandler->getModule($module);

    // Config rewrites are stored in 'modulename/config/$dire'.
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
    if (file_exists($rewrite_dir) && ($files = $this->fileSystem->scanDirectory($rewrite_dir, '/^.*\.yml$/i', ['recurse' => FALSE]))) {
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
  protected function rewriteConfig($original_config, $rewrite, $configName, $extensionName) {
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
}
