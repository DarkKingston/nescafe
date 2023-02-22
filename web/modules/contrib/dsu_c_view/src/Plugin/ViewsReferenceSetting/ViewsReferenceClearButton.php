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
 *   id = "clear_button",
 *   label = @Translation("Show Clear Button"),
 *   default_value = 1,
 * )
 */
class ViewsReferenceClearButton extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $form_field['#type'] = 'checkbox';

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
    $exposed_options = $view->display_handler->getOption('exposed_form');
    $exposed_options['options']['reset_button'] = $value ? TRUE : FALSE;
    $view->display_handler->setOption('exposed_form', $exposed_options);
  }

}
