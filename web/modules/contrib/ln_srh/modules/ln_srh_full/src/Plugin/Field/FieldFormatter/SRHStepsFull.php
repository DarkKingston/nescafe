<?php

namespace Drupal\ln_srh_full\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\ln_srh_basic\SRHBasicConstants;
use Drupal\ln_srh_full\SRHFullConstants;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_steps_full",
 *   label = @Translation("SRH Steps Full"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class SRHStepsFull extends EntityReferenceRevisionsEntityFormatter{

  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * SRHStepsFull constructor.
   * @param $plugin_id
   * @param $plugin_definition
   * @param FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param $label
   * @param $view_mode
   * @param array $third_party_settings
   * @param LoggerChannelFactoryInterface $logger_factory
   * @param EntityDisplayRepositoryInterface $entity_display_repository
   * @param EntityFieldManagerInterface $entityFieldManager
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $entity_display_repository);
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_display.repository'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'ignore_groups' => FALSE,
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['ignore_groups'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ignore Groups'),
      '#default_value' => $this->getSetting('ignore_groups'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Ignore Groups: @ignore_group', ['@ignore_group' => $this->getSetting('ignore_groups')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    if(!$this->getSetting('ignore_groups')){
      $recipe = $items->getEntity();
      if($recipe->hasField(SRHFullConstants::SRH_STEPS_GROUPS_FIELD) && !$recipe->get(SRHFullConstants::SRH_STEPS_GROUPS_FIELD)->isEmpty()){
        $groups = $recipe->get(SRHFullConstants::SRH_STEPS_GROUPS_FIELD)->referencedEntities();
        $recipeGroupedIds = [];
        /** @var ParagraphInterface $group */
        foreach ($groups as $group){
          if($group->hasField(SRHFullConstants::SRH_STEPS_GROUP_IDS_FIELD) && !$group->get(SRHFullConstants::SRH_STEPS_GROUP_IDS_FIELD)->isEmpty()){
            $recipeGroupedIds += array_column($group->get(SRHFullConstants::SRH_STEPS_GROUP_IDS_FIELD)->getValue(),'value','value');
          }
        }
        foreach ($elements as $key=>$element){
          /** @var ParagraphInterface $step */
          if($step = $element['#paragraph'] ?? FALSE){
            if($step->hasField(SRHFullConstants::SRH_RECIPE_STEP_ID_FIELD) && !$step->get(SRHFullConstants::SRH_RECIPE_STEP_ID_FIELD)->isEmpty()){
              $recipeStepId = $step->get(SRHFullConstants::SRH_RECIPE_STEP_ID_FIELD)->getString();
              if(in_array($recipeStepId,$recipeGroupedIds)){
                unset($elements[$key]);
              }
            }
          }
        }
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getTargetEntityTypeId() == 'node' && $field_definition->getTargetBundle() == 'srh_recipe' && $field_definition->getName() == SRHBasicConstants::SRH_RECIPE_STEPS_FIELD;
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
