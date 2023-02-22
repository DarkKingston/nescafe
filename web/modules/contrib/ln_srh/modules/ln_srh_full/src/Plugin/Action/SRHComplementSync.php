<?php

namespace Drupal\ln_srh_full\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_full\Services\SRHComplementUtilsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resynchronize selected complements.
 *
 * @Action(
 *   id = "srh_complement_sync",
 *   label = @Translation("Resynchronize selected complements"),
 *   type = "node"
 * )
 */
class SRHComplementSync extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The SRH utils.
   *
   * @var SRHComplementUtilsInterface
   */
  protected $srhUtils;

  /**
   * Constructs an SRHComplementSync object.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param SRHComplementUtilsInterface $srhUtils
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SRHComplementUtilsInterface $srhUtils) {
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
      $container->get('ln_srh_full.complement_utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->srhUtils->reSyncComplement($entity);
  }

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIf($object->bundle() == SRHConstants::SRH_COMPLEMENT_BUNDLE);
    $result = $return_as_object ? $result : $result->isAllowed();
    return $result && $object->access('update', $account, $return_as_object);
  }

}
