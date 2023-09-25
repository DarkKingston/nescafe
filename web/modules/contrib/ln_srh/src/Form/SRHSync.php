<?php

namespace Drupal\ln_srh\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\Services\SRHUtilsInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a confirmation form to syncronice recipes.
 */
class SRHSync extends ConfirmFormBase {

  /**
   * @var SRHUtilsInterface
   */
  protected $srhUtils;

  public function __construct(SRHUtilsInterface $srhUtils){
    $this->srhUtils = $srhUtils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ln_srh.utils')
    );
  }


  /**
   * @var NodeInterface
   */
  protected $recipe;

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $this->recipe = $node;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->srhUtils->reSyncRecipe($this->recipe);
    $form_state->setRedirectUrl($this->recipe->toUrl('edit-form'));
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
    return $this->t('Do you want to sync @title now?', ['@title' => $this->recipe->label()]);
  }

  public function getCancelUrl(){
    return $this->recipe->toUrl('edit-form');
  }

}
