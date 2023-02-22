<?php

namespace Drupal\ln_bazaarvoice\Plugin\Field\FieldFormatter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_bazaarvoice\LnBazaarvoiceConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ln_bazaarvoice' formatter.
 *
 * @FieldFormatter(
 *   id = "ln_bazaarvoice",
 *   label = @Translation("Bazaarvoice"),
 *   field_types = {
 *     "string",
 *     "ln_bazaarvoice_id"
 *   }
 * )
 */
class LnBazaarvoiceFormatter extends FormatterBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ModuleHandlerInterface $module_handler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'mode' => LnBazaarvoiceConstants::MODE_REVIEWS,
        'seo' => TRUE,
        'advanced' => [
          'dcc_info' => ''
        ],
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['mode'] = [
      '#title' => $this->t('Mode'),
      '#type' => 'select',
      '#options' => [
        LnBazaarvoiceConstants::MODE_RATING_SUMMARY => $this->t('Rating summary'),
        LnBazaarvoiceConstants::MODE_REVIEWS => $this->t('Reviews'),
        LnBazaarvoiceConstants::MODE_QUESTIONS => $this->t('Questions & Answers'),
        LnBazaarvoiceConstants::MODE_REVIEW_HIGHLIGHTS => $this->t('Review Highlights'),
        LnBazaarvoiceConstants::MODE_INLINE_RATING => $this->t('Inline ratings'),
        LnBazaarvoiceConstants::MODE_SELLER_RATINGS => $this->t('Seller Ratings'),
      ],
      '#default_value' => $this->getSetting('mode'),
    ];

    $elements['seo'] = [
      '#title' => $this->t('SEO'),
      '#type' => 'checkbox',
      '#description' => $this->t('Render schema.org metadata'),
      '#default_value' => $this->getSetting('seo'),
    ];

    $elements['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#collapsible' => TRUE,
      '#open' => FALSE,
    ];
    $advanced_settings = $this->getSetting('advanced');
    $elements['advanced']['dcc_info'] = [
      '#title' => $this->t('DCC mapping'),
      '#description' => $this->t('Field mapping between <a href=":bazaarvoice_url" target="_blank">DCC data attributes</a> and entity fields. The values are in the form of dccAttributte|token. Enter one value per line. For example:', [
          ':bazaarvoice_url' => 'https://knowledge.bazaarvoice.com/wp-content/conversations/en_US/Collect/DCC.html#dcc-data-attributes-1'
        ]) .  '<br /><br />
        productName|[node:title]<br />
        productDescription|[node:summary]<br />
        productSku|[node:field_dsu_sku:value]<br />
        productImageURL|[node:field_dsu_image:0:entity:field_media_image:entity:url]<br />
        productPageURL|[node:url]<br />'
      ,
      '#type' => 'textarea',
      '#default_value' => $advanced_settings['dcc_info'],
    ];

    // Token support.
    if ($this->moduleHandler->moduleExists('token')) {
      $elements['advanced']['token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => [$this->fieldDefinition->getTargetEntityTypeId()],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Mode: @mode', ['@mode' => $this->getSetting('mode')]);
    $summary[] = $this->t('SEO: @seo', ['@seo' => $this->getSetting('seo') ? $this->t('Enabled') : $this->t('Disabled')]);
    $summary[] = $this->t('DCC info: @dcc_info', ['@dcc_info' => !empty($this->getSetting('advanced')['dcc_info']) ? $this->t('Enabled') : $this->t('Disabled')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      /** @var \Drupal\Core\Field\FieldItemInterface $item */
      if($item->value){
        $elements[$delta] = [
          '#theme' => "ln_bazaarvoice",
          '#mode' => $this->getSetting('mode'),
          '#bazaarvoice_id' => $item->value,
          '#bazaarvoice_entity' => $item->getEntity(),
          '#seo' => $this->getSetting('seo'),
          '#dcc_info' => $this->getSetting('advanced')['dcc_info']
        ];
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    //We only support the type string to support the old version of the module
    if($field_definition->getType() == 'string' && $field_definition->getName() != LnBazaarvoiceConstants::OLD_FIELD_NAME){
      return FALSE;
    }

    return TRUE;
  }
}
