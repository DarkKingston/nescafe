<?php

namespace Drupal\ln_srh_full\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ln_srh\Form\SRHConnectionSettings;
use Drupal\ln_srh\Services\SRHInterface;
use Drupal\ln_srh_full\SRHComplementsBatchConfiguration;
use Drupal\ln_srh_full\Batch\SRHComplementsBatchService;

/**
 * Defines a confirmation form to synchronize complements.
 */
class SRHSyncComplements extends ConfirmFormBase {

  /**
   * @var SRHInterface
   */
  protected $srhConnector;


  public function __construct(SRHInterface $srhConnector){
    $this->srhConnector = $srhConnector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('srh')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['complements'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SRH Complements IDs'),
      '#description' => $this->t('You must enter one or more complement ids separated by commas, for example 19245,23114, .... If left blank, all available complements will be synchronized'),
    ];
    $form['published'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only published complements'),
    ];
    $form['from_date'] = [
      '#type' => 'date',
      '#title' => $this->t('From date'),
      '#description' =>
        $this->t('If a value is provided, only complements that were updated after this date will by synchronized.') . "<br />" .
        $this->t('Be sure that complements updated before this date are already synchronized.'),
      // Don't allow to complete both "from_date" and "complements"
      '#states' => ['visible' => [[[":input[name='complements']" => ['filled' => FALSE]]]]],
    ];
    $form['page_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size'),
      '#description' => $this->t('Number of returned complements per page (Max: 100)'),
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
    $form['batch_complements'] = [
      '#type' => 'number',
      '#title' => $this->t('Complemwents for each batch'),
      '#description' => $this->t('Complements to synchronize in each batch'),
      '#min' => 1,
      '#default_value' => 5,
      '#required' => TRUE,
    ];

    if ($this->config(SRHConnectionSettings::SETTINGS)->get('enable_sync_complements')) {
      $form['clear_queue'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Remove synchronized complements from cron queue'),
        '#states' => ['visible' => [[[":input[name='complements']" => ['filled' => FALSE]]]]],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('complements',NULL);
    $srh_complements_ids = $value ? explode(',',$value) : NULL;
    $onlyPublished = $form_state->getValue('published',FALSE);
    $page_size = $form_state->getValue('page_size', 10);
    $batch_pages = $form_state->getValue('batch_pages',1);
    $batch_complements = $form_state->getValue('batch_complements',1);
    $localesSettings = $this->srhConnector->getConnectorSettings()->get('locales');
    $from = 0;
    if (!$srh_complements_ids && $form_state->getValue('from_date')) {
      $from = strtotime($form_state->getValue('from_date'));
    }
    $clearCronQueue = $srh_complements_ids ? FALSE : $form_state->getValue('clear_queue');
    $batch = [
      'title' => $this->t('Synchronizing complements...'),
    ];

    $batchConfig = SRHComplementsBatchConfiguration::getDefaultConfiguration()
      ->setComplementIds($srh_complements_ids)
      ->setBatchComplements($batch_complements)
      ->setOnlyPublished(boolval($onlyPublished))
      ->setFromDate($from)
      ->setPageSize($page_size)
      ->setBatchPages($batch_pages)
      ->setClearCronQueue(boolval($clearCronQueue));

    foreach ($localesSettings as $localesSetting) {
      $locale = $localesSetting['connect_markets'];
      $batch['operations'][] = [
        [SRHComplementsBatchService::class, 'buildBatchComplementsLocale'],
        [
          $batchConfig,
          $locale,
        ],
      ];
    }
    batch_set($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "srh_sync_complements_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return '';
  }

  public function getCancelUrl(){
    return Url::fromRoute('ln_srh_full.sync_complements');
  }

}
