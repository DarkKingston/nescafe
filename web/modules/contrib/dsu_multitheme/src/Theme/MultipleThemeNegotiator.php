<?php

namespace Drupal\dsu_multitheme\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MultipleThemeNegotiator.
 *
 * @package Drupal\dsu_multitheme\Theme
 */
class MultipleThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * Created object for theme packages.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return bool
   * version 1.3
   */
  protected $configFactory;
  protected $pathAliasManager;
  protected  $adminContext;
  protected  $currentPathStack;
  const  SETTINGS_KEY = 'dsu_multitheme.settings';
  const THEME_MAPPING_KEY = 'dsu_multitheme.themeMappings';

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $path_alias_manager, AdminContext $admin_context, CurrentPathStack $currentPathStack) {

    $this->configFactory = $config_factory;
    $this->pathAliasManager = $path_alias_manager;
    $this->adminContext = $admin_context;
    $this->currentPathStack = $currentPathStack;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory'),
          $container->get('path_alias.manager'),
          $container->get('router.admin_context'),
          $container->get('path.current')

      );
  }

  /**
   *
   */
  public function applies(RouteMatchInterface $route_match) {
    $isAdmin = $this->adminContext->isAdminRoute($route_match->getRouteObject());

    return !$isAdmin &&
        $this->negotiateRoute($route_match) ? TRUE : FALSE;
  }

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return null|string
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {

    return $this->negotiateRoute($route_match) ?: NULL;

  }

  /**
   * Function that does all of the work in selecting a theme.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *
   * @return bool|string
   */
  private function negotiateRoute(RouteMatchInterface $route_match) {
    $config = $this->configFactory->get(MultipleThemeNegotiator::SETTINGS_KEY);
    $themeMapping = $config->get(MultipleThemeNegotiator::THEME_MAPPING_KEY);

    if(empty($themeMapping)){
      return;
    }

    // Get the current node id of the request.
    $node = $route_match->getParameter('node');

    $pathAlias = NULL;
    $themeName = FALSE;
    if (!empty($node)) {
      $nodeId = $node->id();
      if ($nodeId !== NULL) {
        // What is the path alias of the node. If there is no alias a node the node path will be returned.
        $pathAlias = $this->pathAliasManager->getAliasByPath('/node/' . $nodeId);
        $themeName = $this->findTheme($pathAlias, $themeMapping);

      }
    }
    else {
      $pathAlias = $this->currentPathStack->getPath();

      if (FALSE !== strpos($pathAlias, "/views/ajax")) {
        /** TODO: Add Dependency injection */
        $request = \Drupal::requestStack()->getCurrentRequest();
        $ajaxPageState = $request->request->get("ajax_page_state");
        $themeName = $ajaxPageState['theme'];
        if (!isset($themeName)) {
          $themeName = FALSE;
        }
      }
      else {
        $themeName = $this->findTheme($pathAlias, $themeMapping);
      }
    }

    return $themeName;

  }

  /**
   *
   */
  private  function findTheme($pathAlias, $themeMapping) {

    // Get the all the url start strings that we have custom mappings for.
    $themeMappingKeys = array_keys($themeMapping);
    $cleanedPathAlias = ltrim($pathAlias, "/");

    foreach ($themeMappingKeys as $themeMappingKey) {
      // Check if the alias or node path starts with the url start string.
      $cleanedThemeMappingKey = ltrim($themeMappingKey, "/");

      if (strpos($cleanedPathAlias, $cleanedThemeMappingKey, 0) !== FALSE) {
        return $themeMapping[$themeMappingKey];
      }
    }
    // Return beyond by default.
    return FALSE;
  }

}
