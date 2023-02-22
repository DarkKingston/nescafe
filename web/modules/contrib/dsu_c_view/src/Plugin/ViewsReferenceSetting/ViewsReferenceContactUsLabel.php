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
 *   id = "contact_us_label",
 *   label = @Translation("Contact Us label"),
 *   default_value = "Contact Us",
 * )
 */
class ViewsReferenceContactUsLabel extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $path = array_keys($form_field['#states']['visible']);
    $form_field['#title'] = $this->t('Contact Us label');
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
      $view->footer['area_text_custom_3']->options['content'] = $value;

      // Replace value while rendering view in case of ajax is enabled.
      $footer = $view->display_handler->getOption('footer');
      $footer['area_text_custom_3']['content'] = $value;
      $view->display_handler->setOption('footer', $footer);
    }
  }

}
