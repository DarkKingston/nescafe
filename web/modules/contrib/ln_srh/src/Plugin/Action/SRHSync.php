<?php

namespace Drupal\ln_srh\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resynchronize selected recipes.
 *
 * @Action(
 *   id = "srh_recipe_sync",
 *   label = @Translation("Resynchronize selected recipes"),
 *   type = "node"
 * )
 */
class SRHSync extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The SRH utils.
   *
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * Constructs an SRHSync object.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param SRHUtilsInterface $srhUtils
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->srhUtils = $srhUtils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ln_srh.utils')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->srhUtils->reSyncRecipe($entity);
  }

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIf($object->bundle() == SRHConstants::SRH_RECIPE_BUNDLE);
    $result = $return_as_object ? $result : $result->isAllowed();
    return $result && $object->access('update', $account, $return_as_object);
  }

}
