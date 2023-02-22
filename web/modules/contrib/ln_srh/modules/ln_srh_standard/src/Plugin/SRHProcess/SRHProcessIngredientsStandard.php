<?php

namespace Drupal\ln_srh_standard\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessIngredientsBasic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_ingredients_standard",
 *   field_name = "field_srh_ingredients",
 *   label = @Translation("Ingredients Standard")
 * )
 */

class SRHProcessIngredientsStandard extends SRHProcessIngredientsBasic {

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;


  /**
   * SRHProcessIngredients constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param SRHUtilsInterface $srhUtils
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils,SRHProcessManager $srhProcessManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager,$srhUtils);
    $this->srhProcessManager = $srhProcessManager;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return \Drupal\ln_srh\Plugin\SRHProcess\SRHProcessBase|SRHProcessParagraph|SRHProcessIngredientsBasic|static
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('ln_srh.utils'),
      $container->get('plugin.manager.srh_process')
    );
  }

  public function getValues($srh_data, $langcode) {
    $values = parent::getValues($srh_data, $langcode);

    if (!empty($srh_data['tips'])) {
      /** @var SRHProcessTipsStandard $processTipsStandardPlugin */
      $srhProcessPlugin = $this->srhProcessManager->createInstance('srh_process_tips_standard',[]);
      $ingredient = $this->paragraphStorage->create($values);
      $values['field_srh_tips'] = $srhProcessPlugin->process($ingredient,$srh_data,'field_srh_tips');
    }
    return $values;
  }

}
