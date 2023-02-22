<?php

namespace Drupal\ln_campaign\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class LnCampaignListBuilderFilter extends FormBase{

  /**
   * @var EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * @var Request
   */
  protected $request;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeBundleInfoInterface $entityTypeBundleInfo, Request $request) {
    $this->bundleInfo = $entityTypeBundleInfo;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }


  public function getFormId() {
    return 'ln_campaign_list_builder_filter';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter submissions'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['filter']['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#title_display' => 'invisible',
      '#options' => $this->getBundleOptions(),
      '#default_value' => $this->request->get('bundle') ?? '',
    ];
    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#button_type' => 'primary',
    ];
    if ($this->request->getQueryString()) {
      $form['filter']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::resetForm'],
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = [];
    $bundle = $form_state->getValue('bundle') ?? '';
    if ($bundle) {
      $query['bundle'] = $bundle;
    }
    $form_state->setRedirect('entity.ln_campaign.collection', $query);
  }

  public function resetForm(array $form, FormStateInterface &$form_state) {
    $form_state->setRedirect('entity.ln_campaign.collection');
  }

  protected function getBundleOptions(){
    $bundles = $this->bundleInfo->getBundleInfo('ln_campaign');
    $options = ['' => $this->t('Select a Campaign Type')];
    foreach ($bundles as $id=>$bundle){
      $options[$id] = $bundle['label'] ?? $id;
    }

    return $options;
  }

}
