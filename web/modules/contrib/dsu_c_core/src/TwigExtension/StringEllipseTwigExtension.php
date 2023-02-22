<?php

  namespace Drupal\dsu_c_core\TwigExtension;

  use Drupal;
  use Twig_Extension;
  use Twig_SimpleFunction;

  /**
   * Class DefaultService.
   *
   * @package Drupal\dsu_c_core
   */
  class StringEllipseTwigExtension extends Twig_Extension {

    /**
     * {@inheritdoc}
     * This function must return the name of the extension. It must be unique.
     */
    public function getName() {
      return 'dsu_c_cdoe.StringEllipseTwigExtension';
    }

    /**
     * In this function we can declare the extension function.
     */
    public function getFunctions() {
      return [
        new Twig_SimpleFunction('addEllipseInString', [
          $this,
          'addEllipseInString',
        ], ['is_safe' => ['html']]),
      ];
    }

    /**
     * This function is used to return the substring with limited character
     * with ellipse.
     *
     * @param string $string
     * @param string $char_limit
     * @param string $ellipse
     *
     * @return array
     */
    public function addEllipseInString($string, $char_limit, $ellipse = '') {
      $trim_string = '';
      if (!empty($string)) {
        $text = preg_replace("/\r|\n/", "", trim(strip_tags($string)));
        $trim_string = (strlen($text) <= $char_limit) ? $text : substr($text, 0, strpos(wordwrap($text, $char_limit), "\n")) . $ellipse;
      }
      return $trim_string;
    }
  }
