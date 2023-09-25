<?php

namespace Drupal\dsu_ratings_reviews\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\dsu_ratings_reviews\RatingsReviewsDisplayAdapter;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller responses to paint the dashboard.
 */
class RatingsReviewsDashboardController extends ControllerBase {

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RatingsReviewsDashboardController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(FormBuilderInterface $form_builder, EntityTypeManagerInterface $entity_type_manager) {
    $this->formBuilder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Display the dashboard without filters.
   *
   * @return array
   *   Return markup array.
   */
  public function main() {
    // We won't show statistics if user did not select anything.
    $bundle_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsBundleSelectForm');
    $node_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsNodeSelectForm');

    return [
      '#type'               => '#theme',
      '#theme'              => 'page_statistics',
      '#data'               => [],
      '#bundle_select_form' => $bundle_select_form,
      '#node_select_form'   => $node_select_form,
      '#node'               => NULL,
    ];
  }

  /**
   * Display the dashboard.
   *
   * @param string $bundle
   *   Bundle of the node to search for.
   *
   * @return array
   *   Return markup array.
   */
  public function bundle($bundle = '') {
    $bundle_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsBundleSelectForm');
    $node_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsNodeSelectForm');
    $statistics = [];

    // We don't show statistics either if bundle has no comments.
    /** @var \Drupal\dsu_ratings_reviews\RatingsReviewsDisplayAdapter $adapter */
    $adapter = Drupal::classResolver()
      ->getInstanceFromDefinition(RatingsReviewsDisplayAdapter::class);
    $options = $adapter->getNodeTypesWithRatings();
    if (!empty($bundle) && !empty($options[$bundle])) {
      $statistics = $this->getStatistics(NULL, $bundle);
    }
    return [
      '#type'               => '#theme',
      '#theme'              => 'page_statistics',
      '#data'               => $statistics,
      '#bundle_select_form' => $bundle_select_form,
      '#node_select_form'   => $node_select_form,
      '#node'               => NULL,
    ];
  }

  /**
   * Get the entire statistics grouped per bundle.
   *
   * @param null|int $entity_id
   *   Optional node id, if empty, all will be counted.
   * @param string $bundle
   *   Optional node bundle, if empty, all will be counted.
   *
   * @return array
   *   Array of statistics.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getStatistics($entity_id = NULL, $bundle = '') {
    /** @var \Drupal\dsu_ratings_reviews\RatingsReviewsDisplayAdapter $adapter */
    $adapter = Drupal::classResolver()
      ->getInstanceFromDefinition(RatingsReviewsDisplayAdapter::class);
    $type_names = $adapter->getContentTypes();
    $node = NULL;

    if (!empty($entity_id)) {
      /** @var \Drupal\node\NodeInterface $node */
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($entity_id);
      $bundle = $node->bundle();
    }
    elseif (empty($bundle) || !in_array($bundle, array_keys($type_names))) {
      $bundle = '';
    }

    $node_votes = $adapter->getAllNodeVotingStatisticsPerBundle($entity_id, $bundle);
    $node_recommendations = $adapter->getAllNodeRecommendedPerBundle($entity_id, $bundle);
    $node_published = $adapter->getNodeCommentsCountPerStatus($entity_id, $bundle);
    $nodes_with_reviews = $adapter->getNodeCommentsCount($entity_id, $bundle);
    if (!empty($entity_id)) {
      $nodes_per_content = [$bundle => (int) $node->isPublished()];
    }
    else {
      $nodes_per_content = $adapter->getNodePerContentType($bundle);
    }

    // Join all data to visualize.
    $result = [];
    $bundles = !empty($bundle) ? [$bundle] : array_keys($type_names);
    foreach ($bundles as $type_name) {
      $votes = [
        '20'  => 0,
        '40'  => 0,
        '60'  => 0,
        '80'  => 0,
        '100' => 0,
      ];
      $published_values = [0, 0];
      $recommendation_values = [0, 0];
      $nodes_with_reviews_value = 0;
      if (isset($node_votes[$type_name])) {
        $votes = $node_votes[$type_name] + $votes;
      }
      if (isset($node_recommendations[$type_name])) {
        $recommendation_values = $node_recommendations[$type_name] + $recommendation_values;
      }
      if (isset($node_published[$type_name])) {
        $published_values = $node_published[$type_name] + $published_values;
      }
      if (isset($nodes_with_reviews[$type_name])) {
        $nodes_with_reviews_value = count($nodes_with_reviews[$type_name]);
      }
      $nodes_per_this_bundle = isset($nodes_per_content[$type_name]) ? $nodes_per_content[$type_name] : 0;
      $sum = 0;
      foreach ($votes as $value => $count) {
        $sum = $sum + ($value * $count);
      }
      $recommendation_total = array_sum(array_values($recommendation_values));
      $total = array_sum(array_values($votes));
      $average = $total != 0 ? (float) $sum / (float) $total : 0;
      $fourStarVotesPercentage = $total != 0 ? (($votes['80'] + $votes['100']) / $total) : 0;
      $recommendationsPercentage = $recommendation_total ? ($recommendation_values[1] / $recommendation_total) : 0;

      $result[$type_name] = [
        'label'                         => $type_names[$type_name],
        'votes'                         => $votes,
        'average'                       => number_format($average / 20, 2),
        'totalVotes'                    => $total,
        'fourStarVotesPercentage'       => number_format($fourStarVotesPercentage, 2) * 100,
        'recommendations'               => $recommendation_values,
        'recommendationsPercentage'     => number_format($recommendationsPercentage, 2) * 100,
        'published'                     => $published_values,
        'nodesWithReviews'              => 0,
        'nodesWithoutReviews'           => 0,
        'nodesWithReviewsPercentage'    => 0,
        'nodesWithoutReviewsPercentage' => 0,
        'totalNodes'                    => 0,
      ];
      // No need to count nodes if empty.
      if (!empty($nodes_per_this_bundle)) {
        $result[$type_name] = [
            'nodesWithReviews'              => $nodes_with_reviews_value,
            'nodesWithoutReviews'           => $nodes_per_this_bundle - $nodes_with_reviews_value,
            'nodesWithReviewsPercentage'    => number_format(($nodes_with_reviews_value / (float) $nodes_per_this_bundle), 2) * 100,
            'nodesWithoutReviewsPercentage' => number_format((($nodes_per_this_bundle - $nodes_with_reviews_value) / (float) $nodes_per_this_bundle), 2) * 100,
            'totalNodes'                    => $nodes_per_this_bundle,
          ] + $result[$type_name];
      }
    }
    return $result;
  }

  /**
   * Display the dashboard.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node route parameter.
   *
   * @return array
   *   Return markup array.
   */
  public function node(NodeInterface $node) {
    $bundle_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsBundleSelectForm');
    $node_select_form = $this->formBuilder->getForm('Drupal\dsu_ratings_reviews\Form\RatingsNodeSelectForm');
    $statistics = $this->getStatistics($node->id(), $node->bundle());
    return [
      '#type'               => '#theme',
      '#theme'              => 'page_statistics',
      '#data'               => $statistics,
      '#bundle_select_form' => $bundle_select_form,
      '#node_select_form'   => $node_select_form,
      '#node'               => $node,
    ];
  }

}
