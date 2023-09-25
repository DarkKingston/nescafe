<?php

namespace Drupal\ln_adimo\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Utility\LinkGeneratorInterface;

class LnAdimoUpdateFieldForm extends FormBase {

  /**
   * @var Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $link_generator;

  /**
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   */
  public function __construct(MigrationPluginManagerInterface $migration, EntityFieldManagerInterface $entity_field_manager, LinkGeneratorInterface $linkGenerator) {
    $this->migration = $migration;
    $this->entityFieldManager = $entity_field_manager;
    $this->link_generator = $linkGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.migration'), $container->get('entity_field.manager'), $container->get('link_generator'));
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'ln_update_adimo_field_form';
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $link_options = [
      'attributes' => [
        'target' => '_blank',
      ],
    ];

    $csv_example_link_url = Url::fromUri('internal:/modules/contrib/ln_adimo/migrations/examples/example_by_nid.csv');
    $csv_example_link_url->setOptions($link_options);
    $csv_example_link = $this->link_generator->generate(t('Download a CSV file example'), $csv_example_link_url);

    $form['file_upload'] = [
      '#title'             => $this->t('Upload File'),
      '#type'              => 'managed_file',
      '#description'       => $this->t('Upload CSV file for importing ADIMO ID with respective nodes. Get a sample CSV file from below link<p>' . $csv_example_link . '</p>'),
      '#upload_location'   => 'public://adimo/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required'          => TRUE,
    ];
    $fieldsArray = $this->entityFieldManager->getFieldMapByFieldType('integrationParams');
    $options = [];
    foreach ($fieldsArray as $entity_type_id => $entity_type) {
      foreach ($entity_type as $field_name => $field) {
        $options[$entity_type_id]["{$entity_type_id}:{$field_name}"] = $field_name;
      }

    }
    $form['field_name'] = [
      '#type'        => 'select',
      '#title'       => $this->t('Adimo Field'),
      '#options'     => $options,
      '#description' => $this->t('Choose an Adimo widget field in the dropdown to import data into from the CSV file.'),
      '#required'    => TRUE,
    ];

    $file = file_get_contents(drupal_get_path('module', 'ln_adimo') . '/integrations.json', FILE_USE_INCLUDE_PATH);
    $json = json_decode($file);

    $options = [];
    foreach ($json->integrations as $integration) {
      array_push($options, $integration->key);
    }

    $form['widget_type'] = [
      '#type'        => 'select',
      '#title'       => $this->t('Adimo Widget Style'),
      '#description' => $this->t('Select the widget style to attach to the products in your CSV upload file. If you require different widget styles, please upload separate CSVs for each widget style.'),
      '#options'     => $options,
      '#required'    => TRUE,
    ];

    $form['button_html'] = [
      '#type'        => 'textarea',
      '#title'       => $this->t('Custom Button HTML'),
      '#description' => $this->t('Enter the button label or Button HTML required. E.g., @tags', ['@tags' => ' Buy Now or <div class="mg-btn dark btn-buynow">Buy Now</div>']),
      '#rows'        => '3',
      '#cols'        => '30',
      '#resizable'   => FALSE,
      '#maxlength'   => 1000,
    ];

    $form['custom_css'] = [
      '#type'        => 'textarea',
      '#title'       => $this->t('Adimo Widget Custom CSS'),
      '#description' => $this->t('Add the custom CSS for the button design required. E.g. button {color: red}'),
      '#rows'        => '3',
      '#cols'        => '30',
      '#resizable'   => FALSE,
      '#maxlength'   => 1000,
    ];

    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Import'),
    ];

    $form['#attached']['library'][] = 'ln_adimo/adimo-general';
    return $form;
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Form\FormStateInterface
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_upload = $form_state->getValue('file_upload');
    $field_name_value = $form_state->getValue('field_name');
    $extra_param['widget_type'] = $form_state->getValue('widget_type');
    $extra_param['button_html'] = $form_state->getValue('button_html');
    $extra_param['custom_css'] = $form_state->getValue('custom_css');
    [$entity_type, $field_name] = explode(':', $field_name_value);
    $file = File::load($file_upload[0]);

    $migrationPlugin = $this->migration->createInstance('ln_adimo_migration');
    if ($file instanceof File && $migrationPlugin instanceof Migration) {
      $this->importAdimoField($file, $field_name, $entity_type, $extra_param, $migrationPlugin);
      $this->messenger()
        ->addStatus($this->t('Import completed successfully'), FALSE);
      return $form_state->setRedirectUrl(new Url('ln_adimo.migrate.admin.form'));
    }
    $file->delete();
  }

  /**
   * @param \Drupal\file\Entity\File $file
   * @param $field_name
   * @param $entity_type
   * @param $extra_param
   * @param \Drupal\migrate\Plugin\Migration $migrationPlugin
   *
   * @throws \Drupal\migrate\MigrateException
   */
  private function importAdimoField(File $file, $field_name, $entity_type, $extra_param, Migration $migrationPlugin) {
    $source = $migrationPlugin->getSourceConfiguration();
    $process = $migrationPlugin->getProcess();
    $source['path'] = $file->getFileUri();
    $source['entity_type'] = $entity_type;
    $source['field_name'] = $field_name;
    $source['widget_type'] = $extra_param['widget_type'];
    $source['button_html'] = $extra_param['button_html'];
    $source['custom_css'] = $extra_param['custom_css'];
    $migrationPlugin->set('source', $source);
    $migrationPlugin->set('process', $process);
    $migrationPlugin->getIdMap()->prepareUpdate();
    $messages = new MigrateMessage();
    $executable = new MigrateExecutable($migrationPlugin, $messages);
    $executable->import();
    $migrationPlugin->setStatus(MigrationInterface::STATUS_IDLE);
  }

}

