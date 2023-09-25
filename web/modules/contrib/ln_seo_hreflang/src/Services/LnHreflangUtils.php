<?php

namespace Drupal\ln_seo_hreflang\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class LnHreflangUtils.
 */
class LnHreflangUtils implements LnHreflangUtilsInterface {

  /**
   * The hreflang storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $hreflangStorage;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;



  /**
   * Constructs a LnHreflangUtils object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The request stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CurrentPathStack $current_path, RequestStack $request_stack, AliasManagerInterface $alias_manager) {
    $this->hreflangStorage = $entity_type_manager->getStorage('ln_hreflang');
    $this->currentPath = $current_path;
    $this->request = $request_stack->getCurrentRequest();
    $this->aliasManager = $alias_manager;
  }

  /**
   * @inheritdoc
   */
  public function getCurrentHreflangs(){
    //Get by drupal internal path
    $path = $this->currentPath->getPath();
    $hreflangs = $this->hreflangStorage->loadByProperties(['path' => $path]);
    //Get by drupal alias
    $hreflangs += $this->hreflangStorage->loadByProperties(['path' => $this->aliasManager->getAliasByPath($path)]);
    //Get by request uri
    $hreflangs += $this->hreflangStorage->loadByProperties(['path' => $this->request->getPathInfo()]);
    return $hreflangs;
  }
}
