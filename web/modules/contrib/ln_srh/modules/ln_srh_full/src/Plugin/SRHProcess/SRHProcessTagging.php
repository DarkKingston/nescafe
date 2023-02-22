<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessTerm;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh\SRHProcessManager;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessTag;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_tagging",
 *   field_name = "field_srh_tagging",
 *   label = @Translation("Tagging")
 * )
 */

class SRHProcessTagging extends SRHProcessTerm {

  /**
   * @var SRHProcessManager
   */
  protected $srhProcessManager;

  /**
   * SRHProcessTerm constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils, SRHProcessManager $srhProcessManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $srhUtils);
    $this->srhProcessManager = $srhProcessManager;
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
      $container->get('ln_srh.utils'),
      $container->get('plugin.manager.srh_process')
    );
  }

  public function getValues($srh_data, $langcode) {
    /** @var SRHProcessTag $srhProcessTag */
    $srhProcessTag = $this->srhProcessManager->createInstance('srh_process_tag', [
      'srh_source_field' => 'type',
      'vocabulary_id' => $this->getVocabularyId()
    ]);
    $values = [
      'vid' => $this->getVocabularyId(),
      'name' => $srh_data['tag']['name'] ?? $srh_data['tag']['id'],
      SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_data['tag']['id']
    ];
    /** @var TermInterface $term */
    $term = $this->termStorage->create($values);
    $values += [
      'parent' => $srhProcessTag->process($term, $srh_data, 'parent')
    ];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name) {
    $entities = [];
    $langcode = $srh_data['langcode'] ?? \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($srh_tagging = $this->getSRHEntityReferenceData($srh_data)) {
      foreach ($srh_tagging as $item) {
        if (isset($item['type'])) {
          $item['type']['id'] = 'tagging_type_' . $item['type']['id'];
        } else {
          $item['type']['id'] = 'tagging_type_' . $item['tagTypeId'];
          $item['type']['name'] = $item['tagTypeName'];
        }
        foreach ($item['tags'] as $srh_tag) {
          $srg_tag_data = [
            'tag' => $srh_tag,
            'type' => $item['type']
          ];
          $entities[] = $this->provideEntityRefernce($srg_tag_data, $langcode);
        }
      }
    }
    return $entities;
  }

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['tagging'] ?? FALSE;
  }

  /**
   * @return mixed|null
   */
  protected function getVocabularyId() {
    return 'srh_tagging';
  }

}
