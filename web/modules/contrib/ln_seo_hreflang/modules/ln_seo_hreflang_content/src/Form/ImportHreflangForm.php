<?php

namespace Drupal\ln_seo_hreflang_content\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\ln_seo_hreflang_content\LnHreflangContentConstants;
use League\Csv\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class ImportHreflangForm extends FormBase {

  /**
   * The base directory to store our batched files.
   *
   * @var string
   */
  protected $baseDirectory = "private://ln_seo_hreflang_content/batched_migrations/";

  /**
   * The migration class.
   *
   * @var use Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * ImportHreflangForm constructor.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration
   * @param \Drupal\Core\Messenger\MessengerInterface              $messenger
   * @param \Drupal\Core\Extension\ModuleHandlerInterface          $module_handler
   * @param \Drupal\Core\File\FileSystemInterface                  $fileSystem
   */
  public function __construct(MigrationPluginManagerInterface $migration,
    MessengerInterface $messenger, ModuleHandlerInterface $module_handler,
    FileSystemInterface $fileSystem) {
    $this->migration = $migration;
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $fileSystem;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.migration'),
      $container->get('messenger'),
      $container->get('module_handler'),
      $container->get('file_system')
    );
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'ln_seo_hreflang_import';
  }


  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['file_upload'] = [
      '#title' => t('Hreflangs File'),
      '#type' => 'managed_file',
      '#upload_location' => $this->baseDirectory,
      '#upload_validators' => [
        'file_validate_extensions' => ['csv']
      ],
      '#description' => $this->t('Allowed file extensions: csv'),
      '#required' => TRUE
    ];

    $modulePath = $this->moduleHandler->getModule('ln_seo_hreflang_content')->getPath();
    $path = file_create_url("$modulePath/migrations/data/hreflangs.csv");
    $templateUrl = $path ? Url::fromUri($path)->toString() : '';

    $items[] = $this->t('The import file must follow this format and <a href=":xls_template">template</a>. It is important to respect the name of the header fields.', [':xls_template' => $templateUrl]);
    $items[] = $this->t('The import is based on the <strong>Path</strong> field of each hreflang, if it exists it updates it and if it does not create it new.');
    $items[] = $this->t('All fields are required.');
    $items[] = $this->t('The <strong>Path</strong> field must start with a slash, parameters are not allowed.');
    $items[] = $this->t('The <strong>Links</strong> field, you must follow this format(URL|language) and the URL must always have a protocol (http or https). In case of having multiple values, separated by comma: URL|language,...,URL|language');
    $form['info'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#type'] == 'submit' && $trigger['#name'] == 'op') {
      $file_upload = $form_state->getValue('file_upload');
      $file = isset($file_upload[0]) ? File::load($file_upload[0]) : FALSE;
      if($file instanceof File){
        $csv = fopen($file->getFileUri(), 'r');
        if (!$csv) {
          $form_state->setErrorByName('file_upload', $this->t('File ":path" could not be opened.', [':path' => $file->getFileUri()]));
        }
        $reader = Reader::createFromStream($csv);
        $reader->setHeaderOffset(LnHreflangContentConstants::CSV_READER_HEADER_OFFSET);
        $reader->setDelimiter(LnHreflangContentConstants::CSV_READER_HEADER_DELIMITER);
        $csvHeaders = $reader->getHeader();
        if(is_array($csvHeaders) &&
          $csvHeaders !== LnHreflangContentConstants::CSV_LN_HREFLANG_IMPORT_HEADERS){
          $form_state->setErrorByName('file_upload', $this->t('The file header does not meet the requirements.'));
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_upload = $form_state->getValue('file_upload');
    $file = File::load($file_upload[0]);
    $migration = $this->migration->createInstance(LnHreflangContentConstants::MIGRATION_LN_HREFLANG_IMPORT_ID);
    if($file instanceof File && $migration instanceof Migration){
      $this->importLnHreflangBatch($migration, $file->getFileUri(), LnHreflangContentConstants::LN_HREFLANG_IMPORT_BATCH_SIZE);
      // Delete the  file after its been imported.
      $file->delete();
    }

  }

  /**
   * The batched hreflang import function
   *
   * @param \Drupal\migrate\Plugin\Migration $migration
   * @param                                  $path
   * @param                                  $batch_size
   */
  private function importLnHreflangBatch(Migration $migration, $path, $batch_size){

    if (!empty($path) && !empty($batch_size)) {
      // Generate the necessary batched files.
      $batched_files = $this->batchSourceFile($path, $batch_size);

      $batch = [
        'title' => 'Importing Hreflang...',
        'operations' => [],
        'init_message' => 'Starting import...',
        'progress_message'=> 'Processed @current out of @total.',
        'error_message' => 'An error occurred during processing',
        'finished' => [$this, 'finishedCallbackBatch']
      ];
      // Add the operations batch.
      foreach ($batched_files as $index => $file) {
        $batch['operations'][] = [[$this, 'executeMigration'], [$file, $migration]];
      }
      batch_set($batch);
    }

  }
  /**
   * The batch operation function
   *
   * @param                                  $path
   * @param \Drupal\migrate\Plugin\Migration $migration
   */
  public function executeMigration($path, Migration $migration){
    $source = $migration->getSourceConfiguration();
    $source['path'] = $path;
    $migration->set('source', $source);
    $migration->getIdMap()->prepareUpdate();

    // Execute the migration
    try {
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();
    } catch (MigrateException $e) {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);
      $this->logger('ln_seo_hreflang_content')->error($e->getMessage());
      return;
    }
    $migration->setStatus(MigrationInterface::STATUS_IDLE);

    // Delete our temporary batched file after its been used.
    $this->fileSystem->delete($path);
  }

  /**
   * The batch finished function
   *
   * @param $success
   * @param $results
   * @param $operations
   */
  public function finishedCallbackBatch($success, $results, $operations){
    if ($success) {
      $message = 'The import was successful';
    }
    else {
      $message = 'Finished with an error.';
    }

    $this->messenger->addMessage($this->t($message));
  }

  /**
   * Creates batch files for a given source file.
   *
   * @param string $source_file
   *   The path to the source file.
   * @param string $batch_size
   *   The batch size to divide the files into.
   *
   * @return array
   *   An array of file paths for our batched files.
   */
  private function batchSourceFile($source_file, $batch_size) {
    // Use an iterator to read the file to preserve memory.
    $source_file_iterator = $this->getFileIterator($source_file);

    // Create a temporary, random filename for our batched files.
    $temp_file_name = 'ln_hreflang_migration_' . substr(md5(rand()), 0, 7);

    // Initialize parameters for the first batch.
    $batch_id = 0;
    $lines_read = 0;
    $files = [];
    $file_handle = $this->createBatchedFile($files, $temp_file_name, $batch_id);

    // Log that the batching process is being started.
    $this->logger('ln_seo_hreflang_content')->notice('Generating batched files for source file @source_file .', ['@source_file' => $source_file]);

    // Iterate through the lines of the original source file. An iterator is
    // used to conserve memory as these files may be quite large.
    foreach ($source_file_iterator as $line) {
      // If the number of lines read is larger than the batch size, then create
      // a new batch file.
      if ($lines_read >= $batch_size) {
        // Increment our batch ID for the new file and reset how many lines have
        // been read.
        $batch_id++;
        $lines_read = 0;

        // Close the existing file and create a new one.
        fclose($file_handle);
        $file_handle = $this->createBatchedFile($files, $temp_file_name, $batch_id);
        // Add the header to the batched file.
        $header = implode(";", LnHreflangContentConstants::CSV_LN_HREFLANG_IMPORT_HEADERS);
        fwrite($file_handle, "$header\r\n");
      }

      // Write a line to the file.
      fwrite($file_handle, $line);
      $lines_read++;
    }

    // Log that batching is finished.
    $this->logger('ln_seo_hreflang_content')->notice('Finished generating batch files: @count created.', ['@count' => count($files)]);

    return $files;
  }

  /**
   * Creates a batched file via Drupal's unmanaged files.
   *
   * @param array $files
   * @param       $file_name
   * @param       $batch_id
   *
   * @return false|resource
   */
  private function createBatchedFile(array &$files, $file_name, $batch_id) {
    // Create the directory if it does not exist.
    $this->fileSystem->prepareDirectory($this->baseDirectory, FileSystemInterface::CREATE_DIRECTORY);

    // Generate a file name for our file.
    $destination = $this->baseDirectory . $file_name . '_' . $batch_id . '.csv';

    // Create a blank file via Drupal that is not managed in the database.
    $this->fileSystem->saveData('', $destination, FileSystemInterface::EXISTS_REPLACE);

    // Get the actual path so we can use PHP to write to the file.
    $file_path = $this->fileSystem->realpath($destination);

    // Add this file to our list of batched files created so far.
    $files[] = $file_path;

    // Return the PHP file resource.
    return fopen($file_path, 'w');
  }

  /**
   * Generate an iterator for a file.
   *
   * @param string $file_path
   *   A given file path.
   *
   * @return \Generator
   *   An iterator for this file.
   */
  private function getFileIterator($file_path) {
    // Open the file.
    $file = fopen($this->fileSystem->realpath($file_path), 'r');

    // Return a line at a time.
    while (!feof($file)) {
      yield fgets($file);
    }

    // Close the file.
    fclose($file);
  }
}
