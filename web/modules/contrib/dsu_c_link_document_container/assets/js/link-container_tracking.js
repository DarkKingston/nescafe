/**
 * @file
 *   Javascript for the adding event tracking from advanced datalayer.
 */

(function ($, Drupal, drupalSettings) {
  // Check dsu_c_link_document_container is exist on any content type.
  jQuery(".field--name-field-c-link-document-item .file-link a").click(function() {

    let domain = '';
    try{
      //relative links
      let url = $(this).attr("href");
      if(url.startsWith('/')){
        url = document.location.origin + url;
      }
      domain = new URL(url).hostname;
    }catch(e){}

    var file_extension = $(this).attr("type").split("/");
    var file_name_url = $(this).attr("href");
    var file_name = file_name_url.split("/");

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event' : 'file_download',
      'event_name' : 'file_download',
      'file_extension' : file_extension[1],
      'file_name' : file_name[file_name.length - 1],
      'link_classes' : $(this).closest("div").attr("class"),
      'link_domain' : domain,
      'link_id' : $(this).closest("div").attr("id"),
      'link_text' : $(this).attr("title"),
      'link_url' : file_name_url,
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'module_name' : drupalSettings.dsu_c_link_document_container.data.module_name,
      'module_version' : drupalSettings.dsu_c_link_document_container.data.module_version,
    });
  });

// Check dsu_c_link_document_container is exist on any content type.
  jQuery(".paragraph--type--c-link  a").click(function() {

    let domain = '';
    try{
      //relative links
      let url = $(this).attr("href");
      if(url.startsWith('/')){
        url = document.location.origin + url;
      }
      domain = new URL(url).hostname;
    }catch(e){}

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event' : 'file_download',
      'event_name' : 'file_download',
      'link_classes' : $(this).closest(".paragraph--type--c-link").attr("class"),
      'link_domain' : domain,
      'link_id' : $(this).closest(".paragraph--type--c-link").attr("id"),
      'link_text' : $(this).attr("title"),
      'link_url' : $(this).attr("href"),
      'content_id' : drupalSettings.ln_datalayer?.data?.content_id,
      'content_name' : drupalSettings.ln_datalayer?.data?.content_name,
      'module_name' : drupalSettings.dsu_c_link_document_container.data.module_name,
      'module_version' : drupalSettings.dsu_c_link_document_container.data.module_version,
    });
  });

})(jQuery, Drupal, drupalSettings);

