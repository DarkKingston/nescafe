<?php

namespace Drupal\ln_srh\Services;

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

interface SRHUtilsInterface{

  /**
   * @return array
   */
  public function syncRecipes();

  /**
   * @param mixed $srh_recipe
   * @return mixed
   * @throws SRHException
   */
  public function syncRecipe($srh_recipe);

  /**
   * @param $srh_id
   * @return \Drupal\Core\Entity\EntityInterface|false
   */
  public function getRecipeBySRHId($srh_id);


  /**
   * @param $srh_id
   * @return mixed
   */
  public function getSRHRecipe($srh_id);

  /**
   * @param $locale
   *  The srh locale code
   * @return int
   */
  public function getLocaleSync($locale);

  /**
   * @param $locale
   *  The srh locale code
   * @return mixed
   */
  public function updateLocaleSync($locale);

  /**
   * @param NodeInterface $recipe
   * @return mixed
   */
  public function reSyncRecipe(NodeInterface $recipe);

  /**
   * @param $srh_id
   * @param $vid
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getTermBySRHId($srh_id, $vid);

  /**
   * @param $name
   * @param $vid
   * @return \Drupal\Core\Entity\EntityInterface|false|mixed
   */
  public function getTermByName($name, $vid);

  /**
   * @param TermInterface $term
   * @param $langcode
   * @param $values
   * @return mixed|void
   */
  public function addTermTranslation(TermInterface $term, $langcode, $values);

  /**
   * @param $values
   * @param $langcode
   * @return \Drupal\Core\Entity\EntityInterface|TermInterface|mixed|null
   */
  public function provideTerm($values,$langcode);

  /**
   * @param $queue_type
   *
   * @return mixed
   */
  public function processQueueSync($queue_type);

}
