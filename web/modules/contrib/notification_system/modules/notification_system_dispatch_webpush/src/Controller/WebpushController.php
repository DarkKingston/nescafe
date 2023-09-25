<?php

namespace Drupal\notification_system_dispatch_webpush\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Notification System Dispatch Web Push routes.
 */
class WebpushController extends ControllerBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   */
  public function __construct(FileSystemInterface $file_system, ModuleExtensionList $extension_list_module) {
    $this->fileSystem = $file_system;
    $this->extensionListModule = $extension_list_module;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('extension.list.module')
    );
  }

  /**
   * Serves the service worker file.
   */
  public function serviceWorker(): CacheableResponse {
    $modulePath = $this->extensionListModule->getPath('notification_system_dispatch_webpush');
    $serviceWorker = file_get_contents($modulePath . '/js/webpush_serviceworker.js');

    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->setCacheMaxAge(1);

    $response = new CacheableResponse($serviceWorker, 200, [
      'Service-Worker-Allowed' => '/',
      'Content-Type' => 'application/javascript',
    ]);

    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
