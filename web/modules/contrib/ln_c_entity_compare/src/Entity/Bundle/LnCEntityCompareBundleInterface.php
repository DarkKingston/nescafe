<?php

namespace Drupal\ln_c_entity_compare\Entity\Bundle;

interface LnCEntityCompareBundleInterface {

  /**
   * Get all entities available for comparison.
   * 
   * Returns entities selected in field_available_entities setting. If empty,
   * returns all entities available for the selected entity type and bundle
   *
   * @return array
   */
  public function getEntitiesAvailableForComparison(): array;

  /**
   * Get paragraph settings
   *
   * @return array
   */
  public function getParagraphSettings(): array;

  /**
   * Returns render array of field value wrap with custom wrapper
   * html_tag and field identifier
   *
   * @return array
   */
  public function wrapFieldValue($render_array, $field_name, $col): array;

  /**
   * Generates an identifier string to wrap comparison tables in each paragraph
   *
   * @return string
   */
  public function getParagraphWrapperId(): string;

  /**
   * Generates an identifier string to wrap an entity template
   *
   * @return string
   */
  public function getEntityTemplateWrapperId($entity): string;

  /**
   * Create render array with cached field values for a given entity
   *
   * @param object $entity
   * @param array $fields
   * @return array
   */
  public function getCachedEntityTemplate($entity, $fields): array;

  /**
   * Flip entity field values into rows array
   *
   * @param array $rendered_entities
   * @param array $fields
   * @return array
   */
  public function getRows($rendered_entities, $fields): array;

  /**
   * Generate render array of fields for an array of entities
   *
   * @param array $entities
   * @param array $fields
   * @return array
   */
  public function getRenderedFieldsFromEntities($entities, &$fields): array;

  /**
   * Get list of fields needed to be rendered for the current paragraph settings
   * @param array $entities
   * @return array
   */
  public function getFieldsToRender($entities): array;
  
}
