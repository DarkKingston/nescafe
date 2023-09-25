<?php

/**
 * @file
 * Documentation for Lightnest PDH module APIs.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Alter or add additional products in pdh importer hook.
 *
 * This hook runs after the pdh product importer start. So Agency can use
 * this hook to add additional product attributes from pdh in dsu_product.
 *
 * A popular use case for this hook is mapping additional information like
 * new fields created in content type and some more information needs to
 * import from PDH API.
 *
 * So agency needs to use alter hook and have to
 * send node object with updated values mapping. example in the alter Agency
 * need set values $node->set('field_dsu_sku', $data->GTIN);
 *
 * @param \Drupal\node\Entity\Node $node
 *   Node of the product.
 * @param object $data
 *   The product info object coming from PDH.
 *
 * @ingroup ln_pdh_api
 */
function hook_ln_pdh_import_alter(Node &$node, $data) {
  // Update only single additional node fields data.
  $node->set('field_node_product_info', $data['pdh_product_info']);

  // Update and add paragraph in a node.
  $paragraph_data = Paragraph::create([
    'type'                          => 'paragraph_name',
    'field_paragraph_field'         => $data['field'],
    'field_paragraph_attributes_id' => $data['id'],
  ]);
  try {
    $paragraph_data->save();
  }
  catch (Exception $e) {
    Drupal::logger('PDH Alter')
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
 * Alter hook to alter the header options before sending the GET request.
 *
 * @param string $endpoint
 *   The endpoint URL.
 * @param array $options
 *   The request options to pass to the endpoint as query parameters.
 *
 * @ingroup ln_pdh_api
 */
function hook_ln_pdh_request_alter($endpoint, array $options) {
}
