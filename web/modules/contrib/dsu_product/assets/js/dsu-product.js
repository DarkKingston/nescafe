/**
 * @file
 *   Javascript for the event tracking on Buy Now button from advanced datalayer.
 */
(function ($) {
  // Check Product page is exist on any content type.

  $(document).ready(function () {
    if ($(".dsu-product-component-list").length > 0) {
      // Call on submit drupal ratings & reviews form.
      $('.dsu-product-component-list button').click(function () {
        // Get content category of the node.
        var content_category = $(this).parents('.dsu-product-component-list').data('eventcategory');
        // Get content brand of the node.
        var content_brand = $(this).parents('.dsu-product-component-list').data('eventbrand');
        // Get content id of the node.
        var content_id = $(this).parents('.dsu-product-component-list').data('eventid');
        // Get title of the node.
        var content_title = $(this).parents('.dsu-product-component-list').data('eventlabel');
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "buyNowButtonClick",
          eventCategory: "Buy Now",
          eventAction: "Buy Now Click",
          eventLabel: content_title,
          productBrand: content_brand,
          productCategory: content_category,
          productName: content_title,
          productId: content_id.toString(),
          buyNowClicked: 1
        });
      });
    }
  });

})(jQuery);
