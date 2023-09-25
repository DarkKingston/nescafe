<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;


use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Each SRHProcess for entity references will extend this base.
 */
abstract class SRHProcessEntityReference extends SRHProcessBase {

  /**
   * @var ContentEntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name){
    $this->entity = $entity;
    $entities = [];
    $langcode = $srh_data['langcode'] ?? \Drupal::languageManager()->getCurrentLanguage()->getId();
    if($srh_entity_refernce_data = $this->getSRHEntityReferenceData($srh_data)){
      if($this->isMultiple()){
        foreach ($srh_entity_refernce_data as $delta => $srh_entity_reference){
          $entities[] = $this->provideEntityRefernce($srh_entity_reference, $langcode, $delta);
        }
      }else{
        $entities[] = $this->provideEntityRefernce($srh_entity_refernce_data, $langcode);
      }
    }
    return $entities;
  }

  /**
   * @return bool
   */
  protected function isMultiple(){
    return FALSE;
  }

  /**
   * Returns the entity reference data from SRH global data.
   *
   * @param $srh_data
   * @return mixed
   */
  abstract protected function getSRHEntityReferenceData($srh_data);

  /**
   * Create entity reference from SRH data
   *
   * @param $srh_entity_refernce_data
   * @param string $langcode
   *   The langcode from srh recipe
   * @param int $delta
   *
   * @return mixed
   */
  abstract public function provideEntityRefernce($srh_entity_refernce_data, $langcode, $delta = 0);

  /**
   * @param $srh_data
   * @param $langcode
   * @return array
   */
  abstract public function getValues($srh_data, $langcode);


}
