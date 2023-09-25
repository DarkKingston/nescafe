<?php

namespace Drupal\dsu_multitheme\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MultipleThemeNegotiatorSettingsForm.
 *
 * @package Drupal\dsu_multitheme\Form
 */
class MultipleThemeNegotiatorSettingsForm extends ConfigFormBase {

  /**
   * The configuration type.
   *
   * @var array
   */
  protected $themeMappings = [];
  protected $options = [];
  protected $addButtonName = 'New Map';
  protected $addButtonKey = 'addButton';
  protected $deleteButtonName = 'Delete Selected';
  protected $deleteButtonKey = 'deleteButton';
  protected $startOfPathKey = 'startOfPath';
  protected $themeNameKey = 'themeName';
  protected $themeMappingTableKey = 'themeMappingTable';
  protected $actionsKey = 'actions';
  protected $addNewMappingGroupKey = 'addNewMapping';
  protected $tableGroupKey = 'tableGroup';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_multitheme.settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dsu_multitheme.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $themeMappings = $this->config('dsu_multitheme.settings')->get('dsu_multitheme.themeMappings') ?? [];
    $header = [
      $this->startOfPathKey => $this->t('Start of path'),
      $this->themeNameKey => $this->t('Theme Name'),
    ];

    $themeMappingKeys = array_keys($themeMappings);

    $i = 1;
    foreach ($themeMappingKeys as $themeMappingKey) {
      $this->options[$i++] = [$this->startOfPathKey => $themeMappingKey, $this->themeNameKey => $themeMappings[$themeMappingKey]];

    }

    $form[$this->actionsKey] = [
      '#type' => 'actions',
    ];

    $form[$this->tableGroupKey] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Current Mappings'),
    ];

    $form[$this->tableGroupKey][$this->themeMappingTableKey] = [
      '#type' => 'tableselect',
      '#caption' => $this->t('Theme Mappings'),
      '#header' => $header,
      '#options' => $this->options,
      '#empty' => $this->t('No mappings found'),
    ];

    $form[$this->tableGroupKey][$this->deleteButtonKey] = [
      '#type' => 'submit',
      '#value' => $this->t($this->deleteButtonName),
      '#weight' => 10,
    ];

    $form[$this->addNewMappingGroupKey] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Add New Mapping'),
    ];

    $form[$this->addNewMappingGroupKey][$this->startOfPathKey] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start of URL Path'),

    ];

    $form[$this->addNewMappingGroupKey][$this->themeNameKey] = [
      '#type' => 'textfield',
      '#title' => $this->t('Theme to be mapped to URL Path'),
    ];

    // Add a submit button that handles the submission of the form.
    $form[$this->addNewMappingGroupKey][$this->addButtonKey] = [
      '#type' => 'submit',
      '#value' => $this->t($this->addButtonName),
      '#weight' => 10,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Trim the text values.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @var $config \Drupal\Core\Config\Config
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('dsu_multitheme.settings');
    $this->themeMappings = $config->get('dsu_multitheme.themeMappings');
    $startOfPath = $form_state->getValue('startOfPath');
    $themeName = $form_state->getValue('themeName');
    $userInput = $form_state->getUserInput();
    $operation = $userInput['op'];
    if ($operation === $this->addButtonName) {
      if (!$this->isNullOrEmptyString($startOfPath) &&  !$this->isNullOrEmptyString($themeName)) {
        $this->themeMappings[$startOfPath] = $themeName;
        $this->saveConfig($this->themeMappings);

      }
    }
    elseif ($operation === $this->deleteButtonName) {
      $results = array_filter($form_state->getValue($this->themeMappingTableKey));
      $themeMappingsLengthBeforeDelete = count($this->themeMappings);
      foreach ($results as $tableItems) {
        $selectedRow = $this->options[$tableItems];
        unset($this->themeMappings[$selectedRow[$this->startOfPathKey]]);

      }
      if (count($this->themeMappings) < $themeMappingsLengthBeforeDelete) {
        $this->saveConfig($this->themeMappings);
      }
    }
    else {
      // Perform default submit for all other calls to submit.
      parent::submitForm($form, $form_state);
    }
  }

  /**
   *
   */
  private function saveConfig($values) {
    $this->config('dsu_multitheme.settings')
      ->set('dsu_multitheme.themeMappings', $values)
      ->save();

  }

  /**
   *
   */
  private function isNullOrEmptyString($question) {
    return (!isset($question) || trim($question) === '');
  }

}
