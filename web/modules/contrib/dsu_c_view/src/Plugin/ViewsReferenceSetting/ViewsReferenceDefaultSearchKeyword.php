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
 *   id = "default_search_keyword",
 *   label = @Translation("Search Keyword"),
 *   default_value = "",
 * )
 */
class ViewsReferenceDefaultSearchKeyword extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $form_field['#title'] = $this->t('Default Search Keyword');
    $form_field['#type'] = 'textfield';

    // Show this filter only for following views,
    // article_list, documents_reports, presentations,
    // error_page_search_results.
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'article_list']],
          [$path[0] => ['value' => 'documents_reports']],
          [$path[0] => ['value' => 'presentations']],
          [$path[0] => ['value' => 'error_page_search_results']],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value)) {
      if (isset($view->filter['title'])) {
        $view->filter['title']->value = $value;
      }
      if (isset($view->filter['combine']) && $view->storage->id() == 'article_list') {
        $view->filter['combine']->value = $value;
      }
    }
  }

}
