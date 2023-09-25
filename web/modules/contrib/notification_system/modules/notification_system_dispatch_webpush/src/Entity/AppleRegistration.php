<?php

namespace Drupal\notification_system_dispatch_webpush\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Apple registration entity.
 *
 * @ingroup notification_system_dispatch_webpush
 *
 * @ContentEntityType(
 *   id = "apple_registration",
 *   label = @Translation("Apple registration"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\notification_system_dispatch_webpush\AppleRegistrationListBuilder",
 *     "views_data" = "Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationViewsData",
 *   },
 *   base_table = "apple_registration",
 *   translatable = FALSE,
 *   admin_permission = "administer apple registration entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "device_token",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "collection" = "/admin/config/services/apple-registration",
 *   }
 * )
 */
class AppleRegistration extends ContentEntityBase implements AppleRegistrationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getUser() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setUser(UserInterface $user) {
    $this->set('uid', $user);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeviceToken() {
    return $this->get('device_token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDeviceToken($token) {
    $this->set('device_token', $token);
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['device_token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Device Token'))
      ->setDescription(t('The device_token of the Apple registration entity.'))
      ->setSettings([
        'max_length' => 512,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSettings([
        'target_type' => 'user',
      ])
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
