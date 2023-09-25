<?php

namespace Drupal\dsu_c_core\Plugin\Field\FieldWidget;

use Drupal\classy_paragraphs\Entity\ClassyParagraphsStyle;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\dsu_c_core\CCoreConstants;
use Drupal\dsu_c_core\Services\CCoreUtilsInterface;
use Drupal\dsu_c_core\Services\ClassyGroupUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'classy_group' widget.
 *
 * @FieldWidget(
 *   id = "classy_group",
 *   label = @Translation("Classy group"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class ClassyGroupWidget extends OptionsButtonsWidget {
  protected const NONE_VALUE = '_none';

  /**
   * The ccore utils.
   *
   * @var \Drupal\dsu_c_core\Services\CCoreUtilsInterface
   */
  protected $cCoreUtils;

  /**
   * The classy group utils.
   *
   * @var \Drupal\dsu_c_core\Services\ClassyGroupUtils
   */
  protected $classyGroupUtils;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\dsu_c_core\Services\CCoreUtilsInterface $c_core_utils
   *   The ccore utils.
   * @param \Drupal\dsu_c_core\Services\ClassyGroupUtils $classy_group_utils
   *   The classy group utils.
   */
  public function __construct(
      $plugin_id,
      $plugin_definition,
      FieldDefinitionInterface $field_definition,
      array $settings,
      array $third_party_settings,
      CCoreUtilsInterface $c_core_utils,
      ClassyGroupUtils $classy_group_utils) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->cCoreUtils = $c_core_utils;
    $this->classyGroupUtils = $classy_group_utils;
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
      $configuration['third_party_settings'],
      $container->get('dsu_c_core.utils'),
      $container->get('dsu_c_core.classy_group_utils')
    );
  }



  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    //For valid _none option
    $this->required = FALSE;
    $this->multiple = FALSE;
    $selected = $this->getSelectedOptions($items);

    $form_groups = [];
    $entity = $items->getEntity();
    $bundle = $entity->bundle();
    foreach ($this->classyGroupUtils->getClassyGroupsForPagragraphBundle($bundle) as $group){
      /** @var  \Drupal\dsu_c_core\Entity\ClassyGroupInterface $group */
      $group_bundle = $bundle;
      if(in_array(CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES, $group->getBundles())){
        $group_bundle = CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES;
      }
      if(!isset($form_groups[$group_bundle])){
        $form_groups[$group_bundle] = [
          '#type' => 'fieldset',
          '#title' => $group_bundle == CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES ? $this->t('General styles') : $this->t('Paragraph styles'),
        ];
      }
      $selected_values = array_intersect($group->getClassys(), $selected);
      $options = $this->cCoreUtils->entitiesToOptions(ClassyParagraphsStyle::loadMultiple($group->getClassys()));
      if($group->isMultiple()){
        $form_groups[$group_bundle][$group->id()] = [
          '#title' => $group->label(),
          '#type' => 'checkboxes',
          '#options' => $options,
          '#default_value' =>  $selected_values,
          '#attributes' => [
            'class' => [
              'container-inline',
            ],
          ],
      ];
      }else {
        $form_groups[$group_bundle][$group->id()] = [
          '#title' => $group->label(),
          '#type' => 'select',
          '#options' => [self::NONE_VALUE => $this->getEmptyLabel()] + $options,
          '#default_value' => reset($selected_values) ?: self::NONE_VALUE,
        ];
      }
    }

    if(!empty($form_groups)){
      $element += [
        '#type' => 'details',
        '#title' => $this->t('Classy'),
        '#open' => TRUE,
        $form_groups
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    if(!$form_state->getTemporaryValue("classy_group_processed_{$entity->bundle()}_{$entity->id()}")){
      $field_name = $this->fieldDefinition->getName();

      // Extract the values from $form_state->getValues().
      $path = array_merge($form['#parents'], [$field_name]);
      $values = NestedArray::getValue($form_state->getValues(), $path);
      $all_values = [];
      foreach($values as $value_groups){
        foreach ($value_groups as $value_group){
          foreach ($value_group as $value){
            if(is_array($value)){ //Is multiple chechbox
              $all_values = [...$all_values, ...Checkboxes::getCheckedCheckboxes($value)];
            }else if($value != self::NONE_VALUE){
              $all_values = [...$all_values, ...[$value]];
            }
          }
        }
      }

      $values = [];
      foreach ($all_values as $value){
        $values[] = ['target_id' => $value];
      }

      $form_state->setValue($path, $values);
      $form_state->setTemporaryValue("classy_group_processed_{$entity->bundle()}_{$entity->id()}", TRUE);
    }

    parent::extractFormValues($items, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if(isset($field_definition->getSettings()['handler']) && $field_definition->getSettings()['handler'] == 'classy_group'){
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if (!$this->required && !$this->multiple) {
      return $this->t('Default');
    }
  }

}
