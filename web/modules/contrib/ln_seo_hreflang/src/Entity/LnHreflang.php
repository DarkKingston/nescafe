<?php

namespace Drupal\ln_seo_hreflang\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;

/**
 * Defines the hreflang entity class.
 *
 * @ContentEntityType(
 *   id = "ln_hreflang",
 *   label = @Translation("Hreflang"),
 *   label_collection = @Translation("Hreflangs"),
 *   handlers = {
 *     "list_builder" = "Drupal\ln_seo_hreflang\LnHreflangListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\ln_seo_hreflang\Form\LnHreflangForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "ln_hreflang",
 *   admin_permission = "administer hreflang",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "path",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/config/lightnest/ln-seo-hreflang/add",
 *     "edit-form" = "/admin/config/lightnest/ln-seo-hreflang/{ln_hreflang}/edit",
 *     "delete-form" = "/admin/config/lightnest/ln-seo-hreflang/{ln_hreflang}/delete",
 *     "collection" = "/admin/config/lightnest/ln-seo-hreflang"
 *   }
 * )
 */
class LnHreflang extends ContentEntityBase implements LnHreflangInterface {


  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->get('path')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return Url::fromUserInput($this->getPath());
  }

  /**
   * {@inheritdoc}
   */
  public function getLink() {
    return Link::fromTextAndUrl($this->getPath(), $this->getUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->set('path', $path);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLinks() {
    return $this->get('links');
  }

  /**
   * {@inheritdoc}
   */
  public function setLinks($links) {
    $this->set('links', $links);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['path'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('System path'))
      ->setRequired(TRUE)
      ->addConstraint('UniqueField', [])
      ->addPropertyConstraints('value', [
        'Regex' => [
          'pattern' => '/^\//i',
          'message' => new TranslatableMarkup('The source path has to start with a slash.'),
        ],
      ])
      ->addPropertyConstraints('value', ['ValidCleanPath' => []])
      ->setDisplayOptions('form',  [
        'type' => 'string_textfield',
        'weight' => 1,
        'settings' => [
          'size' => 45,
        ]
      ]);

    $fields['links'] = BaseFieldDefinition::create('ln_hreflang')
      ->setLabel(t('Links'))
      ->setRequired(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'ln_hreflang_widget',
        'weight' => 2,
      ]);;

    return $fields;
  }

}
