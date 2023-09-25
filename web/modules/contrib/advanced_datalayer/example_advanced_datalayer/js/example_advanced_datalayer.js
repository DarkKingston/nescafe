/**
 * @file
 * Initializes some client side datalayer tags.
 */
function getCookie(cname) {
  'use strict';
  var name = cname + '=';
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) === ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) === 0) {
      return c.substring(name.length, c.length);
    }
  }
  return '';
}

if (typeof dataLayer_tags !== 'undefined' && dataLayer_tags.site_Information) {
  if (typeof dataLayer_tags.site_Information.ga_client_id !== 'undefined') {
    dataLayer_tags.site_Information.ga_client_id = getCookie('_ga');
  }
}
