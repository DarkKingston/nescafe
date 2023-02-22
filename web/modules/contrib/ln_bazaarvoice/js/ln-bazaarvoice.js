/**
 * @file
 *   Javascript for the adding header script of bazaarvoice and product iframe.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  //Load bazaarvoice script
  if(drupalSettings.ln_bazaarvoice && drupalSettings.ln_bazaarvoice.js_url){
    (function(d, s, id, url) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = url;
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'bazaarvoice', drupalSettings.ln_bazaarvoice.js_url));


    if(drupalSettings.ln_bazaarvoice.products){
      window.bvDCC = {
        catalogData: {
          catalogProducts: JSON.stringify(Object.values(drupalSettings.ln_bazaarvoice.products))
        }
      };
    }


    window.bvCallback = function (BV) {
      if(drupalSettings.ln_bazaarvoice.products){
        BV.pixel.trackEvent("CatalogUpdate", {
          type: 'Product',
          catalogProducts: window.bvDCC.catalogData.catalogProducts
        });
      }
    };
  }

  Drupal.behaviors.ln_bazaarvoice = {
    attach: function attach(context, settings) {
      $('.ln-bazaarvoice').once('ln-bazaarvoice-events').each(function(){
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "ratingReviewEvent",
          eventCategory: "Ratings & Reviews",
          eventAction: "Detail View",
          eventLabel: drupalSettings.ln_datalayer?.data?.content_name,
          reviewContent: drupalSettings.ln_datalayer?.data?.content_name
        });
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
