<?php

namespace Drupal\google_optimize_js;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Default implementation.
 */
class Inclusion implements InclusionInterface {

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The admin context service.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The current path stack service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Inclusion constructor.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The module configuration.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The admin context service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The current path stack service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The alias manager service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The patch matcher service.
   */
  public function __construct(ImmutableConfig $config, AdminContext $admin_context, CurrentPathStack $current_path_stack, AliasManagerInterface $alias_manager, PathMatcherInterface $path_matcher) {
    $this->config = $config;
    $this->adminContext = $admin_context;
    $this->currentPathStack = $current_path_stack;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritDoc}
   */
  public function includeOptimizeSnippet() {

    // Load on every page by default.
    $include_snippet = TRUE;

    // If there's no GTM container, don't do anything.
    if (empty($this->config->get('container'))) {
      $include_snippet = FALSE;
    }

    // Do not include the snippet if disabled.
    if (!$this->config->get('enabled')) {
      $include_snippet = FALSE;
    }

    // Never load on admin pages.
    if ($this->adminContext->isAdminRoute()) {
      $include_snippet = FALSE;
    }

    // If any include paths are set, see if the current path is enabled.
    if ($pages = $this->config->get('pages')) {
      $include_snippet = $this->pathMatcher->matchPath(
        $this->aliasManager->getAliasByPath($this->currentPathStack->getPath()),
        $pages
      );
    }

    return $include_snippet;
  }

  /**
   * {@inheritDoc}
   */
  public function includeAntiFlickerSnippet() {

    // Exclude the anti-flicker snippet by default.
    $include_snippet = FALSE;

    $anti_flicker_pages = $this->config->get('anti_flicker_pages');

    // If any paths are defined and optimize is loaded, check the current path.
    if (!empty($anti_flicker_pages) && $this->includeOptimizeSnippet()) {
      $include_snippet = $this->pathMatcher->matchPath(
        $this->aliasManager->getAliasByPath($this->currentPathStack->getPath()),
        $anti_flicker_pages
      );
    }

    return $include_snippet;
  }

}
