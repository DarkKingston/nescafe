<?php

/**
 * @file
 * Documentation for Lightnest Alkemics module APIs.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Alter or add additional products in alkemics importer hook.
 *
 * This hook runs after the alkemics product importer start. So Agency can ue
 * this hook to add additional product attributes from alkemics in ds_product.
 *
 * A popular use case for this hook is mapping additional information like
 * new fields created in content type and some more information needs to
 * import from Alkemics API.
 *
 * So agency need to use alter hook and have to
 * send node object with updated values mapping. example in the alter Agency
 * need set values $node->set('field_dsu_sku', $data['alkemics_gtim']);
 *
 * @param \Drupal\node\Entity\Node $node
 * @param array $data
 *   An associative array with context information:
 *   - data:         Array of all Alkemics data response.
 *
 * @ingroup ln_alkemics_api
 */
function hook_ln_alkemics_import_alter(Node &$node, array $data) {
  // Update only single additional node fields data.
  $node->set('field_node_product_info', $data['alkemics_product_info']);

  // Update and add paragraph in a node.
  $paragraph_data = Paragraph::create([
    'type'                          => 'paragraph_name',
    'field_paragraph_field'         => $data['duration'],
    'field_paragraph_attributes_id' => $data['id'],
  ]);
  try {
    $paragraph_data->save();
  } catch (Exception $e) {
    Drupal::logger('Alkemics Alter')
      ->notice('Cannot save Paragraph: ' . $e->getMessage());

  }

  // Get paragraph revision id.
  $paragraph_data = [
    'target_id'          => $paragraph_data->id(),
    'target_revision_id' => $paragraph_data->getRevisionId(),
  ];

  // Save paragraph in existing products.
  $node->set('field_node_paragraph_data', $paragraph_data);

  // Remove old field and update new data in exiting fields.
  unset($node->field_name);

}

/**
 * Alter hook to alter the header options before sending the get request.
 *
 * @param array $options
 *   An array of header options
 *
 *     // Endpoints header.
 *     $options = [
 *     'method'      => 'GET',
 *     'http_errors' => TRUE,
 *     'headers'     => [
 *     'Content-Type'  => 'application/json',
 *     'Authorization' => 'Bearer ' . $token['access_token'],
 *       ],
 *     ];.
 *
 * @param array $token
 *   An array of tokens comes from alkemics
 *
 * @ingroup ln_alkemics_api
 */
function hook_ln_alkemics_header_options_alter(array &$options, $token) {
}
