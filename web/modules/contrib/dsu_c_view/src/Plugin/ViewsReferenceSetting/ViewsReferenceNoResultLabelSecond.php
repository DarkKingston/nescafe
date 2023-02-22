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
 *   id = "no_result_label_second",
 *   label = @Translation("No Result Second Label"),
 *   default_value = "in the FAQ",
 * )
 */
class ViewsReferenceNoResultLabelSecond extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#title'] = $this->t('No Result Second Label');
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
      $view->empty['area_text_custom_3']->options['content'] = $value;

      // Replace value while rendering view in case of ajax is enabled.
      $empty = $view->display_handler->getOption('empty');
      $empty['area_text_custom_3']['content'] = $value;
      $view->display_handler->setOption('empty', $empty);
    }
  }

}
