<?php

namespace Drupal\ln_price_spider\Form;

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

/**
 *
 */
class LnPriceSpiderUpdateFieldForm extends FormBase {

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
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\ln_price_spider\Form\LnPriceSpiderUpdateFieldForm|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.migration'),
      $container->get('entity_field.manager'),
      $container->get('link_generator')
    );
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'ln_price_spider_field_form';
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

    $csv_example_link_url = Url::fromUri('internal:/modules/contrib/ln_price_spider/migrations/pricespider_example/price_spider_by_nid.csv');
    $csv_example_link_url->setOptions($link_options);
    $csv_example_link = $this->link_generator->generate(t('Download a CSV file example'), $csv_example_link_url);

    $form['file_upload'] = [
      '#title'             => $this->t('Upload File'),
      '#type'              => 'managed_file',
      '#upload_location'   => 'public://pricespider/',
      '#description'       => $this->t('Upload CSV file for importing PriceSpider ID with respective nodes. Get a sample CSV file from below link<p>' . $csv_example_link . '</p>'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required'          => TRUE,
    ];
    $fieldsArray = $this->entityFieldManager->getFieldMapByFieldType('field_price_spider');
    $options = [];
    foreach ($fieldsArray as $entity_type_id => $entity_type) {
      foreach ($entity_type as $field_name => $field) {
        $options[$entity_type_id]["{$entity_type_id}:{$field_name}"] = $field_name;
      }

    }
    $form['field_name'] = [
      '#type'     => 'select',
      '#title'    => $this->t('Price Spider Field'),
      '#options'  => $options,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Import'),
    ];

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
    [$entity_type, $field_name] = explode(':', $field_name_value);
    $file = File::load($file_upload[0]);

    $migrationPlugin = $this->migration->createInstance('ln_price_spider_migration');
    if ($file instanceof File && $migrationPlugin instanceof Migration) {
      $this->importPriceSpiderField($file, $field_name, $entity_type, $migrationPlugin);
      $this->messenger()
        ->addStatus($this->t('Import completed successfully'), FALSE);
      return $form_state->setRedirectUrl(new Url('ln_price_spider.migrate.admin.form'));
    }
    $file->delete();
  }

  /**
   * @param \Drupal\file\Entity\File $file
   * @param $field_name
   * @param $entity_type
   * @param \Drupal\migrate\Plugin\Migration $migrationPlugin
   *
   * @throws \Drupal\migrate\MigrateException
   */
  private function importPriceSpiderField(File $file, $field_name, $entity_type, Migration $migrationPlugin) {
    $source = $migrationPlugin->getSourceConfiguration();
    $process = $migrationPlugin->getProcess();
    $source['path'] = $file->getFileUri();
    $source['entity_type'] = $entity_type;
    $process[$field_name] = $process['price_spider_id'];
    $source['field_name'] = $field_name;
    $migrationPlugin->set('source', $source);
    $migrationPlugin->set('process', $process);
    $migrationPlugin->getIdMap()->prepareUpdate();
    $messages = new MigrateMessage();
    $executable = new MigrateExecutable(
      $migrationPlugin,
      $messages
    );
    $executable->import();
    $migrationPlugin->setStatus(MigrationInterface::STATUS_IDLE);
  }

}
