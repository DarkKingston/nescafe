<?php

namespace Drupal\ln_srh_menuiq\Plugin\Field\FieldFormatter;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_sidedish_media",
 *   label = @Translation("SRH SideDish Media"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SRHSideDishMedia extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'link_to_recipe' => FALSE,
        'target' => '_blank'
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form,$form_state);
    $elements['link_to_recipe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to recipe'),
      '#default_value' => $this->getSetting('link_to_recipe'),
    ];
    $elements['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Target'),
      '#options' => [
        '_blank' => 'Blank',
        '_self' => 'Self',
        '_parent' => 'Parent',
        '_top' => 'Top',
        'modal' => 'Modal',
      ],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][link_to_recipe]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $this->getSetting('target'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    $sideDish = $items->getEntity();
    $elements = [];
    if($items->isEmpty()){
      if(!$sideDish->get(SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_FIELD)->isEmpty()){
        /** @var TermInterface $srhAssociationType */
        $srhAssociationType = $sideDish->get(SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_FIELD)->entity;
        if($srhAssociationType->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() == SRHMyMenuIQConstants::SRH_ASSOCIATION_TYPE_RECIPE){
          /** @var NodeInterface $recipe */
          if($recipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity){
             if($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_GALLERY_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_GALLERY_FIELD)->isEmpty()){
               $items = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_GALLERY_FIELD);
               foreach ($items as $item){
                 $item->set('_loaded', TRUE);
               }
             }
          }
        }
      }
    }

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Due to render caching and delayed calls, the viewElements() method
      // will be called later in the rendering process through a '#pre_render'
      // callback, so we need to generate a counter that takes into account
      // all the relevant information about this field and the referenced
      // entity that is being rendered.
      $recursive_render_id = $items->getFieldDefinition()->getTargetEntityTypeId()
        . $items->getFieldDefinition()->getTargetBundle()
        . $items->getName()
        // We include the referencing entity, so we can render default images
        // without hitting recursive protections.
        . $items->getEntity()->id()
        . $entity->getEntityTypeId()
        . $entity->id();

      if (isset(static::$recursiveRenderDepth[$recursive_render_id])) {
        static::$recursiveRenderDepth[$recursive_render_id]++;
      }
      else {
        static::$recursiveRenderDepth[$recursive_render_id] = 1;
      }

      // Protect ourselves from recursive rendering.
      if (static::$recursiveRenderDepth[$recursive_render_id] > static::RECURSIVE_RENDER_LIMIT) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity %entity_type: %entity_id, using the %field_name field on the %parent_entity_type:%parent_bundle %parent_entity_id entity. Aborting rendering.', [
          '%entity_type' => $entity->getEntityTypeId(),
          '%entity_id' => $entity->id(),
          '%field_name' => $items->getName(),
          '%parent_entity_type' => $items->getFieldDefinition()->getTargetEntityTypeId(),
          '%parent_bundle' => $items->getFieldDefinition()->getTargetBundle(),
          '%parent_entity_id' => $items->getEntity()->id(),
        ]);
        return $elements;
      }

      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      if (!empty($recipe) && $this->getSetting('link_to_recipe')) {
        $elements[$delta] = [
          '#type' => 'link',
          '#url' => $recipe->toUrl(),
          '#title' => $view_builder->view($entity, $view_mode, $entity->language()->getId()),
        ];
        if ($this->getSetting('target') == 'modal') {
          $elements[$delta]['#attributes']['class'][] = 'use-ajax';
          $elements[$delta]['#attributes']['data-dialog-type'] = 'modal';
          $elements[$delta]['#attributes']['data-ajax-progress'] = 'fullscreen';
          $elements[$delta]['#attributes']['data-dialog-options'] = Json::encode([
            'dialogClass' => 'srh-sidedish-recipe-modal',
          ]);
        }
        else {
          $elements[$delta]['#attributes']['target'] = $this->getSetting('target');
        }
      }
      else {
        $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());
      }

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += ['resource' => $entity->toUrl()->toString()];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $isApplicable = $field_definition->getTargetEntityTypeId() == 'paragraph' && $field_definition->getTargetBundle() == SRHMyMenuIQConstants::SRH_PARAGRAPH_SIDEDISH_BUNDLE;
    return parent::isApplicable($field_definition) && $isApplicable;
  }

}
