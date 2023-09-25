<?php

namespace Drupal\ln_srh_full\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh_full\Services\SRHComplementUtilsInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a confirmation form to synchronize compliment.
 */
class SRHSyncComplementConfirm extends ConfirmFormBase {

  /**
   * @var SRHComplementUtilsInterface
   */
  protected $srhUtils;

  public function __construct(SRHComplementUtilsInterface $srhUtils){
    $this->srhUtils = $srhUtils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ln_srh_full.complement_utils')
    );
  }


  /**
   * @var NodeInterface
   */
  protected $complement;

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $this->complement = $node;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->srhUtils->reSyncComplement($this->complement);
    $form_state->setRedirectUrl($this->complement->toUrl('edit-form'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "srh_sync_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to sync @title now?', ['@title' => $this->complement->label()]);
  }

  public function getCancelUrl(){
    return $this->complement->toUrl('edit-form');
  }

}
