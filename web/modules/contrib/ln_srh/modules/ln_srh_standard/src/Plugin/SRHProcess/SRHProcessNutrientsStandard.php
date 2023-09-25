<?php

namespace Drupal\ln_srh_standard\Plugin\SRHProcess;

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
 *   id = "srh_process_nutrients_standard",
 *   field_name = "field_srh_nutrients",
 *   label = @Translation("Nutrients standard")
 * )
 */

class SRHProcessNutrientsStandard extends SRHProcessParagraph {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * @var TermStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhUtils = $srhUtils;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
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
    return $srh_data['nutrients'] ?? [];
  }

  public function getValues($srh_data, $langcode) {
    $quantity = isset($srh_data['quantity']) ? $srh_data['quantity'] : NULL;
    return [
      'type' => 'srh_nutrient',
      'field_srh_percentage' => $srh_data['percentage'] ?? '',
      'field_srh_nutrient' => $this->provideNutrientTerm($srh_data,$langcode),
      'field_srh_nutrient_quantity' => $quantity,
    ];
  }

  /**
   * @param $srh_nutrient
   * @param $langcode
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\TermInterface|mixed|null
   */
  private function provideNutrientTerm($srh_nutrient,$langcode){
    if(isset($srh_nutrient['id']) && !empty($srh_nutrient['id'])){
      $values = [
        'vid' => 'srh_nutrient',
        'name' => $srh_nutrient['name'] ?? $srh_nutrient['id'],
        SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_nutrient['id'],
      ];
      return $this->srhUtils->provideTerm($values,$langcode);
    }
    return NULL;
  }

}
