<?php

namespace Drupal\ln_srh_basic\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\LinkRelBase;

/**
 * SRH Alternate meta tag.
 *
 * @MetatagTag(
 *   id = "srh_alternate",
 *   label = @Translation("Alternate"),
 *   description = @Translation("These meta tags are designed to point visitors to versions of the current page in other languages."),
 *   name = "alternate",
 *   group = "ln_srh_alternates",
 *   weight = 4,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = TRUE
 * )
 */
class SRHAlternate extends LinkRelBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $element = []) {
    $form = [
      '#type' => 'textarea',
      '#title' => $this->label(),
      '#default_value' => $this->value(),
      '#required' => $element['#required'] ?? FALSE,
      '#description' => $this->description(),
      '#element_validate' => [[get_class($this), 'validateTag']]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function output() {
    $elements = [];

    $alternates = $this->getArray($this->value());
    foreach ($alternates as $hreflang => $href) {
      $elements[] = [
        '#tag' => 'link',
        '#attributes' => [
          'rel' => $this->name(),
          'href' => $href,
          'hreflang' => $hreflang
        ]
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray($value = '') {
    if (empty($value)) {
      return [];
    }

    $value = str_replace('\r\n', PHP_EOL, $value);
    $value = array_filter(explode(PHP_EOL, $value));

    $alternates = [];
    foreach ($value as $alternate) {
      $alternate = array_filter(explode('|', $alternate));
      $alternates[$alternate[0]] = $alternate[1];
    }

    return $alternates;
  }

}
