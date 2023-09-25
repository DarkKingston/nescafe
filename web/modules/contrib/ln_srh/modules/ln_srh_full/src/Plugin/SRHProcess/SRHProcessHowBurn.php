<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_how_burn",
 *   field_name = "field_srh_how_burn",
 *   label = @Translation("How to Burn it")
 * )
 */

class SRHProcessHowBurn extends SRHProcessParagraph {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
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
      $container->get('entity_type.manager'),
      $container->get('ln_srh.utils')
    );
  }

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['howToBurnIt'] ?? [];
  }

  public function getValues($srh_data, $langcode) {
    return [
      'type' => 'srh_sport',
      'field_c_title' => $srh_data['time'] ?? '',
      'field_srh_sport' => $this->provideSportTerm($srh_data,$langcode),
    ];
  }

  /**
   * @param $srh_nutrient
   * @param $langcode
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\TermInterface|mixed|null
   */
  private function provideSportTerm($srh_data,$langcode){
    if(isset($srh_data['id']) && !empty($srh_data['id'])){
      $values = [
        'vid' => 'srh_sport',
        'name' => $srh_data['sportName'] ?? $srh_data['id'],
        SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_data['id'],
      ];
      return $this->srhUtils->provideTerm($values,$langcode);
    }
    return NULL;
  }

}
