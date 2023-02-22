<?php

namespace Drupal\dsu_ratings_reviews\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allow selecting a node in dashboard.
 */
class RatingsNodeSelectForm extends FormBase {

  const FORM_NODE_TYPE = 'node_reference';

  const ROUTE_NODE_PARAM = 'node';

  const ROUTE_BUNDLE_PARAM = 'bundle';

  /**
   * Current route info.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RatingsNodeSelectForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Current route.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(RouteMatchInterface $route, EntityTypeManagerInterface $entity_type_manager) {
    $this->route = $route;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_rating_reviews_dashboard_select_node';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node_parameter = $this->route->getParameter(self::ROUTE_NODE_PARAM);
    $bundle_label = $bundle_parameter = $this->route->getParameter(self::ROUTE_BUNDLE_PARAM);

    // At least one parameter is required.
    if (empty($bundle_parameter) && empty($node_parameter)) {
      return $form;
    }

    // Turn node bundle into label.
    $node = NULL;
    if (!empty($node_parameter) && $node_parameter instanceof NodeInterface) {
      $node = $node_parameter;
      $bundle_parameter = $node->bundle();
      $form[self::FORM_NODE_TYPE]['#default_value'] = $node_parameter;
    }
    elseif (!empty($node_parameter) && is_numeric($node)) {
      /** @var \Drupal\node\NodeInterface $node */
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($node_parameter);
      $bundle_parameter = $node->bundle();
      $form[self::FORM_NODE_TYPE]['#default_value'] = $node_parameter;
    }

    if (!empty($bundle_parameter) && is_string($bundle_parameter)) {
      $bundle_entity = $this->entityTypeManager->getStorage('node_type')
        ->load($bundle_parameter);
      $bundle_label = !empty($bundle_entity) ? $bundle_entity->label() : '';
    }

    $bundle_text = empty($bundle_label) ? 'item' : $bundle_label;
    $bundle_params = ['@bundle' => $bundle_text];
    $form[self::FORM_NODE_TYPE] = [
      '#title'       => $this->t('Reviews by @bundle', $bundle_params),
      '#description' => $this->t('View the KPIs for an individual @bundle', $bundle_params),
      '#type'        => 'entity_autocomplete',
      '#target_type' => 'node',
    ];
    if (!empty($bundle_parameter)) {
      $form[self::FORM_NODE_TYPE]['#selection_settings'] = [
        'target_bundles' => [$bundle_parameter],
      ];
    }

    $form['submit_node'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $form_state->getValue(self::FORM_NODE_TYPE);
    if (!empty($node)) {
      $form_state->setRedirect('dsu_ratings_reviews.admin.dashboard_node', [
        self::ROUTE_NODE_PARAM => $node,
      ]);
    }
    else {
      $form_state->setRedirect('dsu_ratings_reviews.admin.dashboard');
    }
  }

}
