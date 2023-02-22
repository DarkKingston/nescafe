<?php

namespace Drupal\advanced_datalayer\Form;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerToken;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to add/edit Datalayer defaults entities.
 *
 * @package Drupal\Datalayer\Form
 */
class AdvancedDatalayerDefaultsForm extends EntityForm {

  /**
   * The datalayer tag manager.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface
   */
  protected $datalayerManager;

  /**
   * The datalayer tag manager.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerToken
   */
  protected $datalayerTokenService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The plugin.manager.advanced_datalayer.tag service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity_type.bundle.info service.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager
   */
  protected $datalayerTagPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AdvancedDatalayerManagerInterface $datalayer_manager,
    AdvancedDatalayerToken $datalayer_token_service,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    AdvancedDatalayerTagPluginManager $datalayer_tag_plugin_manager
  ) {
    $this->datalayerManager = $datalayer_manager;
    $this->datalayerTokenService = $datalayer_token_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->datalayerTagPluginManager = $datalayer_tag_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('advanced_datalayer.manager'),
      $container->get('advanced_datalayer.token'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.advanced_datalayer.tag'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $datalayer_defaults = $this->entity;

    $form['#ajax_wrapper_id'] = 'datalayer-defaults-form-ajax-wrapper';
    $ajax = [
      'wrapper' => $form['#ajax_wrapper_id'],
      'callback' => '::rebuildForm',
    ];
    $form['#prefix'] = '<div id="' . $form['#ajax_wrapper_id'] . '">';
    $form['#suffix'] = '</div>';

    $default_type = NULL;
    if ($datalayer_defaults !== NULL) {
      $default_type = $datalayer_defaults->getOriginalId();
    }
    else {
      $form_state->set('default_type', $default_type);
    }

    $token_types = empty($default_type) ? [] : [explode('__', $default_type)[0]];

    // Add the token browser at the top.
    $form += $this->datalayerTokenService->tokenBrowser($token_types);

    // If this is a new Datalayer defaults, then list available bundles.
    if ($datalayer_defaults->isNew()) {
      $options = $this->getAvailableBundles();
      $form['id'] = [
        '#type' => 'select',
        '#title' => $this->t('Type'),
        '#description' => $this->t('Select the type of default datalayer tags you would like to add.'),
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => $default_type,
        '#ajax' => $ajax + [
          'trigger_as' => [
            'name' => 'select_id_submit',
          ],
        ],
      ];
      $form['select_id_submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#name' => 'select_id_submit',
        '#ajax' => $ajax,
        '#attributes' => [
          'class' => ['js-hide'],
        ],
      ];
      $values = [];
    }
    else {
      $values = $datalayer_defaults->get('tags');
    }

    // Retrieve configuration settings.
    $settings = $this->config('advanced_datalayer.settings');
    $entity_type_tags = $settings->get('entity_type_tags');

    // Find the current entity type and bundle.
    $datalayer_defaults_id = $datalayer_defaults->id();
    $type_parts = explode('__', $datalayer_defaults_id);
    $entity_type = $type_parts[0];
    $entity_bundle = $type_parts[1] ?? NULL;

    // Chek if need display only specific tags for this entity type and bundle.
    $tags = !empty($entity_type_tags[$entity_type]) && !empty($entity_type_tags[$entity_type][$entity_bundle]) ? $entity_type_tags[$entity_type][$entity_bundle] : [];
    if ($this->entity->id() !== 'global') {
      $form = $this->datalayerManager->form($values, $form, TRUE, [$entity_type], $tags);
    }
    else {
      $form = $this->datalayerManager->form($values, $form);
    }

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => $datalayer_defaults->status(),
    ];
    if ($datalayer_defaults_id === 'global') {
      // Disabling global prevents any datalayer from working
      // warn users about this.
      $form['status']['#description'] = $this->t('Warning: disabling the Global default datalayer will prevent any datalayer from being used.');
    }

    return $form;
  }

  /**
   * Ajax form submit handler that will return the whole rebuilt form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function rebuildForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (isset($actions['delete'])) {
      $actions['delete']['#access'] = $actions['delete']['#access'] && !in_array($this->entity->id(), $this->datalayerManager::protectedDefaults(), TRUE);
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] === 'select_id_submit') {
      $form_state->set('default_type', $form_state->getValue('id'));
      $form_state->setRebuild();
    }
    else {
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $datalayer_defaults = $this->entity;

    $datalayer_defaults->setStatus($form_state->getValue('status'));

    // Set the label on new defaults.
    if ($datalayer_defaults->isNew()) {
      $datalayer_defaults_id = $form_state->getValue('id');

      $type_parts = explode('__', $datalayer_defaults_id);
      $entity_type = $type_parts[0];
      $entity_bundle = $type_parts[1] ?? NULL;

      // Get the entity label.
      $entity_info = $this->entityTypeManager->getDefinitions();
      $entity_label = (string) $entity_info[$entity_type]->get('label');

      if ($entity_bundle !== NULL) {
        // Get the bundle label.
        $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
        $entity_label .= ': ' . $bundle_info[$entity_bundle]['label'];
      }

      // Set the label to the config entity.
      $this->entity->set('label', $entity_label);
    }

    // Set tags within the Datalayer entity.
    $tags = $this->datalayerTagPluginManager->getDefinitions();
    $tag_values = [];
    foreach ($tags as $tag_id => $tag_definition) {
      if ($form_state->hasValue($tag_id)) {
        // Some plugins need to process form input before storing it. Hence, we
        // set it and then get it.
        $tag = $this->datalayerTagPluginManager->createInstance($tag_id);
        $tag->setValue($form_state->getValue($tag_id));
        if (!empty($tag->value()) || $tag->showEmpty()) {
          $tag_values[$tag_id] = $tag->value();
        }
      }
    }
    $datalayer_defaults->set('tags', $tag_values);
    $status = $datalayer_defaults->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Datalayer defaults.', [
          '%label' => $datalayer_defaults->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Datalayer defaults.', [
          '%label' => $datalayer_defaults->label(),
        ]));
    }

    $form_state->setRedirectUrl($datalayer_defaults->toUrl('collection'));
  }

  /**
   * Returns an array of available bundles to override.
   *
   * @return array
   *   A list of available bundles as $id => $label.
   */
  protected function getAvailableBundles() {
    $options = [];
    $entity_types = static::getSupportedEntityTypes();
    $datalayer_defaults_manager = $this->entityTypeManager->getStorage('advanced_datalayer_defaults');
    foreach ($entity_types as $entity_type => $entity_label) {
      if ($datalayer_defaults_manager->load($entity_type) === NULL) {
        $options[$entity_label][$entity_type] = "$entity_label (Default)";
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
      foreach ($bundles as $bundle_id => $bundle_metadata) {
        $datalayer_defaults_id = $entity_type . '__' . $bundle_id;

        if ($datalayer_defaults_manager->load($datalayer_defaults_id) === NULL) {
          $options[$entity_label][$datalayer_defaults_id] = $bundle_metadata['label'];
        }
      }
    }
    return $options;
  }

  /**
   * Returns a list of supported entity types.
   *
   * @return array
   *   A list of available entity types as $machine_name => $label.
   */
  public static function getSupportedEntityTypes() {
    $entity_types = [];

    // A list of entity types that are not supported.
    $unsupported_types = [
      'block_content',
      'comment',
      'contact_message',
      'menu_link_content',
      'shortcut',
      'redirect',
    ];

    // Make a list of supported content types.
    foreach (\Drupal::service('entity_type.manager')->getDefinitions() as $entity_name => $definition) {
      // Skip some entity types that we don't want to support.
      if (in_array($entity_name, $unsupported_types, TRUE)) {
        continue;
      }

      // Identify supported entities.
      if ($definition instanceof ContentEntityType) {
        // Only work with entity types that have canonical link.
        $links = $definition->get('links');
        if (!empty($links) && isset($links['canonical'])) {
          $entity_types[$entity_name] = static::getEntityTypeLabel($definition);
        }
      }
    }

    return $entity_types;
  }

  /**
   * Returns the text label for the entity type specified.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type to process.
   *
   * @return string
   *   A label.
   */
  public static function getEntityTypeLabel(EntityTypeInterface $entityType) {
    $label = $entityType->getLabel();

    if (is_a($label, 'Drupal\Core\StringTranslation\TranslatableMarkup')) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
      $label = $label->render();
    }

    return $label;
  }

}
