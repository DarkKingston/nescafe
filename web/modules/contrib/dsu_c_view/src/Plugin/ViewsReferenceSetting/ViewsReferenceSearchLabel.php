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
 *   id = "search_label",
 *   label = @Translation("Search Label"),
 *   default_value = "Quick Search",
 * )
 */
class ViewsReferenceSearchLabel extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#title'] = $this->t('Search Label');
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
      $exposed_options = $view->display_handler->getOption('exposed_form');
      $exposed_options['options']['submit_button'] = $value;
      $view->display_handler->setOption('exposed_form', $exposed_options);
    }
  }

}
