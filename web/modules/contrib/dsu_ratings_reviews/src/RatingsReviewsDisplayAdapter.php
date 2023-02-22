<?php

namespace Drupal\dsu_ratings_reviews;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\fivestar\Element\Fivestar;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RatingsReviewsDisplayAdapter.
 */
class RatingsReviewsDisplayAdapter implements ContainerInjectionInterface {

  use StringTranslationTrait;

  const COMMENT_TYPE = 'dsu_ratings_reviews_comment_type';

  const RATINGS_FIELD = 'field_dsu_ratings';

  const RECOMMEND_FIELD = 'field_dsu_recommend';

  const TOS_FIELD = 'field_dsu_tos';

  const REPLY_PERMISSION = 'reply rating comments';

  const REPLY_HIDDEN_FIELDS = [
    self::RECOMMEND_FIELD,
    self::RATINGS_FIELD,
    self::TOS_FIELD,
  ];

  const DISPLAY_NAME_FIELD = 'field_display_name';

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The Date Formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * RatingsReviewsAdaptations constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Entity field manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Date Formatter object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The route match service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser, Connection $connection, EntityFieldManagerInterface $entityFieldManager, DateFormatterInterface $dateFormatter, CurrentRouteMatch $route_match) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->connection = $connection;
    $this->entityFieldManager = $entityFieldManager;
    $this->dateFormatter = $dateFormatter;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('date.formatter'),
      $container->get('current_route_match')
    );
  }

  /**
   * Implements hook_comment_links_alter().
   *
   * Hides reply button on rating comments for non-admins/brands.
   */
  public function commentLinksAlter(array &$links, CommentInterface $entity, array &$context) {
    // Allow just one level of depth in comments.
    if ($entity->getTypeId() !== RatingsReviewsDisplayAdapter::COMMENT_TYPE) {
      return;
    }
    if (!empty($entity->getParentComment())) {
      unset($links['comment']['#links']['comment-reply']);
    }
    else {
      /** @var \Drupal\comment\CommentStorage $commentStorage */
      $commentStorage = $this->entityTypeManager->getStorage('comment');
      $children = $commentStorage->getChildCids([$entity->id() => $entity]);
      $access = $this->currentUser->hasPermission(RatingsReviewsDisplayAdapter::REPLY_PERMISSION);

      // Also limit replies to just one, and only for the brand.
      if (!empty($children) || !$access) {
        unset($links['comment']['#links']['comment-reply']);
      }
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_view() for comment.
   *
   * Customizes comment display to show reply as pseudo field.
   */
  public function commentView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if ($entity->bundle() === RatingsReviewsDisplayAdapter::COMMENT_TYPE && $display->getComponent('reply')) {
      /** @var \Drupal\comment\CommentStorageInterface $comment_storage */
      $comment_storage = $this->entityTypeManager->getStorage('comment');
      /** @var \Drupal\comment\CommentInterface $entity */
      $child_ids = $comment_storage->getChildCids([$entity->id() => $entity]);

      if (!empty($child_ids[0])) {
        $reply = Comment::load($child_ids[0]);
        $view_builder = $this->entityTypeManager->getViewBuilder('comment');
        $build['reply'] = $view_builder->view($reply, $view_mode);
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * Customizes comment reply form for the rating comment.
   */
  public function formCommentFormAlter(&$form, &$form_state, $form_id) {
    // If this is a reply, rating is not allowed in it.
    /** @var \Drupal\comment\CommentForm $comment_form */
    $comment_form = $form_state->getFormObject();
    /** @var \Drupal\comment\CommentInterface $entity */
    $entity = $comment_form->getEntity();
    $field_value = $entity->get('pid')->getValue();

    if (!empty($field_value[0]['target_id']) && $entity->bundle() === RatingsReviewsDisplayAdapter::COMMENT_TYPE) {
      $this->hideFieldsFromReplyRenderArray($form);
    }
    else {
      // If it's not a reply we hide the display name field.
      if (isset($form[self::DISPLAY_NAME_FIELD])) {
        $form[self::DISPLAY_NAME_FIELD]['#access'] = FALSE;
      }
    }
  }

  /**
   * Implements hook_entity_view_alter().
   *
   * Customizes entity view display to remove votes on replies.
   */
  public function entityViewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display) {
    // Any reply to rating comments does not include vote and will use the
    // display field instead of the username.
    if ($entity->getEntityType()
        ->id() === 'comment' && $entity->bundle() === RatingsReviewsDisplayAdapter::COMMENT_TYPE) {
      /** @var \Drupal\comment\CommentInterface $entity */
      $parent_comment = $entity->get('pid')->getValue();
      if (!empty($parent_comment[0]['target_id'])) {
        $this->hideFieldsFromReplyRenderArray($build);
      }

      $build['published_time'] = ['#markup' => $this->dateFormatter->format($entity->getCreatedTime(), 'comment_medium_date')];
    }
  }

  /**
   * Deletes list of fields from replies in display and/or form.
   *
   * @param array $render
   *   Build or form render array.
   */
  private function hideFieldsFromReplyRenderArray(array &$render) {
    foreach (self::REPLY_HIDDEN_FIELDS as $field_name) {
      if (isset($render[$field_name])) {
        unset($render[$field_name]);
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter() for views_exposed_form.
   *
   * Modify exposed form for the dsu_comments view.
   */
  public function commentsExposedFormAlter(&$form, $form_state, $form_id) {
    if (!$this->isCommentsExposedForm($form_id, $form_state)) {
      return;
    }

    /** @var \Drupal\views\Entity\View $storageView */
    $viewStorage = $form_state->getStorage('view');
    $view = isset($viewStorage['view']) ? $viewStorage['view'] : NULL;
    $nid = $view->args[0];
    $info = $this->getCommentsStatistics(intval($nid));

    // Show rating form widget just for results.
    $this->setFivestarWidget($form, $info);
    // Show progress bars on radio button with results.
    $this->alterRatingRadioButtons($form, $info);
    // Use checkboxes instead of dropdowns.
    $this->alterDropdowns($form);
    // With everything prepared, add automatic submission.
    $this->addAutoSubmission($form);
  }

  /**
   * Obtains the votes statistics and returns the info.
   *
   * @param int $nid
   *   The node id.
   *
   * @return array
   *   Info to be add the widgets and forms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getCommentsStatistics($nid) {
    $info = [];
    if (empty($nid)) {
      return $info;
    }
    // Calculate all statistics.
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $comment_count = $this->getNodeCommentsCountPerStatus($nid);
    $published_comments = !empty($comment_count[$node->bundle()][1]) ? $comment_count[$node->bundle()][1] : 0;
    $info['results'] = $this->getItemVotingStatistics($nid);
    $info['average'] = $this->getAverage($info['results']);
    $info['total'] = $published_comments;
    $info['avg_recommended_count'] = 0;
    if($published_comments){
      // Get % of recommended comment.
      $cids = $this->getavgrecommendedval($nid);
      $avg_recommended_count = ((int) $cids / $published_comments) * 100;
      $avg_recommended_count = (int)(round($avg_recommended_count));
      if (!empty($avg_recommended_count)) {
        $info['avg_recommended_count'] = $avg_recommended_count;
      }
    }

    // End here.
    return $info;
  }

  /**
   * Checks if a form is the exposed of the view form our comments view.
   *
   * @param string $form_id
   *   The id of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return bool
   *   True if it's the comments view exposed form, false otherwise.
   */
  protected function isCommentsExposedForm($form_id, FormStateInterface $form_state) {
    // Double check in case we use from another hook_form_FORM_ID_alter.
    if ($form_id !== 'views_exposed_form') {
      return FALSE;
    }

    $viewStorage = $form_state->getStorage('view');
    if (empty($viewStorage)) {
      return FALSE;
    }
    /** @var \Drupal\views\Entity\View $storageView */
    $view = isset($viewStorage['view']) ? $viewStorage['view'] : NULL;
    if (empty($view) || $view->id() !== 'dsu_ratings_node_view' || !isset($view->args[0])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Hide the dropdownds for filters and use checkboxes to manage the state.
   *
   * @param array $form
   *   Array with form definition.
   */
  protected function alterDropdowns(array &$form) {
    $form['recommend']['#type'] = 'hidden';
    $form['recommend_checkbox'] = [
      '#type'  => 'checkbox',
      '#title' => t('Recommended'),
    ];

    // Count created.
    $form['sort_by']['#type'] = 'hidden';
    // ASC DESC.
    $form['sort_order']['#type'] = 'hidden';
    $form['sort_by_useful_checkbox'] = [
      '#type'  => 'checkbox',
      '#title' => t('Most useful first'),
    ];
  }

  /**
   * Add the autosubmition functionality for the form.
   *
   * @param array $form
   *   Form array.
   */
  protected function addAutoSubmission(array &$form) {
    $form['stars']['#attributes']['class'][] = 'auto-submit-click';
    $form['recommend']['#attributes']['class'][] = 'auto-submit-click';

    $form['#attached']['library'][] = 'dsu_ratings_reviews/ratings-autosubmit';
  }

  /**
   * Implements hook_preprocess_node_links_alter().
   *
   * Modifies the "Add new comment" text for our comment type.
   */
  public function nodeLinksAlter(array &$links, NodeInterface $entity, array &$context) {
    // Find all comment fields with add button.
    $fields = [];
    foreach ($links as $key => $value) {
      if (strpos($key, 'comment__') === 0 && isset($links[$key]['#links']['comment-add'])) {
        $fields[] = str_replace('comment__', '', $key);
      }
    }

    // Filter those that are not DSU ones and replace text.
    foreach ($fields as $field_name) {
      $storage = $entity->get($field_name)
        ->getFieldDefinition()
        ->getFieldStorageDefinition();
      if ($storage->getSetting('comment_type') === self::COMMENT_TYPE) {
        $links['comment__' . $field_name]['#links']['comment-add']['title'] = t('Write a review');
      }
    }
  }

  /**
   * Workaround to gather voting results from comments of a node.
   *
   * @param int $entity_id
   *   Node type.
   * @param string $entity_type
   *   Entity type, node default.
   * @param string $bundle
   *   Bundle, if querying a node.
   *
   * @return array
   *   Voting results, grouped by fivestar value.
   */
  public function getItemVotingStatistics($entity_id, $entity_type = 'node', $bundle = '') {
    $query = $this->connection->select('comment_field_data', 'c');
    $query->join('comment__field_dsu_ratings', 'cr', 'cr.entity_id = c.cid');
    $query->fields('cr', ['field_dsu_ratings_rating']);
    $query->addExpression('count(c.cid)', 'votes_count');

    $query->isNull('c.pid');
    $query->condition('c.comment_type', 'dsu_ratings_reviews_comment_type', '=');
    $query->condition('c.entity_type', $entity_type, '=');
    $query->condition('c.entity_id', $entity_id, '=');
    $query->condition('c.status', '1');
    $query->groupBy("cr.field_dsu_ratings_rating");

    if ($entity_type === 'node' && !empty($bundle)) {
      $query->join('node', 'n', 'n.nid = c.entity_id');
      $query->condition('n.type', $bundle, '=');
    }

    return $query->execute()->fetchAllKeyed(0, 1);
  }

  /**
   * Workaround to gather voting results from node bundles.
   *
   * @param int|null $entity_id
   *   Node type.
   * @param string $bundle
   *   Bundle, if querying a node.
   *
   * @return array
   *   Voting results, grouped by fivestar value.
   */
  public function getAllNodeVotingStatisticsPerBundle($entity_id = NULL, $bundle = '') {
    $query = $this->connection->select('comment_field_data', 'c');
    $query->join('comment__field_dsu_ratings', 'cr', 'cr.entity_id = c.cid');
    $query->join('node', 'n', 'n.nid = c.entity_id');
    $query->fields('n', ['type']);
    $query->fields('cr', ['field_dsu_ratings_rating']);
    $query->addExpression('count(c.cid)', 'votes_count');

    $query->isNull('c.pid');
    $query->condition('c.comment_type', 'dsu_ratings_reviews_comment_type', '=');
    $query->condition('c.entity_type', 'node', '=');
    $query->condition('c.status', CommentInterface::PUBLISHED);
    $query->groupBy("cr.field_dsu_ratings_rating");
    $query->groupBy("n.type");
    if (!empty($bundle)) {
      $query->condition('n.type', $bundle, '=');
    }
    if (!empty($entity_id)) {
      $query->condition('c.entity_id', $entity_id, '=');
    }

    $result = [];
    $node_votes = $query->execute()->fetchAll();
    foreach ($node_votes as $node_stat) {
      $result[$node_stat->type][$node_stat->field_dsu_ratings_rating] = $node_stat->votes_count;
    }

    return $result;
  }

  /**
   * Workaround to gather voting results from node bundles.
   *
   * @param int $entity_id
   *   Node type.
   * @param string $bundle
   *   Bundle, if querying a node.
   *
   * @return array
   *   Recommend results, grouped by value.
   */
  public function getAllNodeRecommendedPerBundle($entity_id = NULL, $bundle = '') {
    $query = $this->connection->select('comment_field_data', 'c');
    $query->join('comment__field_dsu_recommend', 'cr', 'cr.entity_id = c.cid');
    $query->fields('n', ['type']);
    $query->fields('cr', ['field_dsu_recommend_value']);
    $query->addExpression('count(c.cid)', 'votes_count');

    $query->isNull('c.pid');
    $query->condition('c.comment_type', 'dsu_ratings_reviews_comment_type', '=');
    $query->condition('c.entity_type', 'node', '=');
    $query->condition('c.status', CommentInterface::PUBLISHED);
    $query->groupBy("cr.field_dsu_recommend_value");

    $query->join('node', 'n', 'n.nid = c.entity_id');
    $query->groupBy("n.type");

    if (!empty($bundle)) {
      $query->condition('n.type', $bundle, '=');
    }
    if (!empty($entity_id)) {
      $query->condition('c.entity_id', $entity_id, '=');
    }

    $result = [];
    $node_votes = $query->execute()->fetchAll();
    foreach ($node_votes as $node_stat) {
      $result[$node_stat->type][$node_stat->field_dsu_recommend_value] = $node_stat->votes_count;
    }

    return $result;
  }

  /**
   * Returns the node types of the system as options array.
   *
   * @return array
   *   Node type array.
   */
  public function getNodeTypesWithRatings() {
    try {
      $nodes = $this->getNodeCommentsCountPerStatus();
      $node_bundles = array_keys($nodes);
      $types = $this->entityTypeManager->getStorage('node_type')
        ->loadMultiple($node_bundles);
    } catch (\Exception $e) {
      return [];
    }
    $options = [];
    foreach ($types as $name => $type) {
      /** @var \Drupal\node\NodeTypeInterface $type */
      $options[$name] = $type->label();
    }

    return $options;
  }

  /**
   * Count of votes not per comment but by node.
   *
   * @param int $entity_id
   *   Node id.
   * @param string $bundle
   *   Bundle name.
   *
   * @return array
   *   Number of results grouped by content type.
   */
  public function getNodeCommentsCountPerStatus($entity_id = NULL, $bundle = '') {
    $query = $this->connection->select('comment_field_data', 'c');
    $query->join('node', 'n', 'n.nid = c.entity_id');
    $query->isNull('c.pid');

    $query->fields('n', ['type']);
    $query->fields('c', ['status']);
    $query->addExpression('count(c.cid)', 'sum');

    $query->condition('c.comment_type', 'dsu_ratings_reviews_comment_type', '=');
    $query->condition('c.entity_type', 'node', '=');
    $query->groupBy("n.type");
    $query->groupBy("c.status");

    if (!empty($bundle)) {
      $query->condition('n.type', $bundle, '=');
    }
    if (!empty($entity_id)) {
      $query->condition('c.entity_id', $entity_id, '=');
    }

    $result = [];
    $node_comment_count = $query->execute()->fetchAll();
    foreach ($node_comment_count as $content_type_count) {
      if (empty($result[$content_type_count->type])) {
        $result[$content_type_count->type] = [0, 0];
      }
      $result[$content_type_count->type][$content_type_count->status] = $content_type_count->sum;
    }

    return $result;
  }

  /**
   * Count of votes not per comment but by node.
   *
   * @param null|int $entity_id
   *   Node id.
   * @param string $bundle
   *   Bundle name.
   *
   * @return array
   *   Number of results grouped by content type.
   */
  public function getNodeCommentsCount($entity_id = NULL, $bundle = '') {
    $query = $this->connection->select('comment_field_data', 'c');
    $query->join('node', 'n', 'n.nid = c.entity_id');
    $query->isNull('c.pid');

    $query->fields('n', ['type']);
    $query->fields('n', ['nid']);
    $query->addExpression('count(c.cid)', 'sum');

    $query->condition('c.comment_type', 'dsu_ratings_reviews_comment_type', '=');
    $query->condition('c.entity_type', 'node', '=');
    $query->condition('c.status', CommentInterface::PUBLISHED);
    $query->groupBy("n.type");
    $query->groupBy("n.nid");

    if (!empty($bundle)) {
      $query->condition('n.type', $bundle, '=');
    }
    if (!empty($entity_id)) {
      $query->condition('c.entity_id', $entity_id, '=');
    }

    $result = [];
    $node_comment_count = $query->execute()->fetchAll();
    foreach ($node_comment_count as $content_type_count) {
      $result[$content_type_count->type][$content_type_count->nid] = $content_type_count->sum;
    }

    return $result;
  }

  /**
   * Count of nodes not per comment but by node.
   *
   * @param string $bundle
   *   Bundle name.
   *
   * @return array
   *   Number of results grouped by content type.
   */
  public function getNodePerContentType($bundle = '') {
    $query = $this->connection->select('node', 'n');
    $query->fields('n', ['type']);
    $query->addExpression('count(n.nid)', 'sum');
    $query->groupBy("n.type");

    if (!empty($bundle)) {
      $query->condition('n.type', $bundle, '=');
    }

    return $query->execute()->fetchAllKeyed(0, 1);
  }

  /**
   * Count of published recommended comment by node.
   *
   * @param int $nid
   *   node id.
   *
   * @return int
   *   Number of results count.
   */
  public function getavgrecommendedval($nid) {
    $cids_val = '';
    $cids = $this->entityTypeManager
    ->getStorage('comment')
    ->getQuery('AND')
    ->condition('entity_id', $nid)
    ->condition('entity_type', 'node')
    ->condition('comment_type', 'dsu_ratings_reviews_comment_type')
    ->condition('field_dsu_recommend', '1')
    ->condition('status', '1')
    ->execute();
    if (!empty($cids)) {
      $cids_val = (count($cids));
    }
    return $cids_val;
  }

  /**
   * Calculate average of comments for the node.
   *
   * @param array $results
   *   Already queried vote_value => count array.
   *
   * @return float|int
   *   Average voting value.
   */
  private function getAverage(array $results) {
    $sum_votes = 0;
    $vote_count = 0;
    foreach ($results as $value => $count) {
      $sum_votes += $value * $count;
      $vote_count += $count;
    }
    if (empty($vote_count)) {
      return 0;
    }
    return $sum_votes / $vote_count;
  }

  /**
   * Get list of content types of the site.
   *
   * @return array
   *   Content types array as name => label.
   */
  public function getContentTypes() {
    // Only valid node types. content types of the platform.
    try {
      $types = $this->entityTypeManager
        ->getStorage('node_type')
        ->loadMultiple();
    } catch (\Exception $e) {
      $types = [];
    }
    $type_labels = [];
    foreach ($types as $name => $type) {
      /** @var \Drupal\node\NodeTypeInterface $type */
      $type_labels[$name] = $type->label();
    }
    return $type_labels;
  }

  /**
   * Alters form to show a blocked Fivestar Form Widget to show rating.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   * @param array $info
   *   Information array with voting results.
   */
  private function setFivestarWidget(array &$form, array $info) {
    $form['current'] = [
      '#input'              => TRUE,
      '#stars'              => 5,
      '#allow_clear'        => FALSE,
      '#description'        => $this->t('@recommended_count Recommended', [
        '@recommended_count' => $info['avg_recommended_count'].'%',
      ]),
      '#allow_revote'       => FALSE,
      '#allow_ownvote'      => FALSE,
      '#ajax'               => NULL,
      '#show_static_result' => FALSE,
      '#process'            => [
        [Fivestar::class, 'process'],
        [Fivestar::class, 'processAjaxForm'],
      ],
      '#theme_wrappers'     => ['form_element'],
      '#widget'             => [
        'name' => 'default',
      ],
      '#values'             => [
        'vote_user'    => 0,
        'vote_average' => $info['average'],
        'vote_count'   => $info['total'],
      ],
      '#settings'           => [
        'display_format' => 'average',
        'text_format'    => 'none',
      ],
      '#weight'             => $form['stars']['weight'] - 1,
      '#field_prefix'       => $this->t('@star_number out of @star_total', [
        '@star_number' => number_format(($info['average'] / 100) * 5, 1),
        '@star_total'  => 5,
      ]),
      '#field_suffix'       => $this->t('@total_count reviews', [
        '@total_count' => $info['total'],
      ]),
    ];
  }

  /**
   * Alters radio buttons in view exposed form to show ratings and progress bar.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   * @param array $info
   *   Information array with voting results.
   */
  private function alterRatingRadioButtons(array &$form, array $info) {
    // Transform rating from fivestar to final radio buttons format.
    $stars = [];
    $stars['All'] = $info['total'];
    $map = ['All', '100', '80', '60', '40', '20'];
    foreach ($map as $key => $expected_value) {
      $stars[$key] = isset($info['results'][$expected_value]) ? $info['results'][$expected_value] : 0;
    }
    // Add progress bar as suffix.
    foreach ($form['stars']['#options'] as $option_key => $option_markup) {
      $progress = isset($stars[$option_key]) ? $stars[$option_key] : 0;
      $form['stars'][$option_key]['#field_suffix'] = '<progress id="file" value="' . $progress . '" max="' . $info['total'] . '">' . $progress . '</progress><span class="rating">' . $progress . '</span>';
    }
  }

  /**
   * Implements hook_entity_bundle_field_info_alter().
   *
   * Add a custom text constraint to terms of use checkbox field.
   */
  public function entityBundleFieldInfoAlter(&$fields, EntityTypeInterface $entity_type, $bundle) {
    if ($entity_type->id() === 'comment' && $bundle === RatingsReviewsDisplayAdapter::COMMENT_TYPE) {
      if (isset($fields[RatingsReviewsDisplayAdapter::TOS_FIELD])) {
        $fields[RatingsReviewsDisplayAdapter::TOS_FIELD]->addConstraint('TermsAcceptance', []);
      }
    }
  }

  /**
   * Implements hook_views_query_alter().
   *
   * Hides unpublished comments when the user don't have permissions.
   */
  public function viewsQueryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ($view->id() === 'dsu_ratings_node_view' && $this->currentUser->hasPermission(RatingsReviewsDisplayAdapter::REPLY_PERMISSION)
      && $view->current_display === 'block_ratings') {
      foreach ($query->where as &$condition_group) {
        foreach ($condition_group['conditions'] as $key => $condition) {

          if ($condition['field'] === 'comment_field_data.status') {
            unset($condition_group['conditions'][$key]);
          }
        }
      }
    }
  }

  /**
   * Implements hook_entity_view_mode_alter().
   *
   * Modifies reply comments to use its own view mode.
   */
  public function entityViewModeAlter(&$view_mode, EntityInterface $entity, $context) {
    // If comment is a reply (have parent) then show our display mode.
    if ($entity->getEntityTypeId() === 'comment' && !empty($entity->getParentComment())
      && $entity->bundle() === RatingsReviewsDisplayAdapter::COMMENT_TYPE) {
      $view_mode = 'comment.reply';
    }
  }

  /**
   * Implements hook_entity_form_display_alter().
   *
   * Modifies reply comments to use its own form mode.
   */
  public function entityFormDisplayAlter(&$form_display, $context) {
    // Check the specific content type that we are targeting.
    $entity_type = isset($context['entity_type']) ? $context['entity_type'] : NULL;
    $bundle = isset($context['bundle']) ? $context['bundle'] : NULL;
    $comment_type = RatingsReviewsDisplayAdapter::COMMENT_TYPE;
    $route_name = $this->routeMatch->getRouteName();

    // Check if its a reply form, either new or editing.
    /** @var \Drupal\comment\Entity\Comment $comment */
    $comment = $this->routeMatch->getParameter('comment');
    $pid = $this->routeMatch->getParameter('pid');
    $is_new_reply = $route_name === 'comment.reply' && !empty($pid);
    $is_editing_reply = !empty($comment) && !empty($comment->getParentComment());

    // If so, change form display to our reply one.
    if ($entity_type === 'comment' && $bundle === $comment_type && ($is_new_reply || $is_editing_reply)) {
      // If this is a reply, new or edited, show reply form mode.
      $storage = $this->entityTypeManager->getStorage('entity_form_display');
      $form_display = $storage->load('comment.' . $comment_type . '.reply');
    }
  }

  /**
   * Implements hook_entity_presave().
   *
   * Alters comments so replies are always published by default.
   * Also check ratings are always within values and/or 4-stars.
   */
  public function entityPresave(EntityInterface $entity) {
    if ($entity->bundle() === self::COMMENT_TYPE) {
      $pid = $entity->get('pid')->getValue();
      $rating = $entity->get(self::RATINGS_FIELD)->getValue();

      if (empty($rating[0]['rating']) && empty($pid)) {
        $rating[0]['rating'] = '80';
        $entity->set(self::RATINGS_FIELD, $rating);
      }
    }
  }

  /**
   * Implements hook_preprocess_menu().
   *
   * Prevents admin from seeing unneeded moderator menus.
   */
  public function preprocessMenu(&$variables) {
    // Avoid duplicate menus for administrators.
    $roles = $this->currentUser->getRoles();
    if ($this->currentUser->id() == 1 || !in_array('ln_moderator', $roles)) {
      if (isset($variables['items']['dsu_ratings_reviews.moderator.index'])) {
        unset($variables['items']['dsu_ratings_reviews.moderator.index']);
      }
      if (isset($variables['items']['dsu_ratings_reviews.moderator.dashboard'])) {
        unset($variables['items']['dsu_ratings_reviews.moderator.dashboard']);
      }
      if (isset($variables['items']['dsu_ratings_reviews.moderator.config'])) {
        unset($variables['items']['dsu_ratings_reviews.moderator.config']);
      }
    }
  }

}
