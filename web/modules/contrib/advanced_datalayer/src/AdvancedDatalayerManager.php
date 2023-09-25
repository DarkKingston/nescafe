<?php

namespace Drupal\advanced_datalayer;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Advance datalayer manager.
 *
 * @package Drupal\advanced_datalayer
 */
class AdvancedDatalayerManager implements AdvancedDatalayerManagerInterface {

  /**
   * The group plugin manager.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerGroupPluginManager
   */
  protected $groupPluginManager;

  /**
   * The tag plugin manager.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager
   */
  protected $tagPluginManager;

  /**
   * The advanced_datalayer defaults.
   *
   * @var array
   */
  protected $advancedDatalayerDefaults;

  /**
   * The advanced_datalayer token.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerToken
   */
  protected $tokenService;

  /**
   * The advanced_datalayer logging channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Constructor for AdvancedDatalayerManager.
   *
   * @param \Drupal\advanced_datalayer\AdvancedDatalayerGroupPluginManager $group_plugin_manager
   *   The AdvancedDatalayerTagPluginManager object.
   * @param \Drupal\advanced_datalayer\AdvancedDatalayerTagPluginManager $tag_plugin_manager
   *   The AdvancedDatalayerToken object.
   * @param \Drupal\advanced_datalayer\AdvancedDatalayerToken $token
   *   The AdvancedDatalayerToken object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $channel_factory
   *   The LoggerChannelFactoryInterface object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   */
  public function __construct(
    AdvancedDatalayerGroupPluginManager $group_plugin_manager,
    AdvancedDatalayerTagPluginManager $tag_plugin_manager,
    AdvancedDatalayerToken $token,
    LoggerChannelFactoryInterface $channel_factory,
    EntityTypeManagerInterface $entity_type_manager,
    RouteMatchInterface $route_match,
    LanguageManagerInterface $language_manager,
    PathMatcherInterface $path_matcher
  ) {
    $this->groupPluginManager = $group_plugin_manager;
    $this->tagPluginManager = $tag_plugin_manager;
    $this->tokenService = $token;
    $this->logger = $channel_factory->get('advanced_datalayer');
    $this->advancedDatalayerDefaults = $entity_type_manager->getStorage('advanced_datalayer_defaults');
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * Returns the list of protected defaults.
   *
   * @return array
   *   Th protected defaults.
   */
  public static function protectedDefaults() {
    return [
      'global',
      'front',
      '403',
      '404',
      'login',
      'register',
      'pass',
      'node',
      'taxonomy_term',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function tagsFromEntity(ContentEntityInterface $entity) {
    $tags = [];

    $fields = $this->getFields($entity);

    /** @var \Drupal\field\Entity\FieldConfig $field_info */
    foreach ($fields as $field_name => $field_info) {
      // Get the tags from this field.
      $tags = $this->getFieldTags($entity, $field_name);
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function tagsFromEntityWithDefaults(ContentEntityInterface $entity) {
    return $this->tagsFromEntity($entity) + $this->defaultTagsFromEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultTagsFromEntity(ContentEntityInterface $entity) {
    /** @var \Drupal\advanced_datalayer\Entity\AdvancedDataDatalayerDefaults $datalayer_defaults */
    $datalayer_defaults = $this->advancedDatalayerDefaults->load('global');
    if (!$datalayer_defaults || !$datalayer_defaults->status()) {
      return NULL;
    }
    // Add/overwrite with tags set on the entity type.
    /** @var \Drupal\advanced_datalayer\Entity\AdvancedDataDatalayerDefaults $entity_type_tags */
    $entity_type_tags = $this->advancedDatalayerDefaults->load($entity->getEntityTypeId());
    if ($entity_type_tags !== NULL && $entity_type_tags->status()) {
      $datalayer_defaults->overwriteTags($entity_type_tags->get('tags'));
    }
    // Add/overwrite with tags set on the entity bundle.
    /** @var \Drupal\advanced_datalayer\Entity\AdvancedDatalayerDefaults $bundle_datalayer_defaults */
    $bundle_datalayer_defaults = $this->advancedDatalayerDefaults->load($entity->getEntityTypeId() . '__' . $entity->bundle());
    if ($bundle_datalayer_defaults !== NULL && $bundle_datalayer_defaults->status()) {
      $datalayer_defaults->overwriteTags($bundle_datalayer_defaults->get('tags'));
    }
    return $datalayer_defaults->get('tags');
  }

  /**
   * Gets the group plugin definitions.
   *
   * @return array
   *   Group definitions.
   */
  protected function groupDefinitions() {
    return $this->groupPluginManager->getDefinitions();
  }

  /**
   * Gets the tag plugin definitions.
   *
   * @return array
   *   Tag definitions
   */
  protected function tagDefinitions() {
    return $this->tagPluginManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function sortedGroups() {
    $datalayer_groups = $this->groupDefinitions();

    // Pull the data from the definitions into a new array.
    $groups = [];
    foreach ($datalayer_groups as $group_name => $group_info) {
      $groups[$group_name]['id'] = $group_info['id'];
      $groups[$group_name]['label'] = $group_info['label']->render();
      $groups[$group_name]['description'] = $group_info['description'];
      $groups[$group_name]['weight'] = $group_info['weight'];
    }

    // Create the 'sort by' array.
    $sort_by = [];
    foreach ($groups as $group) {
      $sort_by[] = $group['weight'];
    }

    // Sort the groups by weight.
    array_multisort($sort_by, SORT_ASC, $groups);

    return $groups;
  }

  /**
   * {@inheritdoc}
   */
  public function sortedTags() {
    $datalayer_tags = $this->tagDefinitions();
    // Pull the data from the definitions into a new array.
    $tags = [];
    foreach ($datalayer_tags as $tag_name => $tag_info) {
      $tags[$tag_name]['id'] = $tag_info['id'];
      $tags[$tag_name]['label'] = $tag_info['label']->render();
      $tags[$tag_name]['group'] = $tag_info['group'] ?? '';
      $tags[$tag_name]['weight'] = $tag_info['weight'] ?? 0;
      $tags[$tag_name]['global'] = $tag_info['global'] ?? FALSE;
      $tags[$tag_name]['translatable'] = $tag_info['translatable'] ?? FALSE;
    }

    // Create the 'sort by' array.
    $sort_by = [];
    foreach ($tags as $key => $tag) {
      $sort_by['group'][$key] = $tag['group'];
      $sort_by['weight'][$key] = $tag['weight'];
    }

    if (!empty($tags)) {
      // Sort the tags by weight.
      array_multisort($sort_by['group'], SORT_ASC, $sort_by['weight'], SORT_ASC, $tags);
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function sortedGroupsWithTags() {
    $groups = $this->sortedGroups();
    $tags = $this->sortedTags();

    foreach ($tags as $tag_name => $tag) {
      $tag_group = $tag['group'];

      if (!isset($groups[$tag_group])) {
        // If the tag is claiming a group that has no matching plugin, log an
        // error and force it to the basic group.
        $this->logger->error("Undefined group '%group' on tag '%tag'", [
          '%group' => $tag_group,
          '%tag' => $tag_name,
        ]);

        $tag['group'] = 'basic';
        $tag_group = 'basic';
      }

      $groups[$tag_group]['tags'][$tag_name] = $tag;
    }

    return $groups;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $values, array $element, $remove_global = FALSE, array $token_types = [], array $included_tags = []) {
    // Add the outer fieldset.
    $element += [
      '#type' => 'details',
    ];

    $element += $this->tokenService->tokenBrowser($token_types);
    $groups_and_tags = $this->sortedGroupsWithTags();

    foreach ($groups_and_tags as $group_name => $group) {
      if ($group['id'] === 'root') {
        // Create the fieldset.
        $element[$group_name]['#type'] = 'fieldgroup';
      }
      else {
        // Create the fieldset.
        $element[$group_name]['#type'] = 'details';
        $element[$group_name]['#title'] = $group['label'];
        $element[$group_name]['#description'] = $group['description'];
        $element[$group_name]['#open'] = TRUE;
      }
      if (!empty($group['tags'])) {
        foreach ($group['tags'] as $tag_name => $tag) {
          // Only act on tags in the included tags list, unless that is null.
          if (empty($included_tags) || in_array($tag_name, $included_tags) || in_array($tag['id'], $included_tags)) {
            // Make an instance of the tag.
            $tag = $this->tagPluginManager->createInstance($tag_name);
            // Global tags can be edited only on global default entity.
            if ($remove_global && $tag->isGlobal()) {
              continue;
            }

            // Set the value to the stored value, if any.
            $tag_value = $values[$tag_name] ?? NULL;
            $tag->setValue($tag_value);

            // Create the bit of form for this tag.
            $element[$group_name][$tag_name] = $tag->form($element);
          }
        }
      }
    }
    return $element;
  }

  /**
   * Returns a list of the Datalayer fields on an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to examine.
   *
   * @return array
   *   The fields from the entity which are Datalayer fields.
   */
  protected function getFields(ContentEntityInterface $entity) {
    $field_list = [];

    if ($entity instanceof ContentEntityInterface) {
      // Get a list of the datalayer field types.
      $field_types = $this->fieldTypes();

      // Get a list of the field definitions on this entity.
      $definitions = $entity->getFieldDefinitions();

      // Iterate through all the fields looking for ones in our list.
      foreach ($definitions as $field_name => $definition) {
        // Get the field type, ie: datalayer.
        $field_type = $definition->getType();

        // Check the field type against our list of fields.
        if (isset($field_type) && in_array($field_type, $field_types, TRUE)) {
          $field_list[$field_name] = $definition;
        }
      }
    }

    return $field_list;
  }

  /**
   * Returns a list of the datalayer tags with values from a field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The ContentEntityInterface object.
   * @param string $field_name
   *   The name of the field to work on.
   *
   * @return array
   *   Array of field tags.
   */
  protected function getFieldTags(ContentEntityInterface $entity, $field_name) {
    $tags = [];
    foreach ($entity->{$field_name} as $item) {
      // Get serialized value and break it into an array of tags with values.
      $serialized_value = $item->get('value')->getValue();
      if (!empty($serialized_value)) {
        $tags += unserialize($serialized_value);
      }
    }

    return $tags;
  }

  /**
   * Returns global datalayer tags.
   *
   * @return \Drupal\advanced_datalayer\Entity\AdvancedDatalayerDefaults|null
   *   The global datalayer tags or NULL.
   */
  public function getGlobalDatalayerTags() {
    $datalayer_tags = $this->advancedDatalayerDefaults->load('global');
    return ($datalayer_tags !== NULL && $datalayer_tags->status()) ? $datalayer_tags : NULL;
  }

  /**
   * Returns special datalayer tags.
   *
   * @return \Drupal\advanced_datalayer\Entity\AdvancedDatalayerDefaults|null
   *   The defaults for this page, if it's a special page.
   */
  public function getSpecialDatalayerTags() {
    $datalayer_tags = NULL;

    if ($this->pathMatcher->isFrontPage()) {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('front');
    }
    elseif ($this->routeMatch->getRouteName() === 'system.403') {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('403');
    }
    elseif ($this->routeMatch->getRouteName() === 'system.404') {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('404');
    }
    elseif ($this->routeMatch->getRouteName() === 'user.login') {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('login');
    }
    elseif ($this->routeMatch->getRouteName() === 'user.register') {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('register');
    }
    elseif ($this->routeMatch->getRouteName() === 'user.pass') {
      $datalayer_tags = $this->advancedDatalayerDefaults->load('pass');
    }

    if ($datalayer_tags && !$datalayer_tags->status()) {
      // Do not return disabled special datalayer tags.
      return NULL;
    }

    return $datalayer_tags;
  }

  /**
   * Returns default datalayer tags for an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to work with.
   *
   * @return array
   *   The appropriate default datalayer tags.
   */
  public function getEntityDefaultDatalayerTags(ContentEntityInterface $entity) {
    /** @var \Drupal\advanced_datalayer\Entity\AdvancedDatalayerDefaults $datalayer_defaults */
    $datalayer_defaults = $this->advancedDatalayerDefaults->load($entity->getEntityTypeId());
    $datalayer_tags = [];
    if ($datalayer_defaults !== NULL && $datalayer_defaults->status()) {
      // Merge with global defaults.
      $datalayer_tags = array_merge($datalayer_tags, $datalayer_defaults->get('tags'));
    }

    // Finally, check if we should apply bundle overrides.
    /** @var \Drupal\advanced_datalayer\Entity\AdvancedDatalayerDefaults $bundle_datalayer_defaults */
    $bundle_datalayer_defaults = $this->advancedDatalayerDefaults->load($entity->getEntityTypeId() . '__' . $entity->bundle());
    if ($bundle_datalayer_defaults !== NULL && $bundle_datalayer_defaults->status()) {
      // Merge with existing defaults.
      $datalayer_tags = array_merge($datalayer_tags, $bundle_datalayer_defaults->get('tags'));
    }

    return $datalayer_tags;
  }

  /**
   * Generate the elements that go in the hook_page_attachments attached array.
   *
   * @param array $tags
   *   The array of tags as plugin_id => value.
   * @param object $entity
   *   Optional entity object to use for token replacements.
   *
   * @return array
   *   Render array with tag elements.
   */
  public function generateElements(array $tags, $entity = NULL) {

    $tags = $this->generateRawElements($tags, $entity);

    return $tags;
  }

  /**
   * Generate the actual datalayer tag values.
   *
   * @param array $tags
   *   The array of tags as plugin_id => value.
   * @param object $entity
   *   Optional entity object to use for token replacements.
   *
   * @return array
   *   Render array with tag elements.
   */
  public function generateRawElements(array $tags, $entity = NULL) {

    // Prepare any tokens that might exist.
    $token_replacements = [];
    if ($entity && $entity instanceof ContentEntityInterface) {
      $token_replacements = [$entity->getEntityTypeId() => $entity];
    }

    // Get the current language code.
    $langcode = $this->languageManager
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);

    $rawTags = [];

    $datalayer_tags = $this->tagPluginManager->getDefinitions();

    // Order the elements by weight.
    uksort($tags, function ($tag_name_a, $tag_name_b) use ($datalayer_tags) {
      $weight_a = $datalayer_tags[$tag_name_a]['weight'] ?? 0;
      $weight_b = $datalayer_tags[$tag_name_b]['weight'] ?? 0;

      return ($weight_a < $weight_b) ? -1 : 1;
    });

    // Each element of the $values array is a tag with the tag plugin name as
    // the key.
    foreach ($tags as $tag_name => $value) {
      // Check to ensure there is a matching plugin.
      if (isset($datalayer_tags[$tag_name])) {
        // Get an instance of the plugin.
        try {
          $tag = $this->tagPluginManager->createInstance($tag_name);
        }
        catch (PluginException $e) {
          return [];
        }

        // Set the value.
        $tag->setValue($value);

        if (!$tag->isTranslatable()) {
          // If tag not translatable, set default site language to use in token.
          $default_langcode = $this->languageManager
            ->getCurrentLanguage(LanguageInterface::LANGCODE_SITE_DEFAULT);
          // Switch configs to correct language.
          $language_manager = $this->languageManager;
          $language_manager->setConfigOverrideLanguage($default_langcode);
          $processed_value = PlainTextOutput::renderFromHtml(htmlspecialchars_decode($this->tokenService->replace($tag->value(), $token_replacements, ['langcode' => $default_langcode->getId()])));
          $language_manager->setConfigOverrideLanguage($langcode);
        }
        else {
          // Obtain the processed value with current language..
          $processed_value = PlainTextOutput::renderFromHtml(htmlspecialchars_decode($this->tokenService->replace($tag->value(), $token_replacements, ['langcode' => $langcode->getId()])));
        }

        // Now store the value with processed tokens back into the plugin.
        $tag->setValue($processed_value);

        // Have the tag generate the output based on the value we gave it.
        $output = $tag->output();

        if (!empty($output)) {
          $rawTags[$output['group']][$output['tag']] = $output['value'];
        }
      }
    }
    if (isset($rawTags['root'])) {
      $root_elements = $rawTags['root'];
      unset($rawTags['root']);
      $rawTags = $root_elements + $rawTags;

    }

    return $rawTags;
  }

  /**
   * Returns a list of fields handled by advanced_datalayer.
   *
   * @return array
   *   A list of supported field types.
   */
  protected function fieldTypes() {
    return ['advanced_datalayer'];
  }

}
