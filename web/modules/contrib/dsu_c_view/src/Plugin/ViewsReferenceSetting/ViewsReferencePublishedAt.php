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
 *   id = "published_on",
 *   label = @Translation("Published On"),
 *   default_value = "-7 days",
 * )
 */
class ViewsReferencePublishedAt extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $form_field['#title'] = $this->t('Number of days before today');
    $form_field['#description'] = $this->t('An offset from the current time such as "+1 day" or "-2 hours -30 minutes"');
    $form_field['#type'] = 'textfield';

    // Show this option only for automatic_dated_list view.
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'automatic_dated_list']],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value)) {
      foreach ($view->filter as $key => $filterPluginBase) {
        if ($filterPluginBase instanceof Date) {
          if (!empty($view->filter[$key]->options['expose']['identifier']) && $view->filter[$key]->options['expose']['identifier'] == 'published_at') {
            $existing_value = $view->filter[$key]->value;
            $existing_value['value'] = $value;
            $view->filter[$key]->value = $existing_value;
          }
        }
      }
    }
  }

}
