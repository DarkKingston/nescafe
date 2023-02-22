<?php

namespace Drupal\ln_c_entity_compare\Entity\Bundle;

use Drupal\Component\Utility\Html;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;

/**
 * A bundle class for paragraph entities.
 */
class LnCEntityCompareBundle extends Paragraph implements LnCEntityCompareBundleInterface {

  CONST MAX_NUM_ENTITIES = 100;
  CONST SETTINGS_FIELD = 'field_c_settings';
  CONST DISPLAY_CONTEXT = 'view';

  /**
   * @inheritDoc
   */
  public function getEntitiesAvailableForComparison(): array {

    $entities = [];
    $settings = $this->getParagraphSettings();
    $entity_storage = \Drupal::entityTypeManager()
    ->getStorage($settings['entity_type']);

    // Setting 'available_entities' has priority if populated
    $ids = $settings['available_entities'] ?? [];

    if (empty($ids)) {

      // Setting 'available_entities' is empty, get all entities for the selected type/bundle

      // What is the bundle key of this entity type?
      $bundle_key = $entity_storage->getEntityType()->getKey('bundle');

      // Query all entities for this bundle
      $query = $entity_storage->getQuery();
      $query->condition($bundle_key, $settings['entity_bundle']);

      // Sort by label, if the property exists
      $label_key = $entity_storage->getEntityType()->getKey('label');
      if ($label_key) {
        $query->sort($label_key, 'ASC');
      }

      // Limit max number of results, just to make sure we don't end up with
      // a select list with thousands of options
      $query->range(0, self::MAX_NUM_ENTITIES);
      $ids = $query->execute();
    }

    $results = $entity_storage->loadMultiple($ids);
    foreach ($results as $id => $entity) {
      if ($entity) {
        $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity);
        $access = $entity->access('view', NULL, TRUE);
        if ($access->isAllowed()) {
          $entities[$id] = $entity;
        }
      }
    }
    return $entities;
  }

  /**
   * @inheritDoc
   */
  public function getParagraphSettings(): array {
    $settings = [];
    $item = $this->{self::SETTINGS_FIELD}->first();

    if (!empty($item)) {
      $settings = $item->getValue()['value'] ?? [];
    }

    return $settings;
  }

  /**
   * @inheritDoc
   */
  public function getFieldsToRender($entities): array {
    $entity = reset($entities);
    $settings = $this->getParagraphSettings();

    $config = \Drupal::config('ln_c_entity_compare.settings')
    ->get('entity_bundles_per_type');

    $view_mode = $config[$settings['entity_type']]['view_mode'];

    // Get fields from field definitions in view mode
    $field_manager = \Drupal::service('entity_field.manager');
    
    $entity_view_display = EntityViewDisplay::collectRenderDisplay($entity, $view_mode);
    $field_definitions = $this->getEntityFieldDefinitions($field_manager, $settings['entity_type'], $settings['entity_bundle']);
    $extra_fields = $this->getEntityExtraFields($entity_view_display, $settings['entity_type'], $settings['entity_bundle']);
    $fields = $this->getTargetFields($field_definitions, $entity_view_display);

    $fields = $extra_fields + $fields;

    return $fields;
  }

  /**
   * Collects the definitions of fields whose display is configurable.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   The array of field definitions
   */
  protected function getEntityFieldDefinitions($field_manager, $entity_type, $entity_bundle) {
    $context = self::DISPLAY_CONTEXT;
    return array_filter($field_manager->getFieldDefinitions($entity_type, $entity_bundle), function (FieldDefinitionInterface $field_definition) use ($context) {
      return $field_definition->isDisplayConfigurable($context);
    });
  }

  /**
   * Returns the extra fields of the entity type and bundle
   * 
   * This method add support for the Display Suite module fields
   *
   * @return array
   *   An array of extra field info.
   *
   * @see /modules/contrib/ds/ds.module::ds_entity_view_alter()
   */
  protected function getEntityExtraFields($display, $entity_type, $entity_bundle) {
    $extra_field_definitions = [];

    if (!\Drupal::moduleHandler()->moduleExists('ds')) {
      return $extra_field_definitions;
    }

    // If no layout is configured, stop executing.
    if (!$display->getThirdPartySetting('ds', 'layout')) {
      return $extra_field_definitions;
    }

    // If Display Suite is disabled, stop here.
    if (\Drupal\ds\Ds::isDisabled()) {
      return $extra_field_definitions;
    }

    // Get configuration.
    $configuration = $display->getThirdPartySettings('ds');

    // Don't fatal on missing layout plugins.
    $layout_id = isset($configuration['layout']['id']) ? $configuration['layout']['id'] : '';
    if (!\Drupal\ds\Ds::layoutExists($layout_id)) {
      return $extra_field_definitions;
    }

    // Add Display Suite fields.
    $fields = \Drupal\ds\Ds::getFields($entity_type);
    $field_values = !empty($configuration['fields']) ? $configuration['fields'] : [];

    foreach ($configuration['regions'] as $region) {
      foreach ($region as $weight => $key) {
        // Ignore if this field is not a DS field, just pull it in from the
        // entity.
        if (!isset($fields[$key])) {
          continue;
        }
  
        $field = $fields[$key];
        $extra_field_definitions[$key] = [
          'label' => $field['title'],
        ];
      }
    }
    return $extra_field_definitions;
  }

  /**
   * @inheritDoc
   */
  public function getRenderedFieldsFromEntities($entities, &$fields): array {

    $rendered_fields_by_entity = [];
    $settings = $this->getParagraphSettings();
    $config = \Drupal::config('ln_c_entity_compare.settings')
    ->get('entity_bundles_per_type');
    $view_mode = $config[$settings['entity_type']]['view_mode'];

    $current_column = 1;

    if (count($entities) == 1) {
      $current_column = NULL;
    }

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($settings['entity_type']);
    $entity_view_display = EntityViewDisplay::collectRenderDisplays($entities, $view_mode);

    // Render individual fields of each entity
    foreach ($entities as $id => $entity) {
      if ($entity) {
        $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity);

        // Build and render entity to make sure we collect 'extra fields',
        // that are typically rendered through specific, ad-hoc code in
        // EntityViewBuilderInterface::buildComponents() or in hook_entity_view()
        // implementations.
        $build = $view_builder->view($entity, $view_mode);
        \Drupal::service('renderer')->renderPlain($build);
        
        foreach (Element::children($build) as $field_name) {

          // Ignore build fields not explicitly set in our display
          if (empty($fields[$field_name])) {
            continue;
          }

          // We should respect label config for the field label in the label column
          if (isset($build[$field_name]['#label_display']) && $build[$field_name]['#label_display'] != 'hidden') {
            // 'label_display' was already set in $this->getTargetFields() but we need to set it again
            // to make sure fields gathered in getEntityExtraFields() also respect this setting
            $fields[$field_name]['label_display'] = 'above';
          }

          // Set the weight of the field so we can sort by weight later
          $fields[$field_name]['weight'] = $build[$field_name]['#weight'];

          // Label should always be hidden when field is rendered, regardless of
          // label position for this field
          $build[$field_name]['#label_display'] = 'hidden';

          $rendered_fields_by_entity[$id][$field_name] = $this->wrapFieldValue(
            $build[$field_name],
            $field_name,
            $current_column
          );
        }

        if ($current_column) {
          $current_column++;
        }
      }
    }

    // Sort fields by weight, this affects rendering order in $this->getRows(),
    // which is invoked separately in LnEntityCOmpareFormatter::viewElements
    uasort($fields, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    return $rendered_fields_by_entity;
  }

  /**
   * @inheritDoc
   */
  public function getRows($rendered_entities, $fields): array {
    // Flip entity fields into rows
    $rows = [];
    foreach ($fields as $field_name => $field_settings) {
      $row = [];
      $row[] = $field_settings['label_display'] && $field_settings['label_display'] != 'hidden' ? $field_settings['label'] : '';
      foreach ($rendered_entities as $rendered_entity_fields) {
        $row[] = $rendered_entity_fields[$field_name];
      }

      $rows[$field_name] = $row;
    }

    return $rows;
  }


  /**
   * @inheritDoc
   */
  public function wrapFieldValue($render_array, $field_name, $col = NULL): array {
    $field_attribute = 'class';
    $attr_id_parts = [
      $field_name,
      'p' . $this->id(),
    ];
    if ($col) {
      $attr_id_parts[] = 'c' . $col;
      $field_attribute = 'id';
    }
    $attr_id = implode('--', $attr_id_parts);

    $field_id = Html::cleanCssIdentifier($attr_id);

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [$field_attribute => $field_id],
      $render_array,
    ];
  }

  /**
   * @inheritDoc
   */
  public function getParagraphWrapperId(): string {
    $attr_id = "comparison-tables-p{$this->id()}";
    return Html::cleanCssIdentifier($attr_id);
  }

  /**
   * @inheritDoc
   */
  public function getEntityTemplateWrapperId($entity): string {
    $attr_id = "ln-c-entity-compare-p{$this->id()}-e{$entity->id()}";
    return Html::cleanCssIdentifier($attr_id);
  }

  /**
   * @inheritDoc
   */
  public function getCachedEntityTemplate($entities, $fields): array {
    $render_array = [];
    foreach ($entities as $entity) {
      $render_array[] = [
        '#type' => 'html_tag',
        '#tag' => 'template',
        '#attributes' => [
          'id' => $this->getEntityTemplateWrapperId($entity),
        ],
        $this->getRenderedFieldsFromEntities([$entity], $fields),
      ];
    }
    return $render_array;
  }

  /**
   * Internal function to gather target fields names, labels and display components
   *
   * @param array $field_definitions
   * @param object $entity_view_display
   * @return array
   */
  protected function getTargetFields($field_definitions, $entity_view_display) {
    $content_fields = $entity_view_display->get('content');

    $filtered_fields = [];

    foreach ($content_fields as $field_name => $field_settings) {

      if (isset($field_definitions[$field_name]) && isset($content_fields[$field_name])) {
        $field_label = '';

        if (is_a($field_definitions[$field_name], 'Drupal\field\Entity\FieldConfig')) {
          // Get label from FieldConfig object.
          $field_label = $field_definitions[$field_name]->label();
        }
        elseif (is_a($field_definitions[$field_name], 'Drupal\Core\Field\BaseFieldDefinition')) {
          // Get label from BaseFieldDefinition object.
          $field_label = $field_definitions[$field_name]->getLabel();
        }

        $filtered_fields[$field_name] = [
          'label' => $field_label,
          'label_display' => $field_settings['label'],
        ];
      }
    }

    return $filtered_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = [];
    $settings = $this->getParagraphSettings();

    if (empty($settings)) {
      // Settings not available yet (e.g. new entity being created)
      return parent::getCacheTags();
    }

    $entity_storage = \Drupal::entityTypeManager()
    ->getStorage($settings['entity_type']);
    $ids = $settings['available_entities'] ?? [];

    if (empty($ids)) {
      // All entities are available, add "list" cache tags
      $cache_tags = $entity_storage->getEntityType()->getListCacheTags();
    }
    else {
      // We have specific list of entities, add individual cache tags for each
      $results = $entity_storage->loadMultiple($ids);
      foreach ($results as $entity) {
        if ($entity) {
          $cache_tags = Cache::mergeTags($cache_tags, $entity->getCacheTags());
        }
      }
    }

    return Cache::mergeTags($cache_tags, parent::getCacheTags());
  }
}
