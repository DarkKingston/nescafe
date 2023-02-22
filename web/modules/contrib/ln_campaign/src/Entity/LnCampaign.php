<?php

namespace Drupal\ln_campaign\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\ln_campaign\LnCampaignInterface;
use Drupal\user\UserInterface;

/**
 * Defines the campaign entity class.
 *
 * @ContentEntityType(
 *   id = "ln_campaign",
 *   label = @Translation("Campaign"),
 *   label_collection = @Translation("Campaigns"),
 *   bundle_label = @Translation("Campaign type"),
 *   handlers = {
 *     "view_builder" = "Drupal\ln_campaign\LnCampaignViewBuilder",
 *     "list_builder" = "Drupal\ln_campaign\LnCampaignListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\ln_campaign\LnCampaignAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ln_campaign\Form\LnCampaignForm",
 *       "edit" = "Drupal\ln_campaign\Form\LnCampaignForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "ln_campaign",
 *   data_table = "ln_campaign_field_data",
 *   revision_table = "ln_campaign_revision",
 *   revision_data_table = "ln_campaign_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer campaign types",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
 *     "bundle" = "bundle",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/ln-campaign/add/{ln_campaign_type}",
 *     "add-page" = "/admin/content/ln-campaign/add",
 *     "canonical" = "/ln_campaign/{ln_campaign}",
 *     "edit-form" = "/admin/content/ln-campaign/{ln_campaign}/edit",
 *     "delete-form" = "/admin/content/ln-campaign/{ln_campaign}/delete",
 *     "collection" = "/admin/content/ln-campaign"
 *   },
 *   bundle_entity_type = "ln_campaign_type",
 *   field_ui_base_route = "entity.ln_campaign_type.edit_form"
 * )
 */
class LnCampaign extends RevisionableContentEntityBase implements LnCampaignInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new campaign entity is created, set the uid entity reference to
   * the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the campaign entity.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDescription(t('A boolean indicating whether the campaign is enabled.'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Published')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Image'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'media')
      ->setSetting('handler', 'default:media')
      ->setSettings([
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
          'sort' => [
            'field' => '_none',
          ],
          'auto_create' => FALSE,
          'auto_create_bundle' => '',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'media_library_widget',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'media_thumbnail',
        'settings' => [
          'image_style' => 'wide', //TODO: generar una mas grande
        ]
      ])
      ->setDisplayConfigurable('view', TRUE);


    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the campaign.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['legal'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Legal'))
      ->setDescription(t('Legal bases of the campaign.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['instructions'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setLabel(t('Instructions'))
      ->setDescription(t('Campaign instructions.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the campaign author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the campaign was created.'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the campaign was last edited.'));
    $fields['active_date'] = BaseFieldDefinition::create('daterange')
      ->setLabel(t('Active date'))
      ->setDescription(t('Range of dates where the campaign is active'))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'daterange_default',
        'weight' => -4,
      ))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue([
        'default_date_type' => 'now',
        'default_date' => 'now',
        'default_end_date_type' => 'now',
        'default_end_date' => 'now',
      ]);

    return $fields;
  }

}
