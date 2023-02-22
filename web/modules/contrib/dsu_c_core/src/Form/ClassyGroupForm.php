<?php

namespace Drupal\dsu_c_core\Form;

use Drupal\classy_paragraphs\Entity\ClassyParagraphsStyle;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\dsu_c_core\CCoreConstants;
use Drupal\dsu_c_core\Services\CCoreUtils;
use Drupal\dsu_c_core\Services\CCoreUtilsInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ClassyGroupForm.
 *
 * @package Drupal\classy_paragraphs\Form
 */
class ClassyGroupForm extends EntityForm {

  /**
   * The ccore utils service.
   *
   * @var \Drupal\dsu_c_core\Services\CCoreUtilsInterface
   */
  protected $ccore_utils;

  /**
   * Constructs a new ClassyGroupForm object.
   *
   * @param \Drupal\dsu_c_core\Services\CCoreUtilsInterface $ccore_utils
   *   The ccore utils service.
   */
  public function __construct(CCoreUtilsInterface $ccore_utils) {
    $this->ccore_utils = $ccore_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dsu_c_core.utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\dsu_c_core\Entity\ClassyGroup $group */
    $group = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $group->label(),
      '#description' => $this->t("Label for the Classy group."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $group->id(),
      '#machine_name' => [
        'exists' => '\Drupal\dsu_c_core\Entity\ClassyGroup::load',
      ],
      '#disabled' => !$group->isNew(),
    ];

    $form['multiple'] = [
      '#title' => $this->t('Multiple'),
      '#type' => 'checkbox',
      '#default_value' => $group->isMultiple(),
      '#description' => $this->t('Check it if a paragraph can have more than one class of this group at the same time'),
    ];

    $options = [CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES => $this->t('All bundles')];
    $options += $this->ccore_utils->entitiesToOptions(ParagraphsType::loadMultiple());
    $form['bundles'] = [
      '#title' => $this->t('Bundles'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $group->getBundles(),
      '#required' => TRUE,
    ];
    foreach ($options as $key => $label){
      if($key != CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES){
        $form['bundles'][$key]['#states'] = [
          'disabled' => [
            ':input[name="bundles[all]"]' => [
              'checked' => true
            ],
          ]
        ];
      }
    }

    $options = $this->ccore_utils->entitiesToOptions(ClassyParagraphsStyle::loadMultiple());
    $form['classys'] = [
      '#title' => $this->t('Classys'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $group->getClassys(),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var  \Drupal\dsu_c_core\Entity\ClassyGroupInterface $group */
    $group = $this->entity;
    $group->setBundles(Checkboxes::getCheckedCheckboxes($group->getBundles()));
    $group->setClassys(Checkboxes::getCheckedCheckboxes($group->getClassys()));
    $status = $group->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label group.', [
          '%label' => $group->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label group.', [
          '%label' => $group->label(),
        ]));
    }
    $form_state->setRedirectUrl($group->toUrl('collection'));
  }

}
