/**
 * @file
 */

 (function ($, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.ln_c_entity_compare = {
    attach: function (context, settings) {
      $('[id|="comparison-tables"]',context).once('ln-entity-compare').each(function (index, wrapper) {

        var sticky_enabled = $(wrapper).find('table.sticky-enabled').length > 0;

        // Initial setup
        Drupal.ln_c_entity_compare.init(wrapper);

        $(wrapper).on('change', 'select', function(e) {
          var col = $(this).data('col');
          var pid = $(this).data('pid');
          var eid = $(this).val();
          var template_id = 'ln-c-entity-compare-p' + pid + '-e' + eid;

          if (sticky_enabled) {
            // Sync select elements across sticky headers
            $('select[data-pid="' + pid + '"][data-col="' + col + '"]').val(eid);
          }

          // Disable selected entity in all select lists
          Drupal.ln_c_entity_compare.resetDisabledSelectOptions(wrapper);

          // Update fields for selected template
          var fields_updated = Drupal.ln_c_entity_compare.updateEntityFields(template_id, col);

          if (!fields_updated) {
            // No fields where updated, this means the template doesn't exist in DOM yet
            // so we fetch entity from server by means of ajax request

            // Create ajax request
            var ajax = Drupal.ajax({
              url: '/ln-c-entity-compare/render/' + pid + '/' + eid
            });

            // Add placeholders to all fields of requested column to indicate we are loading new data
            //@see https://getbootstrap.com/docs/5.2/components/placeholders/
            $('div[id$="p' + pid + '--c' + col + '"]')
              .addClass('placeholder')
              .parent()
              .addClass('placeholder-glow');

            $.when(ajax.execute()).then(function (response) {
              // Try again, this should work now (provided eid is a valid entity)
              fields_updated = Drupal.ln_c_entity_compare.updateEntityFields(template_id, col);

              // 2DO: Should we handle errors if update was unsuccessful after ajax request?
              // if (!fields_updated) {...}
            });
          }
        });
      });
    }
  };

  Drupal.ln_c_entity_compare = {};

  /**
   * Perform initial setup for the comparison tables
   */
  Drupal.ln_c_entity_compare.init = function (wrapper) {
    // Match selected option of each select list with the rendered initial entity
    $(wrapper).find('select').each(function(index, elem){
      var index1 = index + 1;
      // Entities are rendered sequentially. Selected entity matches the index of the column
      $(elem).find('option:nth-child(' + index1 + ')').attr('selected', 'selected');
    });

    Drupal.ln_c_entity_compare.resetDisabledSelectOptions(wrapper);
  };

  /**
   * Helper method to toggle disabled attribute in select list options.
   * 
   * The main goal is to disallow selecting for comparison an entity which is already
   * being displayed in some other column.
   */
  Drupal.ln_c_entity_compare.resetDisabledSelectOptions = function (wrapper) {
    var select_lists = $(wrapper).find('select');

    // Reset currently disabled options
    $(select_lists).find('option[disabled]').removeAttr('disabled');

    // Disable current selection
    $(select_lists).each(function(index, elem){
      var selected_value = $(elem).val();
      $(select_lists).find('option[value="' + selected_value + '"]').attr('disabled', 'disabled');
    });
  };
  
  /**
   * Helper method to update rendered entity field in specified column
   * @param {string} template_id - Value of ID attribute of the <template> object containing the fields to update
   * @param {string} col - Number of column that should be updated
   * @returns {boolean} - TRUE if update action took place. FALSE otherwise.
   */
  Drupal.ln_c_entity_compare.updateEntityFields = function (template_id, col) {
    // Grab template by ID
    var template = $('#' + template_id);
    if (template.length == 0) {
      // Template not available, quit early
      return false;
    }
    // The jQuery .children selector doesn't return values when executed on <template> element
    // so we access the property directly
    var template_fields = template[0].content.children;

    // Replace fields of selected column with selected entity
    $.each(template_fields, function(index, node) {
      // Clone whole field wrapper
      var cloned_field = node.cloneNode(true);

      // Class attribute is the slug for the ID of the field to replace
      var field_class = $(cloned_field).attr('class');

      // Generate ID attribute for target field
      // @see LnCEntityCompareBundle::wrapFieldValue
      var target_field_id = field_class + '--c' + col;

      // Cloned attribute should have same ID as node to be replaced
      $(cloned_field).removeAttr('class').attr('id', target_field_id);
      $('#' +  target_field_id).replaceWith(cloned_field);
    });

    return true;
  };

})(jQuery, Drupal, drupalSettings);
