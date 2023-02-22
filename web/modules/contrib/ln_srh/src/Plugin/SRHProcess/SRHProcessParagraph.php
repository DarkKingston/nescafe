<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Each SRHProcess for paragraph will extend this base.
 */
abstract class SRHProcessParagraph extends SRHProcessEntityReference {

  /**
   * @var EntityStorageInterface
   */
  protected $paragraphStorage;

  protected $oldEntities = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->paragraphStorage = $entityTypeManager->getStorage('paragraph');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name) {
    if (!$this->isMultilanguage()) {
      $this->clearRecipeParagraphs($entity, $field_name);
    }
    else {
      $this->oldEntities = $entity->get($field_name)->referencedEntities();
    }
    $newEntities = parent::process($entity, $srh_data, $field_name);

    // Delete not needed old entities
    if ($this->isMultilanguage() && count($newEntities) < count($this->oldEntities)) {
      $toDelete = array_splice($this->oldEntities, count($newEntities));
      foreach ($toDelete as $entity) {
        $entity->delete();
      }
    }

    return $newEntities;
  }

  /**
   * {@inheritdoc}
   */
  protected function isMultiple() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    // This formatter is only available for paragraphs.
    return parent::isApplicable($field_definition,$plugin_definition) && $target_type == 'paragraph';
  }

  /**
   *  Clear paragraphs from recipe field
   */
  protected function clearRecipeParagraphs(ContentEntityInterface $entity, $field_name){
    if($referencedEntities = $entity->get($field_name)->referencedEntities()){
      foreach ($referencedEntities as $referencedEntity) {
        $referencedEntity->delete();
      }
    }
  }

  protected function setParagraphMultilanguage($values, $delta) {
    if(!$this->isMultilanguage()) {
      // For non multilanguage old entites are deleted.
      return $this->paragraphStorage->create($values);
    }

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $this->oldEntities[$delta] ?? NULL;
    $langCode =  $values['langCode'] ?? $this->entity->language()->getId();
    if(!$paragraph) {
      // Paragraph doesn't exist. Create it.
      $values['langCode'] = $langCode;
      return $this->paragraphStorage->create($values);
    }
    // Paragraph exists, recreate translation.
    if ($paragraph->hasTranslation($langCode)) {
      $paragraph = $paragraph->getTranslation($langCode);
      foreach ($values as $fieldName => $value) {
        if ($paragraph->hasField($fieldName)) {
          $paragraph->set($fieldName, $value);
        }
      }
    }
    else {
      $paragraph = $paragraph->addTranslation($langCode, $values);
    }

    return $paragraph;
  }

  /**
   * @param $srh_paragraph_data
   * @param string $langcode
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function provideEntityRefernce($srh_paragraph_data, $langcode, $delta = 0) {
    if ($values = $this->getValues($srh_paragraph_data, $langcode)) {
      $paragraph = $this->setParagraphMultilanguage($values, $delta);
      try {
        $paragraph->save();
      } catch (EntityStorageException $e) {
        \Drupal::logger('ln_srh')->error($e->getMessage());
        return NULL;
      }
      return $paragraph;
    }
    return NULL;
  }
}
