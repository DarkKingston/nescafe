<?php

namespace Drupal\dsu_ratings_reviews\Form;

use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dsu_ratings_reviews\RatingsReviewsDisplayAdapter;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allow selecting a content type in dashboard.
 */
class RatingsBundleSelectForm extends FormBase {

  const FORM_NODE_TYPE = 'type';

  const VALUE_ALL = 'all';

  const ROUTE_BUNDLE_PARAM = 'bundle';

  const ROUTE_NODE_PARAM = 'node';

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
   * RatingsBundleSelectForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Current route.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
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
    return 'dsu_rating_reviews_dashboard_select_bundle';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\dsu_ratings_reviews\RatingsReviewsDisplayAdapter $adapter */
    $adapter = Drupal::classResolver()
      ->getInstanceFromDefinition(RatingsReviewsDisplayAdapter::class);
    $options = $adapter->getNodeTypesWithRatings();
    if (empty($options)) {
      $form[self::FORM_NODE_TYPE] = [
        '#type'   => 'markup',
        '#markup' => $this->t('No content types exist with ratings assigned.'),
      ];
      return $form;
    }

    $form[self::FORM_NODE_TYPE] = [
      '#type'    => 'select',
      '#title'   => $this->t('Select content type'),
      '#options' => [self::VALUE_ALL => ' - Select - '] + $options,
    ];
    $node_parameter = $this->getRouteMatch()
      ->getParameter(self::ROUTE_NODE_PARAM);
    $bundle_parameter = $this->getRouteMatch()
      ->getParameter(self::ROUTE_BUNDLE_PARAM);
    if (!empty($bundle_parameter)) {
      $form[self::FORM_NODE_TYPE]['#default_value'] = $bundle_parameter;
    }
    elseif (!empty($node_parameter) && $node_parameter instanceof NodeInterface) {
      $form[self::FORM_NODE_TYPE]['#default_value'] = $node_parameter->bundle();
    }

    $form['submit_bundle'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $type = $form_state->getValue(self::FORM_NODE_TYPE);
    if (!empty($type) && $type !== 'all') {
      $form_state->setRedirect('dsu_ratings_reviews.admin.dashboard_type', [
        self::ROUTE_BUNDLE_PARAM => $type,
      ]);
    }
    else {
      $form_state->setRedirect('dsu_ratings_reviews.admin.dashboard');
    }
  }

}
