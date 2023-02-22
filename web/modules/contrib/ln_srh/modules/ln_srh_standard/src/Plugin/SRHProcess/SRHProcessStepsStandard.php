<?php

namespace Drupal\ln_srh_standard\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessStepsBasic;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_steps_standard",
 *   field_name = "field_srh_steps",
 *   label = @Translation("Steps Standard")
 * )
 */

class SRHProcessStepsStandard extends SRHProcessStepsBasic {

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * SRHProcessStepsStandard constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param SRHProcessManager $srhProcessManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHProcessManager $srhProcessManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhProcessManager = $srhProcessManager;
  }

  /**
   * @inerhitDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.srh_process')
    );
  }

  public function getValues($srh_data, $langcode) {
    $values = parent::getValues($srh_data, $langcode);
    /** @var ParagraphInterface $srh_step */
    $srh_step = $this->paragraphStorage->create($values);
    $srhProcessTipsPlugin = $this->srhProcessManager->createInstance('srh_process_tips_standard', []);
    $values += [
      'field_srh_tips' => $srhProcessTipsPlugin->process($srh_step,$srh_data,'field_srh_tips'),
    ];
    return $values;
  }

}
