<?php

namespace Drupal\ln_seo_hreflang\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;

/**
 * Plugin implementation of the 'link' field type.
 *
 * @FieldType(
 *   id = "ln_hreflang",
 *   label = @Translation("Lightnest hreflang"),
 *   description = @Translation("Stores a URL string, optional varchar link text, and optional blob of attributes to assemble a link."),
 *   default_widget = "link_default",
 *   default_formatter = "link",
 *   constraints = {"LinkType" = {}, "LinkAccess" = {}, "LinkExternalProtocols" = {}, "LinkNotExistingInternal" = {}}
 * )
 */
class LnHreflang extends FieldItemBase  {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'link_type' => LinkItemInterface::LINK_EXTERNAL,
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['uri'] = DataDefinition::create('uri')
      ->setLabel(t('Uri'));

    $properties['lang'] = DataDefinition::create('string')
      ->setLabel(t('Link text'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'uri' => [
          'description' => 'The href of the hreflang.',
          'type' => 'varchar',
          'length' => 2048,
        ],
        'lang' => [
          'description' => 'The language of the hreflang.',
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'uri' => [['uri', 30]],
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();

    // Set of possible top-level domains.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    // Set random length for the lang.
    $lang_length = mt_rand(2, 4);

    $values['uri'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    $values['lang'] = $random->word($lang_length);

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $uri = $this->get('uri')->getValue();
    $lang = $this->get('lang')->getValue();
    return $uri === NULL || $uri === '' || $lang === NULL || $lang === '';
  }

  /**
   * {@inheritdoc}
   */
  public function isExternal() {
    return $this->getUrl()->isExternal();
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'uri';
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return Url::fromUri($this->uri);
  }

  /**
   * {@inheritdoc}
   */
  public function getLang() {
    return $this->lang;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the main property, if no array is
    // given.
    if (isset($values) && !is_array($values)) {
      $values = [static::mainPropertyName() => $values];
    }
    parent::setValue($values, $notify);
  }

}
