<?php

namespace Drupal\context_advanced_datalayer\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content reaction that adds a advanced GTM datalayer tags.
 *
 * @ContextReaction(
 *   id = "context_advanced_datalayer",
 *   label = @Translation("Context Advanced Datalayer")
 * )
 */
class ContextAdvancedDatalayer extends ContextReactionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The advanced_datalayer.manager service.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface
   */
  protected $datalayerManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    AdvancedDatalayerManagerInterface $datalayer_manager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->datalayerManager = $datalayer_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('advanced_datalayer.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    // Get the sorted tags.
    $sortedTags = $this->datalayerManager->sortedTags();

    $values = [];

    // Check previous values.
    foreach ($sortedTags as $tagId => $tagDefinition) {
      if (isset($this->getConfiguration()[$tagId])) {
        $values[$tagId] = $this->getConfiguration()[$tagId];
      }
    }

    // Get the base form.
    $form = $this->datalayerManager->form($values, [], TRUE);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $sortedTags = $this->datalayerManager->sortedTags();
    $conf = [];

    foreach ($sortedTags as $tagId => $tagDefinition) {
      if ($form_state->hasValue([$tagDefinition['group'], $tagId])) {
        $conf[$tagId] = $form_state->getValue([$tagDefinition['group'], $tagId]);
      }
    }

    $this->setConfiguration($conf);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->getConfiguration()['context_advanced_datalayer'];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array &$vars = []) {
    return $this->getConfiguration();
  }

}
