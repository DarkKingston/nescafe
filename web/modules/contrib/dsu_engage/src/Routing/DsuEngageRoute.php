<?php

namespace Drupal\dsu_engage\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines a route subscriber to generate dynamic route for engage form.
 */
class DsuEngageRoute {

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $collection = new RouteCollection();

    $dsu_engage_config = \Drupal::config('dsu_engage.settings');
    $enable_engage_form = $dsu_engage_config->get('enable_engage_form');
    $engage_form_path = $dsu_engage_config->get('engage_form_path');

    if (!empty($enable_engage_form)) {

      $route = new Route(
            $engage_form_path,
            [
              '_title' => 'Contact us',
              '_form' => '\Drupal\dsu_engage\Form\DsuEngageForm',
            ],
            [
              '_permission' => 'access content',
            ],
        );
      $collection->add("dsu_engage.form", $route);

    }

    return $collection;
  }

}
