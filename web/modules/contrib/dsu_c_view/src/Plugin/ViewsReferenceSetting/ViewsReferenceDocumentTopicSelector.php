<?php

namespace Drupal\dsu_c_view\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\Plugin\views\filter\Bundle;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The views reference setting title plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "document_topic_selector",
 *   label = @Translation("Document Topic selection"),
 *   default_value = "All",
 * )
 */
class ViewsReferenceDocumentTopicSelector extends PluginBase implements
  ViewsReferenceSettingInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Taxonomy Storage services.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $taxonomyStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager) {
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
    $options = [];

    $terms = $this->taxonomyStorage->loadTree('document_report');
    $options['All'] = $this->t('All');
    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }

    $path = array_keys($form_field['#states']['visible']);
    $form_field['#type'] = 'select';
    $form_field['#title'] = $this->t('Select topic');
    $form_field['#options'] = $options;
    $form_field['#states'] = [
      'visible' => [
        [
          [$path[0] => ['value' => 'documents_reports']],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value) && $view->storage->id() == 'documents_reports') {
      foreach ($view->filter as $filterPluginBase) {
        if ($filterPluginBase instanceof Bundle) {
          $view->filter['field_document_report_topics_target_id']->value = $value;
        }
      }
    }
  }

}
