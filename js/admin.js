jQuery(function ($) {
    $("#smsalert_setting\\[smsalert_woocommerce_sms_from\\]").focusout(function() {
        var sender_id = $("#smsalert_setting\\[smsalert_woocommerce_sms_from\\]").val().trim();
        if($.isNumeric(sender_id) && sender_id.length > 20) {
            alert('Message From is too long, max 20 digits for numeric SMS sender.');
        } else if(!$.isNumeric(sender_id) && sender_id.length > 11) {
            alert('Message From is too long, max 11 characters for alphanumeric SMS sender.');
        }   
        $("#smsalert_setting\\[smsalert_woocommerce_sms_from\\]").val(sender_id);        
    });
    
    $("#admin_setting\\[smsalert_woocommerce_admin_sms_recipients\\]").focusout(function() {
        var admin_mobile_no = $("#admin_setting\\[smsalert_woocommerce_admin_sms_recipients\\]").val().trim();
        var admin_mobile_no_array = new Array();
        var counter;
        if(admin_mobile_no != '') {
            admin_mobile_no_array = admin_mobile_no.split(",");
            for(counter = 0; counter < admin_mobile_no_array.length; counter++) {
                admin_mobile_no_array[counter] = admin_mobile_no_array[counter].trim();
                if(!$.isNumeric(admin_mobile_no_array[counter])) {
                    alert('Invalid mobile number, must be numeric.');
                    break;
                } else if(admin_mobile_no_array[counter].substring(0, 1) == '0') {
                    alert('Mobile number must include country code, e.g. 60123456789, 6545214889.');
                    break;
                }
            }
        } 
    });    
});