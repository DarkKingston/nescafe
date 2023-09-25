<?php

namespace Drupal\ln_srh_menuiq\Plugin\Field\FieldFormatter;

use Drupal\backup_migrate\Core\Config\Config;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_menuiq\Form\MyMenuIQSettings;
use Drupal\ln_srh_menuiq\Services\SRHMyMenuIQHelper;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'SRH Quantity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_mymenuiq",
 *   label = @Translation("SRH My MenuIQ"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float",
 *   }
 * )
 */
class SRHMyMenuIQ extends FormatterBase{

  /**
   * @var EntityViewBuilderInterface
   */
  protected $paragraphViewBuilder;

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var SRHMyMenuIQHelper
   */
  protected $menuIqHelper;

  /**
   * @var CacheBackendInterface
   */
  protected $cache;


  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, SRHMyMenuIQHelper $menuIQHelper,  CacheBackendInterface $cache) {
    parent::__construct($plugin_id, $plugin_definition,$field_definition,$settings,$label,$view_mode,$third_party_settings);
    $this->paragraphViewBuilder = $entityTypeManager->getViewBuilder('paragraph');
    $this->configFactory = $configFactory;
    $this->menuIqHelper = $menuIQHelper;
    $this->cache = $cache;
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
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('ln_srh_menuiq.helper'),
      $container->get('cache.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $myMenuIQSettings = $this->configFactory->get(MyMenuIQSettings::SETTINGS);
    /** @var NodeInterface $recipe */
    $recipe = $items->getEntity();
    $elements = [];
    if ($this->recipeIsApplicable($recipe)) {
      $mmiqModulePath = \Drupal::service('module_handler')->getModule('ln_srh_menuiq')->getPath();
      foreach ($items as $delta => $item) {
        $recipeScore = $recipe->hasField(self::myMenuIqFieldName()) ? $recipe->get(self::myMenuIqFieldName())->getString() : 0;
        $currentRecipeSidedishesParagraph = Paragraph::create([
          'type' => SRHMyMenuIQConstants::SRH_PARAGRAPH_SIDEDISH_BUNDLE,
          SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD => $recipe,
          SRHMyMenuIQConstants::SRH_SIDEDISH_TITLE_FIELD => $recipe->label(),
          'field_srh_media' => $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_GALLERY_FIELD)->first(),
        ]);
        $sidedishesCurrentRecipe = [
          '#theme' => 'srh_mymenuiq_sidedish',
          '#sidedish' => [
            'entity' => $this->paragraphViewBuilder->view($currentRecipeSidedishesParagraph),
            'score_diff' => $recipeScore,
            'name' => $recipe->label(),
            'id' => 0,
            'is_current_recipe' => TRUE,
            //'is_selected' => TRUE,
          ],
        ];

        $elements[$delta] = [
          '#theme' => 'srh_mymenuiq',
          '#main_score' => $recipe->get(self::myMenuIqFieldName())->view([
            'label' => 'hidden',
            'settings' => [],
            'third_party_settings' => [],
            'type' => 'srh_progress_circle',
          ]),
          '#recipe' => $recipe,
          '#sidedishes_current_recipe' => $sidedishesCurrentRecipe,
          '#current_score' => $this->getScore($recipe->get(self::myMenuIqFieldName())->getString()),
          '#categories' => $this->buildCategories($recipe),
          '#panels' => [
            'balance' => $myMenuIQSettings->get('balance'),
            'balance_100' => $myMenuIQSettings->get('balance_100'),
            'teaser' => $myMenuIQSettings->get('teaser'),
            'expanded' => $myMenuIQSettings->get('expanded'),
            'info' => [
              'accordion' => [
                '#theme' => 'srh_mymenuiq_accordion_info',
                '#about' => [
                  'balance_info' => [
                    '#theme' => 'srh_mymenuiq_balance_info',
                    '#balance' => $myMenuIQSettings->get('balance'),
                  ],
                  'texts' => $myMenuIQSettings->get('info.about'),
                ],
                '#energy' => [
                  'energy_bars' => [
                    '#theme' => 'srh_mymenuiq_energy_bars',
                    '#bars' => $this->menuIqHelper->getRecipeEnergy($recipe),
                  ],
                  'texts' => $myMenuIQSettings->get('info.energy_info'),
                ]
              ],
              'nutritional_tips' => $this->getNutritionalTips($recipe),
            ]
          ],
          '#adimo' => $this->getAdimoWidget($recipe, $myMenuIQSettings->get('expanded')['summary']['button_buy_text'] ?? null)
        ];
      }
      $elements['#attached']['library'][] = 'ln_srh_menuiq/main';
      $elements['#attached']['drupalSettings']['mymenuiq']['defaultImg'] = $mmiqModulePath.'/images/sidedish-default.jpg';
      $elements['#attached']['drupalSettings']['mymenuiq']['balance'] = $myMenuIQSettings->get('balance');
      $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['name'] = $recipe->label();
      $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['id'] = $recipe->id();
      $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['course'] = $this->recipeGetMainCourseTag($recipe);
      $elements['#attached']['drupalSettings']['mymenuiq']['menu_sidedishes'] = $myMenuIQSettings->get('expanded.menu_sidedishes');
      $elements['#attached']['drupalSettings']['mymenuiq']['summary'] = $myMenuIQSettings->get('expanded.summary');
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()) {
        $srhId = $recipe->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->value;
        $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['srh_id'] = $srhId;
      }
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_TIMES_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_TIMES_FIELD)->isEmpty()) {
        $totalTime = $recipe->get(SRHConstants::SRH_RECIPE_TIMES_FIELD)->total + 0;
        $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['total_time'] = $totalTime;
      }
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_BRAND_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_BRAND_FIELD)->isEmpty()) {
        if ($brand = $recipe->get(SRHConstants::SRH_RECIPE_BRAND_FIELD)->referencedEntities()) {
          $brand = reset($brand);
          $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['brand'] = $brand->label();
        }
      }
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_CHEF_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_CHEF_FIELD)->isEmpty()) {
        $chef = $recipe->get(SRHConstants::SRH_RECIPE_CHEF_FIELD)->value;
        $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['chef'] = $chef;
      }
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_SERVING_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_SERVING_FIELD)->isEmpty()) {
        $serving = $recipe->get(SRHConstants::SRH_RECIPE_SERVING_FIELD)->getValue();
        $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['serving'] = $serving[0]['number'] . ' ' . $serving[0]['display_name'];
      }
      if ($recipe->hasField(SRHConstants::SRH_RECIPE_DIFFICULTY_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_DIFFICULTY_FIELD)->isEmpty()) {
        if($difficulty = $recipe->get(SRHConstants::SRH_RECIPE_DIFFICULTY_FIELD)->referencedEntities()) {
          $difficulty = reset($difficulty);
          $elements['#attached']['drupalSettings']['mymenuiq']['recipe']['difficulty'] = $difficulty->getName();
        }
      }
      // Expose ln_srh module version.
      $lnSrhModuleInfo = \Drupal::service('extension.list.module')
        ->getExtensionInfo('ln_srh');
      if ($lnSrhModuleInfo && isset($lnSrhModuleInfo['version'])) {
        $version = $lnSrhModuleInfo['version'];
        if (0 === strpos($version, '9.x-')) {
          // Remove 9.x- prefix.
          $version = substr($version, 4);
        }
        $elements['#attached']['drupalSettings']['mymenuiq']['version'] = $version;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getName() == self::myMenuIqFieldName();
    return parent::isApplicable($field_definition) && $isApplicable;
  }

  /**
   * @return string
   */
  public static function myMenuIqFieldName(){
    return 'field_srh_menuiqscore';
  }

  private function getScore($percent){
    $myMenuIQSettings = $this->configFactory->get(MyMenuIQSettings::SETTINGS);
    $balance = $myMenuIQSettings->get('balance');
    foreach ($balance as $key=>$item){
      if($percent >= $item['min'] && $percent <= $item['max']){
        return $key;
      }
    }
    return FALSE;
  }

  public function getNutritionalTips(NodeInterface $recipe){
    $myMenuIQSettings = $this->configFactory->get(MyMenuIQSettings::SETTINGS);
    $nutritionalTips = $myMenuIQSettings->get('info.nutritional_tips');
    $nutritionalTips['tips'] = [];
    if($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_NUTRITIONAL_TIPS_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_NUTRITIONAL_TIPS_FIELD)->isEmpty()){
      $tips = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_NUTRITIONAL_TIPS_FIELD)->referencedEntities();
      /** @var ParagraphInterface $tip */
      foreach ($tips as $tip){
        if($tip->hasField(SRHMyMenuIQConstants::SRH_TIP_TITLE_FIELD) && !$tip->get(SRHMyMenuIQConstants::SRH_TIP_TITLE_FIELD)->isEmpty()){
          $nutritionalTips['tips'][] = $tip->get(SRHMyMenuIQConstants::SRH_TIP_TITLE_FIELD)->first()->getValue();
        }
      }
    }
    if(empty($nutritionalTips['tips'])){
      $nutritionalTips = $myMenuIQSettings->get('info.nutritional_tips');
    }
    return $nutritionalTips;
  }

  /**
   * @param NodeInterface $recipe
   * @return bool
   */
  public function recipeGetMainCourseTag(NodeInterface $recipe){
    if($recipe->hasField(SRHConstants::SRH_RECIPE_TAGGING_FIELD) && !$recipe->get(SRHConstants::SRH_RECIPE_TAGGING_FIELD)->isEmpty()){
      $tags = $recipe->get(SRHConstants::SRH_RECIPE_TAGGING_FIELD)->referencedEntities();
      /** @var TermInterface $tag */
      foreach ($tags as $tag){
        if($tag->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() == SRHMyMenuIQConstants::SRH_MAIN_COURSE_TAG){
          return $tag->getName();
        }
      }
    }

    return FALSE;
  }

  /**
   * @param NodeInterface $recipe
   * @param ParagraphInterface $sideDish
   * @return array
   */
  public function buildSidedish(NodeInterface $recipe, ParagraphInterface $sideDish){
    $sideDishScore = $sideDish->hasField(SRHMyMenuIQConstants::SRH_SIDEDISHES_SCORE_FIELD) ? $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_SCORE_FIELD)->getString() : 0;
    $recipeScore = $recipe->hasField(self::myMenuIqFieldName()) ? $recipe->get(self::myMenuIqFieldName())->getString() : 0;
    // Build sidedish mealscore nutrients cache
    $this->menuIqHelper->getSideDishMealscoreNutrients($sideDish);
    $build = [
      '#theme' => 'srh_mymenuiq_sidedish',
      '#sidedish' => [
        'entity' => $this->paragraphViewBuilder->view($sideDish),
        'score_diff' => $sideDishScore - $recipeScore,
        'score' => $this->getScore($sideDishScore),
        'name' => $sideDish->hasField(SRHMyMenuIQConstants::SRH_SIDEDISH_TITLE_FIELD) ? $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_TITLE_FIELD)->getString() : 'none',
        'id' => $sideDish->id(),
        'type' => $this->menuIqHelper->getSideDishType($sideDish),
        'category' => $this->menuIqHelper->getSideDishCategory($sideDish),
      ],
    ];

    return $build;
  }

  /**
   * @param NodeInterface $recipe
   * @return array|null
   *   render array for adimo buy now
   */
  public function getAdimoWidget(NodeInterface $recipe, $label) {
    // Temporary disable adimo widget.
    return NULL;
    if (!\Drupal::service('module_handler')->moduleExists('ln_adimo')) {
      return NULL;
    }
    if (!$recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_ADIMO_FIELD)) {
      return NULL;
    }
    $adimoField = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_ADIMO_FIELD);
    $adimoFieldValue = [];
    if (!$adimoField->isEmpty()) {
      $adimoFieldValue = $adimoField->getValue()[0];
    }
    if (!$adimoFieldValue || empty($adimoFieldValue['touchpointID'])) {
      return NULL;
    }

    if (!$label) {
      $label = $this->t('Buy now');
    }
    $adimoFieldValue['customButtonHTML'] = ['#markup' => '<span>' . $label . '</span>'];
    $adimoField->setValue($adimoFieldValue);
    return $adimoField->view([
      'type' => 'integrationFormatter',
      'label' => 'hidden',
      'settings' => [],
    ]);
  }

  /**
   * @param NodeInterface $recipe
   * @return array
   */
  public function buildCategories(NodeInterface $recipe){
    $categories = [];
    if($this->hasSidedishes($recipe)){
      $sideDishes = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD)->referencedEntities();
      /** @var ParagraphInterface $sideDish */
      foreach ($sideDishes as $sideDish){
        if($sideDish->hasField(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD) && !$sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD)->isEmpty()){
          /** @var TermInterface $category */
          $category = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISHES_TYPE_FIELD)->entity;
          $categoryType = $this->menuIqHelper->getSideDishCategory($sideDish);
          $sideDishType = $this->menuIqHelper->getSideDishType($sideDish);
          $categories[$category->id()]['label'] = $category->label();
          $categories[$category->id()]['type'] = $categoryType;
          $categories[$category->id()]['sidedishes'][$sideDishType][$sideDish->id()] = $this->buildSidedish($recipe,$sideDish);
        }
      }
    }

    return $categories;
  }

  /**
   * @param NodeInterface $recipe
   *
   * @return bool
   */
  public function recipeIsApplicable(NodeInterface $recipe) {
    return $this->hasScore($recipe) && $this->hasSidedishes($recipe) && $this->recipeGetMainCourseTag($recipe);
  }

  /**
   * @param NodeInterface $recipe
   *
   * @return bool
   */
  public function hasScore(NodeInterface $recipe) {
    return $recipe->hasField(self::myMenuIqFieldName()) && !$recipe->get(self::myMenuIqFieldName())->isEmpty();
  }

  /**
   * @param NodeInterface $recipe
   *
   * @return bool
   */
  public function hasSidedishes(NodeInterface $recipe) {
    return $recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_SIDEDISHES_FIELD)->isEmpty();
  }
}
