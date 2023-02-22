<?php

namespace Drupal\advanced_datalayer;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Advance datalayer manager interface.
 */
interface AdvancedDatalayerManagerInterface {

  /**
   * Extracts all tags of a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to extract datalayer tags from.
   *
   * @return array
   *   Array of datalayer tags.
   */
  public function tagsFromEntity(ContentEntityInterface $entity);

  /**
   * Extracts all tags of a given entity.
   *
   * And combines them with sitewide, per-entity-type, and per-bundle defaults.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to extract datalayer tags from.
   *
   * @return array
   *   Array of datalayer tags.
   */
  public function tagsFromEntityWithDefaults(ContentEntityInterface $entity);

  /**
   * Extracts all appropriate default tags for an entity.
   *
   * From sitewide, per-entity-type, and per-bundle defaults.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity for which to calculate defaults.
   *
   * @return array
   *   Array of datalayer tags.
   */
  public function defaultTagsFromEntity(ContentEntityInterface $entity);

  /**
   * Returns an array of group plugin information sorted by weight.
   *
   * @return array
   *   Array of groups, sorted by weight.
   */
  public function sortedGroups();

  /**
   * Returns an array of tag plugin information sorted by group then weight.
   *
   * @return array
   *   Array of datalayer tags, sorted by weight.
   */
  public function sortedTags();

  /**
   * Returns a weighted array of groups containing their weighted tags.
   *
   * @return array
   *   Array of sorted datalayer tags, in groups.
   */
  public function sortedGroupsWithTags();

  /**
   * Builds the form element for a advanced_datalayer field.
   *
   * If a list of either groups or tags are passed in, those will be used to
   * limit the groups/tags on the form. If nothing is passed in, all groups
   * and tags will be used.
   *
   * @param array $values
   *   Existing values.
   * @param array $element
   *   Existing element.
   * @param bool $remove_global
   *   Remove global tags.
   * @param array $token_types
   *   Array of available group plugins.
   * @param array $included_tags
   *   Array of available tag plugins.
   *
   * @return array
   *   Render array for advanced_datalayer form.
   */
  public function form(array $values, array $element, bool $remove_global = FALSE, array $token_types = [], array $included_tags = []);

}
