<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerDynamicNameBase;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * The basic "statusHttps" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "statusHttps",
 *   label = @Translation("Status Https"),
 *   description = @Translation("The  parameter  should contain  the  status  code  that  is  sent  by  the server when the page is requested and loaded."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = TRUE,
 *   weight = 14,
 * )
 */
class StatusHttps extends DatalayerDynamicNameBase {

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
