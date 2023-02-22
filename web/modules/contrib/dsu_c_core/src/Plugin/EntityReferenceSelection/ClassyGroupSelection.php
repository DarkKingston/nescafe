<?php

namespace Drupal\dsu_c_core\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\dsu_c_core\Services\ClassyGroupUtilsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Entity Reference Selection plugin.
 *
 * @EntityReferenceSelection(
 *   id = "classy_group",
 *   label = @Translation("Classy group"),
 *   entity_types = {"classy_paragraphs_style"},
 *   group = "classy_group",
 *   weight = -10
 * )
 */
class ClassyGroupSelection extends DefaultSelection {

  /**
   * The classy group utils.
   *
   * @var \Drupal\dsu_c_core\Services\ClassyGroupUtilsInterface
   */
  protected $classyhGroupUtils;

  /**
   * Constructs a new DefaultSelection object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\dsu_c_core\Services\ClassyGroupUtilsInterface $paragraph_group_utils
   *   The classy group utils.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, EntityRepositoryInterface $entity_repository, ClassyGroupUtilsInterface $paragraph_group_utils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $entity_field_manager, $entity_type_bundle_info, $entity_repository);
    $this->classyGroupUtils = $paragraph_group_utils;
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
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('dsu_c_core.classy_group_utils')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['auto_create']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    // Used for autocomplete.
    if (isset($match)) {
      $query->condition('label', $match, $match_operator);
    }

    if(isset($this->configuration['entity']) && ($entity = $this->configuration['entity'])){
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $ids = $this->classyGroupUtils->getClassysForPagragraphBundle($entity->bundle(), TRUE);
      $query->condition('id', $ids, 'IN');
    }


    $query->addTag('classy_grops');

    return $query;
  }

}
