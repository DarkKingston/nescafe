<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting limit results plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "submit_query_label",
 *   label = @Translation("Submit Query Label"),
 *   default_value = "Search here",
 * )
 */
class ViewsReferenceSubmitQueryLabel extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#title'] = $this->t('Submit Query Label');
    $form_field['#type'] = 'textfield';
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'faq_search']],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value) && $view->storage->id() == 'faq_search') {
      $view->filter['search_api_fulltext']->options['expose']['placeholder'] = $value;
    }
  }

}
