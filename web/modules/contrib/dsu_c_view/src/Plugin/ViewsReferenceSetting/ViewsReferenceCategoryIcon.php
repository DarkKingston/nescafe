<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting title plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "category_icon",
 *   label = @Translation("Display Category Icon"),
 *   default_value = 1,
 * )
 */
class ViewsReferenceCategoryIcon extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    // Show this option only for ln_c_mosaic view.
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#type'] = 'checkbox';
    $form_field['#description'] = $this->t('Allow display of category icon in tiles.');
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'ln_c_mosaic']],
        ],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (empty($value)) {
      $view->style_plugin->options['row_class'] = '';
    }
  }

}
