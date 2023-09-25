<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\Plugin\views\filter\Date;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting title plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "year",
 *   label = @Translation("Year Selector"),
 *   default_value = 1,
 * )
 */
class ViewsReferenceYearSelector extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $form_field['#type'] = 'checkbox';

    // Show this option only for views article_list, events.
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'article_list']],
          [$path[0] => ['value' => 'events']],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (empty($value)) {
      foreach ($view->filter as $key => $filterPluginBase) {
        if ($filterPluginBase instanceof Date) {
          if (!empty($view->filter[$key]->options['expose']['identifier']) && $view->filter[$key]->options['expose']['identifier'] == 'year') {
            unset($view->filter[$key]);
          }
        }
      }
    }
  }

}
