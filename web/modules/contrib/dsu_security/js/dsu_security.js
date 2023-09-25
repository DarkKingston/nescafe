(function ($, Drupal) {
    Drupal.behaviors.nppe_module_dsu_security = {
        attach: function (context, settings) {

            // Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)
            //Please see https://github.com/jquery/jquery/issues/2432
            //long term upgrade is to JQuery 3 because this is a breaking change.
            //however we should be safe if not using $.get(untrusted_url)
            //
            jQuery.ajaxPrefilter( function ( s ) {
                if ( s.crossDomain ) {
                    s.contents.script = false;

                }
            } );
        }
    }
}(jQuery, Drupal));
