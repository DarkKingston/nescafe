<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\ln_datalayer\Session;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * DatalayerEventsBagInterface.
 */
interface DatalayerEventsBagInterface extends SessionBagInterface {
  /**
   * Adds a event.
   *
   * @param string $key
   * @param array $event
   */
  public function add($key, $event);
}
