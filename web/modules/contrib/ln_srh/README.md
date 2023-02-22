---

# SMART RECIPE HUB

---

CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Functionality
* Recipe syncing using drush commands
* Troubleshooting
* Maintainers
* Extend


INTRODUCTION
------------

**Smart Recipe Hub** (from now on SRH) it's the backend where recipes are stored. Each recipe have many tools, ingredients, macronutrients and steps.

Recipes could be ordered by some parameters. Collections are groups or recipes (like summer, fresh, cold...) and Tags are other way to group it, like (breakfast, dinner).

This module provides integration of Drupal Website to Smart Recipe Hub.
The process will import and syncronize the recipes existing in the SRH depending on the market and endpoint configured in UI configuration.

The recipes and the entities related to the recipes are stored in the **new Content-type Recipe**.
The importation process could be configurable in the options menu. Otherwise, in every time-configurable cron process, the module will check if there are new, updated or unpublished recipes and import, update or unpublish it locally.
The **Synchronization tool** allows us to apply the synchronization process to one or more recipes by their "SRH ID". If we select to sync a recipe previous imported, then the module will resynchronise it.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Video Embed Field (https://www.drupal.org/project/video_embed_field)

**Important!** To be able to connect to the API and start importing recipes, your project must be onboarded to **Smart Recipe Hub**. If that is not the case, you can request it here: https://aws.nestle.recipes/

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.
* Please configure the module UI and test the connection to the SRH in /admin/config/ln_srh/config
* Once the connection test has passed, the recipes will be imported/syncronized periodically each time the Cron process is run.
* Likewise, it's also possible to synchronise a specific recipe manually through the edition menu "SRH Sync" or configuration tab "Sync Recipes" at /admin/config/lightnest/srh/sync-recipes.


  To uninstall the module, be aware that the content type Recipe and it's content are erased from the site.


CONFIGURATION
-------------

The configuration it's setted in Configuration->Lightnest->Lightnest SRH (/admin/config/lightnest/srh).
In addition, "Mapping" and "Sync Recipes" tabs are included, where we can map the recipe fields and synchronise recipes from their IDs respectively.

### 1. Connection

In this tab we must configure the server call to SMART RECIPE HUB

* **"Configure the Synchronization parameters of the recipes"**: Here we must fill in the "Author", "Interval time" and "Pages sync every time" to set the username to be used as recipe's author, set the interval time to execute migration in seconds and the number of pages we want to synchronise by iteration.
  We can also check if we want to "Active the recipe synchronization" to active the "Cron Process" (Without this options, there recipes will not be syncronize periodically); and if we want "Allow to delete obsolete recipes" for recipes that no longer exist in SRH to be removed from the system.


* **"Configure the server call to SMART RECIPE HUB"**: On this section we must set the parameters "Url", "Channel ID", "API Key" and "SRH Market Code"; ask to the Nestle Digital Hub to get it.


* **"Configure the language of the connector"**: Here we must set the language market code (locale code) to synchronise all the recipes with this code, and the "Content prefix" to be used to identify the recipe contents of this language.
  As can be seen, it's possible to add several languages simultaneously for multi-language markets.

### 2. Mapping

The Recipe Mapping tab allows us to set the field mapping for Recipes and the Complement Mapping tab allows us to set the field mapping for Complements.

### 3. Sync

Symc Recipes tab we can introduce one or more Recipe IDs to synchronise them specifically.
Same for Sync Complements tab that will work for complements synchronization.


FUNCTIONALITY
-------------

* User can set configuration settings to perform recipe and complements synchronization.

* Migration of all Recipes and Complements of a specific market to local can be performed.

* A new Recipe or Complement can be added, with Paragraphs and Taxonomies integrated.

* **"Mapping"**.

* SRH Sync options allows to re-synchronise a recipe/complement through the recipe/complement edit operations.

* An option for mass synchronization of recipes/complements was added to the bulk actions list on /admin/content.

* A single or multiple Recipe can be synchronized by **"Sync Recipes"** configuration tab. Same for complements, using **"Sync Complements"** configuration tab.

### DEFAULT RECIPE FIELDS

- **Title**
- **SRH Id** (field_srh_id)

### DEFAULT COMPLEMENT FIELDS

- **Title**
- **SRH Id** (field_srh_id)
- **Ingredients** (field_srh_ingredients)
- **Nutrients** (field_srh_nutrients)
- **SRH Media Gallery** (field_srh_media_gallery)

RECIPE SYCING USING DRUSH COMMANDS
----------------------------------

For recipes, two drush commands are provided with which, on the one hand, recipes can be synchronized manually (drush srh-sync-recipes) and, on the other hand, the recipe synchronization queue can be launched (drush srh-recipes-sync-queue).
Same for complements: drush srh-sync-complements and  drush srh-complements-sync-queue

Examples:
  * Manual synchronization of recipes:

    **drush srh-sync-recipes [srh_id1,srh_id2...] [--only_published=false --page_size=10 --batch_pages=10 --batch_recipes=5]**

  * Running the sync queue:

    **drush srh-recipes-sync-queue**

  * Manual synchronization of complements:

    **drush srh-sync-complements [srh_id1,srh_id2...] [--only_published=false --page_size=10 --batch_pages=10 --batch_recipes=5]**

  * Running the sync queue:

    **drush srh-complements-sync-queue**


MAINTAINERS
-----------

* Nestle Webcms team.


EXTEND
------

To map the fields of our SRH Recipe entity with the information we get from server calls to the SMART RECIPE HUB, we use a series of predefined plugins that process each field.

All plugins extend the base class **"SRHProcessBase"** which implements the basic functions of each plugin:
* process
  * Function that processes the data obtained by the call to the server and parses it so that it is compatible with our target field.
* settingsForm
  * We can design a form to manage the plugin configuration (source field, vocabulary target, etc...).
* label
  * Returns the label of the plugin.
* isApplicable
  * Function in which we can define the rules of whether a plugin is applicable to a field.

The main plugins that extend the base are:
* SRHProcessDefault
* SRHProcessEntityReference

While the **"SRHProcessDefault"** plugin is designed to parse a text field, **"SRHProcessEntityReference"** is for any field that is an entity reference (taxonomy, paragraph, etc...). With that intention, a few plugins have been defined that extend **"SRHProcessEntityReference"**:
* SRHProcessParagraph
* SRHProcessTerm

A **"SRHProcessEntityReference"** plugin must implement the following functions:
* getSRHEntityReferenceData
  * Returns the entity reference data from SRH global data.
* provideEntityRefernce
  * Create or update entity reference from SRH data
* getValues
  * Parse the data we want to save.

With the help of all these predefined plugins, we can create new processors within our custom module. We can make one from scratch by extending the base (**"SRHProcessBase"**) or we can take advantage and extend one of the plugins already defined (**"SRHProcessEntityReference"**, **"SRHPRocessTerm"**, **"SRHProcessParagraph"**, etc...). To do this, we just have to create a new **"SRHProcess"** plugin inside the 'Plugins' folder of our custom module. Our new plugin will extend the class that we think is most convenient and implement the basic functions.

---

Here is an example of how to create a new **"SRHProcess"** plugin:

  >We want to save a new field in our recipe entity where we will save the brand along with a field with a custom prefix.

  1. We have created a new vocabulary with two fields:
      * field_srh_id
        * A text field to store the srh id.
      * field_srh_custom
        * A text field to store the custom id with a prefix.

  2. We have added a new field to our recipe entity, of type taxonomy entity reference and that points to the new vocabulary.

  3. We create our new **"SRHProcess"** plugin inside our custom module (__"modules/custom/our_custom_module/src/Plugin/SRHProcess"__). This would be an example plugin:

  ```PHP
  <?php

  namespace Drupal\our_custom_module\Plugin\SRHProcess;

  use Drupal\Core\Entity\EntityStorageException;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Field\FieldDefinitionInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessEntityReference;
  use Drupal\taxonomy\Entity\Vocabulary;
  use Drupal\taxonomy\TermInterface;
  use Drupal\taxonomy\TermStorageInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
  * Provides a SRHProcess generator plugin.
  *
  * @SRHProcess(
  *   id = "srh_custom_process",
  *   field_name = "custom_process",
  *   label = @Translation("Custom process")
  * )
  */
  class SRHProcessCustom extends SRHProcessEntityReference {

    /**
    * @var TermStorageInterface
    */
    protected $termStorage;

    /**
    * SRHProcessTerm constructor.
    * @param array $configuration
    * @param $plugin_id
    * @param $plugin_definition
    * @param EntityTypeManagerInterface $entityTypeManager
    * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
    * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
    */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
      $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    }

    /**
    * @param ContainerInterface $container
    * @param array $configuration
    * @param string $plugin_id
    * @param mixed $plugin_definition
    * @return SRHProcessTest|static
    * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
    * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
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
    public function defaultConfiguration() {
      return [
        'srh_source_field' => '',
        'vocabulary_id' => NULL,
        'custom_prefix' => ''
      ];
    }

    /**
    * {@inheritdoc}
    */
    public function settingsForm($form, FormStateInterface $form_state) {
      $form = parent::settingsForm($form, $form_state);
      $config = $this->getConfiguration();

      $vocabularies = Vocabulary::loadMultiple();
      $options = [];
      foreach ($vocabularies as $vocabulary) {
        $options[$vocabulary->id()] = $vocabulary->label();
      }

      $form['srh_source_field'] = [
        '#type' => 'textfield',
        '#title' => $this->t('SRH source field'),
        '#default_value' => $config['srh_source_field']
      ];

      $form['vocabulary_id'] = [
        '#type' => 'select',
        '#options' => $options,
        '#title' => $this->t('Vocabulary'),
        '#default_value' => $config['vocabulary_id']
      ];

      $form['custom_prefix'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Custom prefix'),
        '#default_value' => $config['custom_prefix']
      ];

      return $form;
    }

    /**
    * {@inheritdoc}
    */
    public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
      $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
      // This formatter is only available for taxonomy terms.
      return $target_type == 'taxonomy_term';
    }

    /**
    * Returns the entity reference data from SRH global data.
    *
    * @param $srh_data
    * @return mixed
    */
    protected function getSRHEntityReferenceData($srh_data) {
      $config = $this->getConfiguration();
      $source_field = $config['srh_source_field'] ?? FALSE;
      return $srh_data[$source_field] ?? FALSE;
    }

    /**
    * Create entity reference from SRH data
    *
    * @param $srh_entity_refernce_data
    * @param string $langcode
    *   The langcode from srh recipe
    * @return mixed
    */
    public function provideEntityRefernce($srh_term_data, $langcode) {
      $values = $this->getValues($srh_term_data, $langcode);

      /** @var TermInterface $term */
      if (isset($values['field_srh_id'])) {
        $query = $this->termStorage->getQuery();
        $query
          ->condition('vid', $values['vid'])
          ->condition('field_srh_id', $values['field_srh_id']);
        $result = $query->execute();
        $terms = $this->termStorage->loadMultiple($result);
        $term = empty($result) ? FALSE : reset($terms);

        if (!$term) {
          $term = $this->termStorage->create($values);
        }

        foreach ($values as $field_name => $value) {
          $term->set($field_name, $value);
        }

        try {
          $term->save();

          return $term;
        } catch (EntityStorageException $e) {
          \Drupal::logger('ln_srh')->error($e->getMessage());
          return NULL;
        }
      } else {
        return NULL;
      }
    }

    /**
    * @param $srh_data
    * @param $langcode
    * @return array
    */
    public function getValues($srh_data, $langcode) {
      $config = $this->getConfiguration();

      return [
        'name' => $srh_data['name'] ?? $srh_data['localizedName'] ?? $srh_data['localizedDisplayName'] ?? $srh_data['displayName'] ?? $srh_data['description'] ?? NULL,
        'field_srh_id' => $srh_data['id'],
        'field_srh_custom' => $config['custom_prefix'] . $srh_data['id'],
        'vid' => $config['vocabulary_id']
      ];
    }

  }
  ```

  4. Then we would need to map this new field. To do this, we will go to __"/admin/config/lightnest/srh/recipe-mapping"__ and where we see our new field, we will mark **"Enable field mapping"**, we will select our new plugin, we will indicate the destination vocabulary and the prefix that we want to add.

  5. Finally, we will resync the recipes so that our new vocabulary is filled in.
