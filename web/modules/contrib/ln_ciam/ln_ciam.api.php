<?php

/**
 * @file
 * Documentation for Lightnest CIAM module APIs.
 */

/**
 * Alter the query parameter in ciam query hook.
 *
 * This hook runs before allowing registration. So Agency can use
 * this hook to add or update query attributes from with the field name.
 *
 * Sometimes agency need to do an extra validation of some field that is not
 * unique in Gigya by design.
 *
 * For some campaigns they need to be sure that consumers are not duplicating
 * data and the Country ID Number is used only once.
 *
 * So agency need to use alter hook and have to
 * send query string with updated values mapping.
 *
 * @param string $query
 *   - query: Raw query.
 * @param array $payload
 *   - payload: Array of all input data from user.
 * @param string $fieldName
 *   - fieldName: Custom field name on registartion screen-set.
 *
 * @ingroup ln_ciam_api
 */
function hook_ln_ciam_query_alter(&$query, array $payload, string &$applicationInternalId, string &$fieldName) {
  // Field value filled by the user in the form.
  $applicationInternalId = $payload["data"]["params"]["data"]["custom_field_value"];

  // Update query with the new custom_field.
  $query = "select count(*) from emailAccounts where custom_field = '" . $applicationInternalId . "'";

  // Input field name.
  $fieldName = "custom_field";
}
