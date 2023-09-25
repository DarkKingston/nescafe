<?php

namespace Drupal\dsu_c_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Classy group entity.
 *
 * @ConfigEntityType(
 *   id = "classy_group",
 *   label = @Translation("Classy group"),
 *   handlers = {
 *     "list_builder" = "Drupal\dsu_c_core\ClassyParagraphsGroupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dsu_c_core\Form\ClassyGroupForm",
 *       "edit" = "Drupal\dsu_c_core\Form\ClassyGroupForm",
 *       "delete" = "Drupal\dsu_c_core\Form\ClassyGroupDeleteForm"
 *     }
 *   },
 *   config_prefix = "classy_group",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "multiple",
 *     "bundles",
 *     "classys"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/classy_paragraphs_style/group/{classy_group}",
 *     "edit-form" = "/admin/structure/classy_paragraphs_style/group/{classy_group}/edit",
 *     "delete-form" = "/admin/structure/classy_paragraphs_style/group/{classy_group}/delete",
 *     "collection" = "/admin/structure/classy_paragraphs_style/group"
 *   }
 * )
 */
class ClassyGroup extends ConfigEntityBase implements ClassyGroupInterface {

  /**
   * The Classy group ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Classy group label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Classy group multiple.
   *
   * @var boolean
   */
  protected $multiple = FALSE;


  /**
   * An array of paragraphs bundles
   *
   * @var array
   */
  protected $bundles = [];

  /**
   * An array of classys
   *
   * @var array
   */
  protected $classys = [];

  /**
   * {@inheritdoc}
   */
  public function isMultiple(){
    return (bool) $this->multiple;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple($multiple){
    $this->multiple = $multiple;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundles(){
    return $this->bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function setBundles($bundles){
    $this->bundles = $bundles;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getClassys(){
    return $this->classys;
  }

  /**
   * {@inheritdoc}
   */
  public function setClassys($classys){
    $this->classys = $classys;
    return $this;
  }

}
