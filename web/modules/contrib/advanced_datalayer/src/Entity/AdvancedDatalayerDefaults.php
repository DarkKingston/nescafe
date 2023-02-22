<?php

namespace Drupal\advanced_datalayer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerDefaultsInterface;

/**
 * Defines the datalayer defaults entity.
 *
 * @ConfigEntityType(
 *   id = "advanced_datalayer_defaults",
 *   label = @Translation("Advanced datalayer defaults"),
 *   handlers = {
 *     "list_builder" = "Drupal\advanced_datalayer\AdvancedDatalayerDefaultsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\advanced_datalayer\Form\AdvancedDatalayerDefaultsForm",
 *       "edit" = "Drupal\advanced_datalayer\Form\AdvancedDatalayerDefaultsForm",
 *       "delete" = "Drupal\advanced_datalayer\Form\AdvancedDatalayerDefaultsDeleteForm"
 *     }
 *   },
 *   config_prefix = "advanced_datalayer_defaults",
 *   admin_permission = "administer advanced datalayer defaults settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/search/advanced_datalayer/page-variables/{advanced_datalayer_defaults}/edit",
 *     "delete-form" = "/admin/config/search/advanced_datalayer/page-variables/{advanced_datalayer_defaults}/delete",
 *     "collection" = "/admin/config/search/advanced_datalayer"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "tags"
 *   }
 * )
 */
class AdvancedDatalayerDefaults extends ConfigEntityBase implements AdvancedDatalayerDefaultsInterface {

  /**
   * The datalayer defaults ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The datalayer defaults label.
   *
   * @var string
   */
  protected $label;

  /**
   * The default tag values.
   *
   * @var array
   */
  protected $tags = [];

  /**
   * Returns TRUE if a tag exists.
   *
   * @param string $tag_id
   *   The identifier of the tag.
   *
   * @return bool
   *   TRUE if the tag exists.
   */
  public function hasTag($tag_id) {
    return array_key_exists($tag_id, $this->tags);
  }

  /**
   * Returns the value of a tag.
   *
   * @param string $tag_id
   *   The identifier of the tag.
   *
   * @return array|null
   *   Array containing the tag values or NULL if not found.
   */
  public function getTag($tag_id) {
    if (!$this->hasTag($tag_id)) {
      return NULL;
    }
    return $this->tags[$tag_id];
  }

  /**
   * Overwrite the current tags with new values.
   *
   * @param array $new_tags
   *   New values for tags.
   */
  public function overwriteTags(array $new_tags = []) {
    if (!empty($new_tags)) {
      // Get the existing tags.
      $combined_tags = $this->get('tags');
      // Loop over the new tags, adding them to the existing tags.
      foreach ($new_tags as $tag_name => $data) {
        $combined_tags[$tag_name] = $data;
      }
      // Save the combination of the existing tags + the new tags.
      $this->set('tags', $combined_tags);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    // Put always Global in 1st place and front page later if available.
    if ($a->id() === 'global') {
      return -1;
    }
    elseif ($b->id() === 'global') {
      return 1;
    }
    elseif ($a->id() === 'front') {
      return -1;
    }
    elseif ($b->id() === 'front') {
      return 1;
    }

    // Use the default sort function.
    return parent::sort($a, $b);
  }

}
