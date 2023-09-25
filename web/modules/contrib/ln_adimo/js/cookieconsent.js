function cokkieConsentAcceptance() {
    console.log("executing cookie check");
    let cookies = document.cookie;
    let myCookies = cookies.split("; ");

    for (let index = 0; index < myCookies.length; index++) {
        let temp = myCookies[index].split("=");
        let cookie_name = temp[0];
        let cookie_value = temp[1];

        if (cookie_name === "_evidon_suppress_notification_cookie") {
            jQuery(document).find("[data-do-not-track]").attr("data-do-not-track", true);
            break;
        }
    }
}

jQuery(document).ready(function(){
    cokkieConsentAcceptance();

    setTimeout(function() {
        jQuery("[class$='declinebutton']").each(function() {
            jQuery(this).on("click", function() {
                    //cokkieConsentAcceptance();
                    jQuery(document).find("[data-do-not-track]").attr("data-do-not-track", true);
                })
            });
    }, 2000);
});

