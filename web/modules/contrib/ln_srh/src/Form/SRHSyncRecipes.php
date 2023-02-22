<?php

namespace Drupal\ln_srh\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a confirmation form to syncronice recipes.
 */
class SRHSyncRecipes extends ConfirmFormBase {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  /**
   * @var SRHInterface
   */
  protected $srhConnector;


  public function __construct(SRHUtilsInterface $srhUtils, SRHInterface $srhConnector){
    $this->srhUtils = $srhUtils;
    $this->srhConnector = $srhConnector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ln_srh.utils'),
      $container->get('srh')
    );
  }


  /**
   * @var NodeInterface
   */
  protected $recipe;

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['recipes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SRH Recipes ids'),
      '#description' => $this->t('You must enter one or more recipe ids separated by commas, for example 19245,23114, .... If left blank, all available recipes will be synchronized'),
    ];
    $form['published'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only published recipes'),
    ];
    $form['from_date'] = [
      '#type' => 'date',
      '#title' => $this->t('From date'),
      '#description' =>
        $this->t('If a value is provided, only recipes that were updated after this date will by synchronized.') . "<br />" .
        $this->t('Be sure that recipes updated before this date are already synchronized.'),
      // Don't allow to complete both "from_date" and "recipes"
      '#states' => ['visible' => [[[":input[name='recipes']" => ['filled' => FALSE]]]]],
    ];
    $form['page_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size'),
      '#description' => $this->t('Number of returned recipes per page (Max: 100)'),
      '#min' => 1,
      '#max' => 100,
      '#default_value' => 100,
      '#required' => TRUE,
    ];
    $form['batch_pages'] = [
      '#type' => 'number',
      '#title' => $this->t('Pages for each batch'),
      '#description' => $this->t('Pages to synchronize in each batch'),
      '#min' => 1,
      '#default_value' => 10,
      '#required' => TRUE,
    ];
    $form['batch_recipes'] = [
      '#type' => 'number',
      '#title' => $this->t('Recipes for each batch'),
      '#description' => $this->t('Recipes to synchronize in each batch'),
      '#min' => 1,
      '#default_value' => 5,
      '#required' => TRUE,
    ];

    if ($this->config(SRHConnectionSettings::SETTINGS)->get('enable_sync')) {
      $form['clear_queue'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Remove synchronized recipes from cron queue'),
        '#states' => ['visible' => [[[":input[name='recipes']" => ['filled' => FALSE]]]]],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('recipes',NULL);
    $srh_recipes_ids = $value ? explode(',',$value) : NULL;
    $onlyPublished = $form_state->getValue('published',FALSE);
    $page_size = $form_state->getValue('page_size', 10);
    $batch_pages = $form_state->getValue('batch_pages',1);
    $batch_recipes = $form_state->getValue('batch_recipes',1);
    $localesSettings = $this->srhConnector->getConnectorSettings()->get('locales');
    $from = 0;
    if (!$srh_recipes_ids && $form_state->getValue('from_date')) {
      $from = strtotime($form_state->getValue('from_date'));
    }
    $clearCronQueue = $srh_recipes_ids ? FALSE : $form_state->getValue('clear_queue');
    $batch = [
      'title' => $this->t('Synchronizing recipes...'),
    ];
    foreach ($localesSettings as $localesSetting){
      $locale = $localesSetting['connect_markets'];
      $batch['operations'][] = [
        '\Drupal\ln_srh\Services\SRHBatchServices::buildBatchRecipesLocale',
        [
          $locale,
          $srh_recipes_ids,
          $onlyPublished,
          $page_size,
          $batch_pages,
          $batch_recipes,
          $from,
          $clearCronQueue
        ],
      ];
    }
    batch_set($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "srh_sync_recipes_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return '';
  }

  public function getCancelUrl(){
    return Url::fromRoute('ln_srh.sync_recipes');
  }

}
