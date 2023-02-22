<?php

namespace Drupal\ln_srh_full\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh_basic\SRHBasicConstants;
use Drupal\ln_srh_full\SRHFullConstants;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'SRH Quantity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_ingredient_group",
 *   label = @Translation("SRH Ingredient Group"),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class SRHIngredientGroup extends FormatterBase{

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'view_mode' => 'default',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['view_mode'] = array(
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions('paragraph'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $view_modes = $this->entityDisplayRepository->getViewModeOptions('paragraph');
    $view_mode = $this->getSetting('view_mode');
    $summary[] = t('Rendered as @mode', ['@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode]);

    return $summary;
  }

  /**
   * @inerhitDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    $view_builder = $this->entityTypeManager->getViewBuilder('paragraph');
    $elements = [];
    $ingredients = $this->getEntitiesToView($items);
    /** @var ParagraphInterface $ingredient */
    foreach ($ingredients as $ingredient){
      $elements[] = $view_builder->view($ingredient, $view_mode, $ingredient->language()->getId());
    }
    return $elements;
  }

  private function getEntitiesToView(FieldItemListInterface $items){
    $goupIngredients = [];
    $idsGroup = array_column($items->getValue(),'value','value');
    /** @var ParagraphInterface $ingredientGroup */
    $ingredientGroup = $items->getEntity();
    /** @var NodeInterface $recipe */
    $recipe = $ingredientGroup->getParentEntity();
    if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD)->isEmpty()){
      $ingredients = $recipe->get(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD)->referencedEntities();
      foreach ($ingredients as $ingredient){
        if($ingredient->hasField(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD) && !$ingredient->get(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD)->isEmpty()){
          $recipeIngredientId = $ingredient->get(SRHFullConstants::SRH_RECIPE_INGREDIENT_ID_FIELD)->getString();
          if(in_array($recipeIngredientId,$idsGroup)){
            $goupIngredients[$recipeIngredientId] = $ingredient;
          }
        }
      }
    }

    return $goupIngredients;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getTargetEntityTypeId() == 'paragraph' && $field_definition->getTargetBundle() == SRHFullConstants::SRH_PARAGRAPH_INGREDIENT_GROUP_TYPE && $field_definition->getName() == SRHFullConstants::SRH_INGREDIENTS_GROUP_IDS_FIELD;
    return parent::isApplicable($field_definition) && $isApplicable;
  }
}
