<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The views reference setting title plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "article_type",
 *   label = @Translation("Article type selection"),
 *   default_value = "",
 * )
 */
class ViewsReferenceArticleType extends PluginBase implements
  ViewsReferenceSettingInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Taxonomy Storage services.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $taxonomyStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id,
                              $plugin_definition, EntityTypeManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->taxonomyStorage = $entity_manager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $terms = $this->taxonomyStorage->loadTree('article_type');
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }

    $form_field = [
      '#type'        => 'select',
      '#title'       => $this->t('Select article type'),
      '#options'     => $term_data,
      '#multiple'    => 'true',
      '#description' => $this->t("Select only when 'Article' content type selected above."),
      '#attached'    => ['library' => ['dsu_c_view/viewbuilder-articletype']],
      '#prefix'      => '<div id="viewbuilder-article-type">',
      '#suffix'      => '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value)) {
      if (($view->id() == 'automatic_dated_list') && ($view->current_display == 'automatic_dated_list_block')) {
        foreach ($view->filter as $key => $filterPluginBase) {
          if ($filterPluginBase instanceof TaxonomyIndexTid) {
            if (!empty($view->filter[$key]->options['expose']['identifier']) && $view->filter[$key]->options['expose']['identifier'] == 'field_article_type_target_id') {
              $view->filter[$key]->value = $value;
            }
          }
        }
      }
    }
  }

}
