<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Each SRHProcess for taxonomy terms will extend this base.
 */
abstract class SRHProcessTerm extends SRHProcessEntityReference {

  /**
   * @var TermStorageInterface
   */
  protected $termStorage;

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;


  /**
   * SRHProcessTerm constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, SRHUtilsInterface $srhUtils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->srhUtils = $srhUtils;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return SRHProcessBase|SRHProcessTerm|static
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
   * Returns the volcabulary id from taxonomy term
   */
  abstract protected function getVocabularyId();

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    // This formatter is only available for taxonomy terms.
    return parent::isApplicable($field_definition,$plugin_definition) && $target_type == 'taxonomy_term';
  }

  protected function getVocabularyOptions (){
    $vocabularies = Vocabulary::loadMultiple();
    $options = [];
    foreach ($vocabularies as $vocabulary){
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    return $options;
  }

  /**
   * @param $srh_entity_refernce_data
   * @return \Drupal\Core\Entity\EntityInterface|TermInterface|false|mixed
   */
  public function provideEntityRefernce($srh_term_data, $langcode, $delta = 0) {
    return $this->srhUtils->provideTerm($this->getValues($srh_term_data, $langcode),$langcode);
  }

}
