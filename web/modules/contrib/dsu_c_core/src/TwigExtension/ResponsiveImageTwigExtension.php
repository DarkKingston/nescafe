<?php

namespace Drupal\dsu_c_core\TwigExtension;

use Drupal;
use Drupal\image\Entity\ImageStyle;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class DefaultService.
 *
 * @package Drupal\dsu_c_core
 */
class ResponsiveImageTwigExtension extends Twig_Extension {

  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'dsu_c_gallery.ResponsiveImageTwigExtension';
  }

  /**
   * In this function we can declare the extension function.
   */
  public function getFunctions() {
    return [
      new Twig_SimpleFunction('addResponsiveImageStyle', [
        $this,
        'addResponsiveImageStyle',
      ], ['is_safe' => ['html']]),
    ];
  }

  /**
   * This function is used to return responsive_image_style of an image
   *
   * @param string $image_uri
   * @param string $image_style
   * @param string $responsive_img_style_id
   * @param array $classes
   *
   * @return array
   */
  public function addResponsiveImageStyle($image_uri, $image_style, $responsive_img_style_id, $classes) {
    $image_build = [];
    $image_width = $image_height = NULL;
    $img_style_object = ImageStyle::load($image_style);
    if (Drupal::moduleHandler()
        ->moduleExists('image_widget_crop') && is_object($img_style_object)) {
      // The image.factory service will check if our image is valid.
      $image = Drupal::service('image.factory')->get($image_uri);
      if ($image->isValid()) {
        $image_width = $image->getWidth();
        $image_height = $image->getHeight();
      }
      $image_build = [
        '#theme' => 'responsive_image',
        '#width' => $image_width,
        '#height' => $image_height,
        '#responsive_image_style_id' => $responsive_img_style_id,
        '#uri' => $image_uri,
        '#attributes' => ['class' => $classes],
      ];
    }
    return $image_build;
  }

}
