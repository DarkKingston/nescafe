<?php

namespace Drupal\ln_seo_hreflang\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;;
use Drupal\ln_seo_hreflang\Entity\LnHreflangInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 *
 * @Action(
 *   id = "ln_hreflang_delete_action",
 *   label = @Translation("Delete hreflang"),
 *   type = "ln_hreflang",
 *   confirm_form_route_name = "entity.ln_hreflang.multiple_delete_confirm"
 * )
 */
class DeleteLnHreflang extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStore = $temp_store_factory->get('ln_hreflang_multiple_delete_confirm');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('tempstore.private'));
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->executeMultiple(array($entity));
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    // @todo Make translation-aware, similar to node.
    $entities_by_id = [];
    foreach ($entities as $entity) {
      $entities_by_id[$entity->id()] = $entity;
    }
    // Just save in temp store for now, delete after confirmation.
    $this->tempStore->set('delete', $entities_by_id);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIf($object instanceof LnHreflangInterface)->andIf(AccessResult::allowedIf($object->access('delete')));
    return $return_as_object ? $result : $result->isAllowed();
  }
}
