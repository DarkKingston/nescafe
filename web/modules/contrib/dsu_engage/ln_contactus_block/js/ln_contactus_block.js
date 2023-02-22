/**
 * @file
 *   Javascript for adding the markup for required fields.
 */

(function ($, Drupal) {
  "use strict";

  $('.form-required').append('<span class="red-marked">*</span>');
  // change event for select.
  $(document).on('change', function() {
     $('.form-required span').remove();
     $('.form-required').append('<span class="red-marked">*</span>');	
  });

  /* Not required as it is already managed by webform validation */
  /*var requiredFieldContext, emailError;
  var email = $('#edit-email-me');
  var chkinput = $("#edit-privacy-agreement");
  
  $('#edit-actions-submit').on('click', function(e) {
    var requiredField = document.getElementById('webform-submission-contact-us-add-form').getElementsByClassName('red-marked');
    var requiredFieldLength = requiredField.length;
   
    for (var i = 0; i < requiredFieldLength; i++) {
      requiredFieldContext = $( requiredField[i] ).parent().siblings("div").children();
      var error = '<label for="'+requiredFieldContext.attr("id")+'" class="error">'+requiredFieldContext.data('webform-required-error')+'</label>'
      if( requiredFieldContext.attr("id") && requiredFieldContext.val() == '' ){
        if( !requiredFieldContext.siblings('label').length ){
          requiredFieldContext.addClass('error').after(error); 
        }
      }else{
        if( requiredFieldContext.siblings('label').length ){
          requiredFieldContext.siblings('label').css("display", "none");
        }
      }
    }
    if (!$(email).val()) {

     if( !$(email).siblings('.error').length ){
         emailError = '<label for="'+$(email).attr("id")+'-error" class="error">'+$(email).data('webform-required-error')+'</label>';
        $(email).addClass('error').after(emailError).find('label').css("display", "inline-block"); 
      }else{
        $(email).find('label').text($(email).data('webform-required-error')); 
        $(email).addClass('error').find('label').remove().css("display", "inline-block");
      }
    }else{
      if(validateEmail($(email).val()) == !1 ){
        if( $(email).siblings('.error').length ){
            $(email).find('label').text($(email).data('msg-email')); 
            $(email).addClass('error').find('label').css("display", "inline-block");
          }else{
            emailError = '<label for="'+$(email).attr("id")+'-error" class="error">'+$(email).data('msg-email')+'</label>';
            $(email).addClass('error').after(emailError).find('label').css("display", "inline-block");
          }
      }else{
        $(email).removeClass('error').find('label').css("display", "none");
      }
    }

    if ($(chkinput).prop("checked") != !0) {
      if( !$(chkinput).siblings('label.error').length ){
        var error = '<label style="display:block" for="'+$(chkinput).attr("id")+'-error" class="error">'+$(chkinput).data('msg-required')+'</label>';
        $(chkinput).addClass('error').parent().append(error); 
      } else {
        $(chkinput).siblings('label.error').html($(chkinput).data('msg-required')).css("display", "block")
      }
    }

    $('input, select, textarea, checkbox').prop('required',false);
    if( $('input, select, textarea, checkbox').hasClass('error') ){
      $('input.error, select.error, textarea.error, checkbox.error')[0].focus();
      return false;
    }
  });

  $('.red-marked').parent().siblings("div").children().on('keyup keypress blur change', function() {
    if( $(this).siblings('label').length ){
      if( $(this).val() ){
        $(this).removeClass('error').siblings('label').css("display", "none");
      }else{
        $(this).addClass('error').siblings('label').css("display", "inline-block");
      }
   }
  });

  // $(email).on('keyup', function() {
  //   var emailVal = $(this).val();
  //   if( emailVal != '' ){
  //     if( !$(this).siblings('.error').length ){
  //         emailError = '<label for="'+$(this).attr("id")+'-error" class="error">'+$(this).data('msg-email')+'</label>';
  //         $(this).addClass('error').after(emailError).find('label').css("display", "inline-block");
  //       }else{
  //         if(!validateEmail(emailVal)){
  //           $(this).siblings('.error').text($(this).data('msg-email')); 
  //           $(this).addClass('error').siblings('.error').css("display", "inline-block");
  //         }else{
  //           $(this).removeClass('error').siblings('.error').css("display", "none");
  //         }
  //       }
  //     }else{
  //       if( !$(this).siblings('.error').length ){
  //         emailError = '<label for="'+$(this).attr("id")+'-error" class="error">'+$(this).data('webform-required-error')+'</label>';
  //         $(this).addClass('error').after(emailError).find('label').css("display", "inline-block");
  //       }else{
  //         $(this).siblings('.error').text($(this).data('webform-required-error'));
  //         $(this).siblings('.error').css("display", "inline-block").addClass('error');
  //       }
  //     }
  // });

  $(chkinput).change( function() {
    if( $(this).length ){
      if($(this).prop("checked") == true){
        $(this).removeClass('error').siblings('label.error').css("display", "none");
      }else{
        $(this).addClass('error').siblings('label.error').css("display", "block");
      }
    }
  });

  function validateEmail(email){
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);
    if (!0 !== regex) 
      return false;
    return true;
  }*/

})(jQuery, Drupal);
