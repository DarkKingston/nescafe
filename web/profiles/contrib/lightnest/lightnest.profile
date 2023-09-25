<?php

use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Yaml;

/**
 * @file
 * Enables modules and site configuration for the Lightnest profile.
 */

/**
 * Implements hook_install_tasks().
 */
function lightnest_install_tasks(&$install_state) {
  $tasks = [];
  if (empty($install_state['config_install_path'])) {
    $tasks['lightnest_module_configure_form'] = [
      'display_name' => t('Select additional components'),
      'type' => 'form',
      'function' => 'Drupal\lightnest\Installer\Form\ModuleConfigureForm',
    ];
    $tasks['lightnest_module_install'] = [
      'display_name' => t('Installing components'),
      'type' => 'batch',
    ];
    $tasks['lightnest_integrations_configure_form'] = [
      'display_name' => t('Select additional integrations'),
      'type' => 'form',
      'function' => 'Drupal\lightnest\Installer\Form\IntegrationConfigureForm',
    ];
    $tasks['lightnest_integration_install'] = [
      'display_name' => t('Installing integrations'),
      'type' => 'batch',
    ];
    $tasks['lightnest_generate_report'] = [
      'display_name' => t('Generate report'),
      'type' => 'batch',
    ];
  }
  return $tasks;
}

/**
 * Installs the lightnest components modules in a batch.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch array to execute.
 */
function lightnest_module_install(array &$install_state) {
  $modules = $install_state['lightnest_additional_modules'];
  $batch = [];
  if ($modules) {
    $operations = [];
    foreach ($modules as $module) {
      $operations[] = ['lightnest_install_module_batch',
        [[$module], $module]];
    }
    $batch = [
      'operations' => $operations,
      'title' => t('Installing additional modules'),
      'error_message' => t('The installation has encountered an error.'),
    ];
  }
  return $batch;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs batch installation of modules.
 */
function lightnest_install_module_batch($module, $module_name, &$context) {
  set_time_limit(0);
  try {
    //try to install module
    \Drupal::service('module_installer')->install($module, true);
  } catch (\Exception $e) {
    \Drupal::logger('lightnest')->error($e->getMessage());
  }
  $context['results'][] = $module;
  $context['message'] = t('Installed %module_name modules.', ['%module_name' => $module_name]);
}

/**
 * Installs the lightnest integrations in a batch.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch array to execute.
 */
function lightnest_integration_install(array &$install_state) {
  $modules = $install_state['lightnest_additional_integration'];
  $batch = [];
  if ($modules) {
    $operations = [];
    foreach ($modules as $module) {
      $operations[] = [
        'lightnest_install_integration_batch',
        [[$module], $module]];
    }
    $batch = [
      'operations' => $operations,
      'title' => t('Installing additional integrations'),
      'error_message' => t('The installation has encountered an error.'),
    ];
  }

  return $batch;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs batch installation of modules.
 */
function lightnest_install_integration_batch($module, $module_name, &$context) {
  set_time_limit(0);
  try {
    \Drupal::service('module_installer')->install($module, true);
  } catch (\Exception $e) {
    \Drupal::logger('lightnest')->error($e->getMessage());
  }
  $context['results'][] = $module;
  $context['message'] = t('Installed %module_name modules.', ['%module_name' => $module_name]);
}


/**
 * Implements callback_batch_operation().
 *
 * Generates a report content of article type.
 */
function lightnest_generate_report(&$context) {
  $module_handler = \Drupal::service('module_handler');
  if ($module_handler->moduleExists('dsu_article')) {
    lightnest_create_node();
    $context['message'] = t('The report generation has encountered an error.');
  }
}

/**
 * Function to set message after installation is finished.
 */
function set_message_finished() {
  // Added site cache clear command to avoid video embed field type missing issue.
  drupal_flush_all_caches();
  $message = t('Thanks for the installation. You can find the link to report <a href="/installation-report"> Here </a>');
  return \Drupal::messenger()->addMessage($message, 'status');
}

/**
 * Implements node create to list modules selected during profile installation.
 *
 */
function lightnest_create_node() {
  $module_list = \Drupal::service('extension.list.module')->getList();
  $body = "";
  foreach ($module_list as $module) { // goes through the list of module
    $module_name = $module->info['name']; // saves name
    $module_description = $module->info['description']; //description
    $status = $module->status; //status
    $type = $module->info['type']; //module or distribution
    $package = $module->info['package']; //package info for getting modules
    //condition checks if enabled, module, and package of lightnest and component
    if (($status == 1) && ($type == 'module') && ($package == "Lightnest Components" || $package == "Lightnest")) {
      // brings the list of modules which are installed by default
      $profile = \Drupal::service('module_handler')->getModule('lightnest');
      $default_modules = Yaml::decode(file_get_contents( "{$profile->getPath()}/config/default-modules.yml"));
      if(!in_array($module_name, $default_modules)){
        $project_link = "";
        if(isset($module->info['configure'])) {
          $configure = $module->info['configure']; //configure link route is coming add logic to get url from route
          $url = Url::fromRoute($configure);
          $url->toString();
          $project_link = Link::fromTextAndUrl(('Configure'), $url)->toString();
          $project_link = str_replace("core/install.php/","","$project_link");
        }
        $body .="<tr><td>".$module_name."</td><td>".$module_description."</td><td>".$project_link."</td></tr>";
      }
    }
  }
  $body_table = "<table><thead><tr><td>Name</td><td>Description</td><td>Configure link</td></tr></thead><tbody>".$body."</tbody></table>";
  $node = Node::create([
    'type'=> 'dsu_article',
    'title'=> 'Installation report',
    'body'=> $body_table,
    'uid'=> 1,
    'status'=> 0,
  ]);
  $node->body->format = 'rich_text';
  $node->set('path', '/installation-report');
  $node->save();
  return $node;
}
