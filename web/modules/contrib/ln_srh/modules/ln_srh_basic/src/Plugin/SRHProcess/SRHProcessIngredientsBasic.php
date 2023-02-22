<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_ingredients_basic",
 *   field_name = "field_srh_ingredients",
 *   label = @Translation("Ingredients Basic")
 * )
 */

class SRHProcessIngredientsBasic extends SRHProcessParagraph {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * @var TermStorageInterface
   */
  protected $termStorage;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,$entityTypeManager);
    $this->srhUtils = $srhUtils;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
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
      $container->get('ln_srh.utils')
    );
  }

  /**
   * @param $srh_data
   * @return array|mixed
   */
  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['ingredients'] ?? [];
  }

  function getValues($srh_data, $langcode) {
    return [
      'type' => 'srh_ingredient',
      'field_c_title' => $srh_data['fullName'] ?? $srh_data['name'] ?? '',
      'field_srh_quantity' => [
        'quantity' => floatval($srh_data['quantityTotal'] ?? $srh_data['quantity'] ?? 0),
        'display' => floatval($srh_data['quantityDisplay'] ?? 0),
        'grams' => floatval($srh_data['quantityGrams'] ?? 0),
        'fraction' => $srh_data['quantityFraction']['description'] ?? '',
      ],
      'field_srh_preparation_hint' => $srh_data['preparationHint'] ?? '',
      'field_srh_is_nestle_product' => $srh_data['isNestleProduct'] ?? FALSE,
      'field_srh_ingredient' => $this->provideIngredientTerm($srh_data,$langcode),
      'field_srh_unit_type' => $this->provideUnitTypeTerm($srh_data,$langcode),
    ];
  }

  /**
   * @param $srh_ingredient
   * @param $langcode
   *
   * @return \Drupal\Core\Entity\EntityInterface|TermInterface|mixed|null
   */
  protected function provideIngredientTerm($srh_ingredient, $langcode) {
    if (isset($srh_ingredient['id']) && !empty($srh_ingredient['id'])) {
      $values = [
        'vid' => 'srh_ingredient',
        'name' => $srh_ingredient['name'] ?? $srh_ingredient['id'],
        'field_srh_id' => $srh_ingredient['idIngredient'] ?? $srh_ingredient['id'],
      ];
      return $this->srhUtils->provideTerm($values, $langcode);
    }
    return NULL;
  }

  /**
   * @param $srh_ingredient
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   */
  protected function provideUnitTypeTerm($srh_ingredient,$langcode){
    if(isset($srh_ingredient['unitType']['id']) && !empty($srh_ingredient['unitType']['id'])){
      $values = [
        'vid' => 'srh_unit_type',
        'name' => $srh_ingredient['unitType']['singularName'] ?? $srh_ingredient['unitType']['localizedName'] ?? $srh_ingredient['unitType']['id'],
        'description' => $srh_ingredient['unitType']['description'] ?? '',
        'field_srh_id' => $srh_ingredient['unitType']['id'],
        'field_srh_plural_name' => $srh_ingredient['unitType']['pluralName'] ?? '',
        'field_srh_abbreviation' => $srh_ingredient['unitType']['singularAbbreviation'] ?? '',
        'field_srh_plural_abbreviation' => $srh_ingredient['unitType']['pluralAbbreviation'] ?? '',
      ];
      return $this->srhUtils->provideTerm($values,$langcode);
    }
    return NULL;
  }


}
