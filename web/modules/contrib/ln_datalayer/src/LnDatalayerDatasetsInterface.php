<?php

namespace Drupal\ln_datalayer;

/**
 * Datasets handling service.
 */
interface LnDatalayerDatasetsInterface {

  /**
   * Normalizes the entity into an array structure.
   *
   * @param string $dataset
   *   Dataset file name to load.
   *
   * @return array
   *   Associative array of data from dataset file.
   */
  public function getValues(string $dataset);

}
