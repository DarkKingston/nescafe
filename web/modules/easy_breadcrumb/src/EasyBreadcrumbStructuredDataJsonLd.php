<?php

namespace Drupal\easy_breadcrumb;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class EasyBreadcrumbStructuredDataJsonLd.
 *
 * @package Drupal\easy_breadcrumb
 */
class EasyBreadcrumbStructuredDataJsonLd implements ContainerInjectionInterface {

  /**
   * The Easy Breadcrumb builder.
   *
   * @var \Drupal\easy_breadcrumb\EasyBreadcrumbBuilder
   */
  protected $easyBreadcrumbBuilder;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * EasyBreadcrumbStructuredDataJsonLd constructor.
   *
   * @param \Drupal\easy_breadcrumb\EasyBreadcrumbBuilder $easy_breadcrumb_builder
   *   The Easy Breadcrumb builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler;
   */
  public function __construct(EasyBreadcrumbBuilder $easy_breadcrumb_builder, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match, ModuleHandlerInterface $module_handler) {
    $this->easyBreadcrumbBuilder = $easy_breadcrumb_builder;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('easy_breadcrumb.breadcrumb'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('module_handler')
    );
  }

  /**
   * Build JSON-LD value.
   */
  public function value() {

    $value = FALSE;

    $config = $this->configFactory->get(EasyBreadcrumbConstants::MODULE_SETTINGS);
    if ($config->get(EasyBreadcrumbConstants::ADD_STRUCTURED_DATA_JSON_LD)) {

      /** @var \Drupal\Core\Breadcrumb\Breadcrumb $breadcrumb */
      $breadcrumb = $this->easyBreadcrumbBuilder->build($this->routeMatch);

      // Allow modules to alter the breadcrumb.
      $context = ['builder' => $this->easyBreadcrumbBuilder];
      $this->moduleHandler->alter('system_breadcrumb', $breadcrumb, $this->routeMatch, $context);

      $links = $breadcrumb->getLinks();

      // Only fire if at least one link present.
      if (count($links) > 0) {

        // Open JSON.
        $value = '{
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          "itemListElement": [';

        $position = 1;
        /** @var \Drupal\Core\Link $link */
        foreach ($links as $link) {
          $name = $link->getText();
          $item = $link->getUrl()->setAbsolute(TRUE)->toString();

          // Escape " to produce valid json for titles with "" in them.
          $name = str_replace('"', '\"', $name);
          $item = str_replace('"', '\"', $item);

          // Add a comma before each item except the first.
          if ($position > 1) {
            $value .= ',';
          }

          // Only add item if link's not empty.
          if (!empty($item)) {
            $value .= '{
            "@type": "ListItem",
            "position": "' . $position . '",
            "name": "' . $name . '",
            "item": "' . $item . '"
          }';
          }
          else {
            $value .= '{
              "@type": "ListItem",
              "position": "' . $position . '",
              "name": "' . $name . '"
            }';
          }

          // Increment position for next run.
          $position++;

        }

        // Close JSON.
        $value .= ']}';
      }
    }

    return $value;
  }

}
