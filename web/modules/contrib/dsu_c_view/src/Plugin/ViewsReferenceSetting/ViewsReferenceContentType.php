<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\filter\Bundle;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting title plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "content_types",
 *   label = @Translation("Content type selection"),
 *   default_value = "",
 * )
 */
class ViewsReferenceContentType extends PluginBase implements
  ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $node_types = NodeType::loadMultiple();
    // We need to display them in a drop down:
    $options = [];
    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }

    // Show this option only for automatic_dated_list view.
    $path = array_keys($form_field['#states']['visible']);
    $form_field = [
      '#type'        => 'select',
      '#title'       => $this
        ->t('Select content type'),
      '#options'     => $options,
      '#description' => $this->t("Do not select 'Article' content type, if you have selected others"),
      '#multiple'    => 'true',
      '#states'      => [
        'visible' => [
          [
            [$path[0] => ['value' => 'automatic_dated_list']],
          ],
        ],
      ],
      '#prefix'      => '<div id="viewbuilder-content-type">',
      '#suffix'      => '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value)) {
      if (($view->id() == 'automatic_dated_list') && ($view->current_display == 'automatic_dated_list_other')) {
        foreach ($view->filter as $key => $filterPluginBase) {
          if ($filterPluginBase instanceof Bundle) {
            if (!empty($view->filter[$key]->options['expose']['identifier']) && $view->filter[$key]->options['expose']['identifier'] == 'type') {
              $view->filter[$key]->value = $value;
            }
          }
        }
      }
    }
  }

}
