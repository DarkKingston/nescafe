<?php

namespace Drupal\ln_srh_full\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_basic\SRHBasicConstants;
use Drupal\ln_srh_full\SRHFullConstants;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_ingredients_full",
 *   label = @Translation("SRH Ingredients Full"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class SRHIngredientsFull extends EntityReferenceRevisionsEntityFormatter{

  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * SRHIngredientsFull constructor.
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
        'show_change_display' => FALSE,
        'show_portion_spinner' => FALSE,
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
    $elements['show_change_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show change display'),
      '#default_value' => $this->getSetting('show_change_display'),
    ];
    $elements['show_portion_spinner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show portion spinner'),
      '#default_value' => $this->getSetting('show_portion_spinner'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Ignore Groups: @value', ['@value' => $this->getSetting('ignore_groups') ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Show change display: @value', ['@value' => $this->getSetting('show_change_display') ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Show portion spinner: @value', ['@value' => $this->getSetting('show_portion_spinner') ? $this->t('Yes') : $this->t('No')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    if (!$this->getSetting('ignore_groups')) {
      $recipe = $items->getEntity();
      if($recipe->hasField(SRHFullConstants::SRH_INGREDIENTS_GROUPS_FIELD) && !$recipe->get(SRHFullConstants::SRH_INGREDIENTS_GROUPS_FIELD)->isEmpty()){
        $groups = $recipe->get(SRHFullConstants::SRH_INGREDIENTS_GROUPS_FIELD)->referencedEntities();
        $recipeGroupedIds = [];
        /** @var ParagraphInterface $group */
        foreach ($groups as $group){
          if ($group->hasField(SRHFullConstants::SRH_INGREDIENTS_GROUP_IDS_FIELD) && !$group->get(SRHFullConstants::SRH_INGREDIENTS_GROUP_IDS_FIELD)->isEmpty()) {
            $recipeGroupedIds += array_column($group->get(SRHFullConstants::SRH_INGREDIENTS_GROUP_IDS_FIELD)->getValue(),'value','value');
          }
        }
        foreach ($elements as $key => $element) {
          /** @var ParagraphInterface $ingredient */
          if ($ingredient = $element['#paragraph'] ?? FALSE) {
            if ($ingredient->hasField(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD) && !$ingredient->get(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD)->isEmpty()) {
              $recipeIngredientId = $ingredient->get(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD)->getString();
              if (in_array($recipeIngredientId, $recipeGroupedIds)) {
                unset($elements[$key]);
              }
            }
          }
        }
      }
    }

    if (!empty($elements)) {
      $serving = NULL;
      if ($show_spinner = $this->getSetting('show_portion_spinner')) {
        $node = $items->getEntity();
        if ($node->hasField(SRHFullConstants::SRH_RECIPE_SERVING_FIELD) && !$node->get(SRHFullConstants::SRH_RECIPE_SERVING_FIELD)->isEmpty()) {
          $serving = $node->get(SRHFullConstants::SRH_RECIPE_SERVING_FIELD)->number;
        }
        else {
          $show_spinner = FALSE;
        }
      }
      return [
        '#theme' => 'srh_ingredients_full',
        '#elements' => $elements,
        '#show_change_display' => $this->getSetting('show_change_display'),
        '#show_portion_spinner' => $show_spinner,
        '#serving' => $serving
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable =
      $field_definition->getTargetEntityTypeId() == 'node' &&
      in_array($field_definition->getTargetBundle(), [
        SRHConstants::SRH_RECIPE_BUNDLE,
        SRHConstants::SRH_COMPLEMENT_BUNDLE
      ]) &&
      $field_definition->getName() == SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD;
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
