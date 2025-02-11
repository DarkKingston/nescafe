<?php

namespace Drupal\ln_seo_hreflang\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the path admin overview filter form.
 *
 * @internal
 */
class PathFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_seo_hreflang_path_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $keys = NULL) {
    $form['#attributes'] = ['class' => ['search-form']];
    $form['basic'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter hreflang'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['basic']['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Path'),
      '#title_display' => 'invisible',
      '#default_value' => $keys,
      '#maxlength' => 128,
      '#size' => 25,
    ];
    $form['basic']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    if ($keys) {
      $form['basic']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::resetForm'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.ln_hreflang.collection', [], [
      'query' => ['search' => trim($form_state->getValue('filter'))],
    ]);
  }

  /**
   * Resets the filter selections.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.ln_hreflang.collection');
  }

}
