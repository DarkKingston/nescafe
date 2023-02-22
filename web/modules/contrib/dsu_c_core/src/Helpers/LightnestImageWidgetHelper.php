<?php

namespace Drupal\dsu_c_core\Helpers;


/**
 * Contains helper functions for manipulating image field widgets.
 */
class LightnestImageWidgetHelper {

  /**
   * Alters an image widget form element.
   *
   * @param array $element
   *   The widget form element.
   */
  public static function alter(array &$element) {
    $element['#process'][] = [static::class, 'process'];
  }
    /**
   * Process callback: update alt length of an image widget form element.
   *
   * @param array $element
   *   The form element.
   *
   * @return array
   *   The processed form element.
   */
  public static function process(array $element) {

    $element['alt']['#maxlength'] = '125';
    $element['alt']['#description'] = t('Short description (125 characters max) of the image used by screen readers and displayed when the image is not loaded. This is important for accessibility.');
    return $element;
  }

}
