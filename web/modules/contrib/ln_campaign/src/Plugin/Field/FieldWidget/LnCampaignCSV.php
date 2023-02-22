<?php

namespace Drupal\ln_campaign\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class LnCampaignCSV extends WidgetBase{

  /**
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var MessengerInterface
   */
  protected $messenger;

  /**
   * @param $plugin_id
   * @param $plugin_definition
   * @param FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param array $third_party_settings
   * @param ModuleHandlerInterface $moduleHandler
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ModuleHandlerInterface $moduleHandler, MessengerInterface $messenger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->moduleHandler = $moduleHandler;
    $this->messenger = $messenger;
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
      $configuration['third_party_settings'],
      $container->get('module_handler'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state){
    $element += [
      '#type' => 'fieldset',
      '#attributes' => ['class' => ['container-inline ln-campaign-csv-widget']],
      '#attached' => ['library' => ['ln_campaign/ln_campaign_csv']],
    ];
    $element['file_upload'] = [
      '#type' => 'managed_file',
      '#name' => 'file_upload',
      '#title' => $this->t('Import CSV File'),
      '#size' => 40,
      '#description' => $this->t('Select the CSV file to be imported.'),
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
    ];
    $element['csv_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSV separator'),
      '#default_value' => ',',
      '#size' => 1,
      '#maxlength' => 1,
    ];
    $exampleLink = $this->getUrlExampleCsv();
    $element['example'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('In the following <a href=":href" target="_blank">link</a> you can download an example csv file',[
        ':href' => $exampleLink,
      ]),
      '#attributes' => ['class' => 'ln-campaign-csv-widget-example'],
    ];
    $csvColumns = $this->getCsvColumns();
    $element['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Import to ' . $items->getFieldDefinition()->getLabel()),
      '#ajax' => [
        'wrapper' => $items->getName() . '-table-data',
        'callback' => [static::class, 'loadRowsFromCsv'],
      ],
      '#field_name' => $items->getName(),
      '#limit_validation_errors' => [
        [$items->getName(),'file_upload'],
        [$items->getName(),'csv_separator']
      ],
      '#attributes' => ['style' => 'display:block;'],
      '#submit' => [],
    ];
    $triggeringElement = $form_state->getTriggeringElement();

    if(isset($triggeringElement['#field_name']) && $triggeringElement['#field_name'] == $items->getName()){
      $rows = $this->getRowsTableResult($form,$form_state);
    }else{
      $complete_form = $form_state->getCompleteForm();
      if(isset($complete_form[$items->getName()]['widget']['result']['table']['#rows'])){
        $rows = $complete_form[$items->getName()]['widget']['result']['table']['#rows'];
      }else{
        $rows = [];
        foreach ($items as $key=>$item){
          $values = $item->getValue();
          foreach ($csvColumns as $column){
            $rows[$key][$column] = $values[$column] ?? '';
          }
        }
      }
    }
    $element['result'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['id' => $items->getName() . '-table-data', 'class' => 'ln-campaign-csv-widget-result'],
      'table' => [
        '#type' => 'table',
        '#rows' => $rows,
        '#header' => $this->getTableHeader(),
        '#empty' => t('No content has been found.'),
      ]
    ];

    return $element;
  }

  /**
   * Returns the field columns values.
   *
   * @return array
   *   The array from columns keys values.
   */
  abstract public function getCsvColumns();

  /**
   * Returns the table header from imported data.
   *
   * @return array
   *   The array from table labels values.
   */
  abstract public function getTableHeader();

  /**
   * Check rows inported.
   *
   * @return bool
   *   Return TRUE if row is valid and false if row not is valid.
   */
  abstract public function checkRow($row);

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  private function getRowsTableResult(array $form, FormStateInterface $form_state){
    $fid = $form_state->getValue([$this->fieldDefinition->getName(),'file_upload',0],FALSE);
    $csvseparator = $form_state->getValue([$this->fieldDefinition->getName(),'csv_separator'], FALSE);
    $csvColumns = $this->getCsvColumns();
    $rows = [];
    if($fid && $csvseparator){
      if($file = File::load($fid)){
        $csvRows = self::getCsvValues($file,$csvseparator);
        foreach ($csvRows as $index=>$row){
          if($this->checkRow($row)){
            foreach ($row as $key=>$cel){
              $rows[$index][$csvColumns[$key]] = $cel;
            }
          }else{
            $this->messenger->addWarning($this->t('Unable to load row @row',['@row' => $index +1]));
          }
        }
      }
    }
    return $rows;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public static function loadRowsFromCsv(array $form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($triggeringElement['#array_parents'], 0, -1));
    return $element['result'];
  }

  /**
   * @param FileInterface $file
   * @param $separator
   * @return array
   */
  public static function getCsvValues(FileInterface $file,$separator){
    $handle = fopen($file->getFileUri(), "r");
    $rows = [];
    if ($handle !== FALSE) {
      while ($line = fgetcsv($handle, 4096, $separator)) {
        $rows[] = $line;
      }
      fclose($handle);
    }
    array_shift($rows);

    return $rows;
  }

  /**
   * @param array $values
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array|mixed
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state){
    $values = $form[$this->fieldDefinition->getName()]['widget']['result']['table']['#rows'] ?? [];
    return $values;
  }

  /**
   * @return \Drupal\Core\GeneratedUrl|string
   */
  public function getUrlExampleCsv(){
    return Url::fromUserInput('/' . $this->moduleHandler->getModule('ln_campaign')->getPath() . '/data/' . $this->pluginId . '.csv')->toString();
  }

}
