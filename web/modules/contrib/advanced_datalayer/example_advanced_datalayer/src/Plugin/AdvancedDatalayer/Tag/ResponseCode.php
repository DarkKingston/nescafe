<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerDynamicNameBase;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * The basic "responseCode" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "response_Code",
 *   label = @Translation("Response code"),
 *   description = @Translation("Response code"),
 *   group = "page_Information",
 *   global = TRUE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = TRUE,
 *   weight = 3,
 * )
 */
class ResponseCode extends DatalayerDynamicNameBase {

  /**
   * {@inheritdoc}
   */
  public function processValue($value) {
    $status_code = '200';
    $exception = \Drupal::requestStack()->getCurrentRequest()->attributes->get('exception');
    if ($exception instanceof HttpExceptionInterface) {
      $status_code = $exception->getStatusCode();
    }
    return $status_code;
  }

}
