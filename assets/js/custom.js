window.addEventListener("load", function () {
    var input = '';
    if( document.querySelector('input[name="post_type"]') !== null && document.querySelector('input[name="post_type"]').value == 'hospital' ){
        var input = document.querySelector(".acf-postbox div[data-name='mobile_number'] input");
    }
    if( document.getElementById('patient-form') !== null ){
        var input = document.querySelector("#patient-form input[name='mobile_number']");
        jQuery('#patient-form').on("submit", function(e){
            e.preventDefault();
            var name = jQuery('input[name="name"]').val();
            var mobile_number = jQuery('input[name="mobile_number"]').val();
            var hospitalId = jQuery('select[name="hospitalId"] option:selected').val();
            var gpId = ( jQuery('select[name="gpId"]').length > 0 ) ? jQuery('select[name="gpId"] option:selected').val() : jQuery('input[name="gpId"]').val();
            var patient_id = ( jQuery('input[name="patient_id"]').length > 0 ) ? jQuery('input[name="patient_id"]').val() : '';
            console.log(gpId);
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : hmAjax.ajaxurl,
                data : {action: "add_edit_patient", patient_id: patient_id, name : name, mobile_number: mobile_number, hospitalId: hospitalId, gpId: gpId},
                success: function(response) {
                    if(response.success == true) {
                        jQuery('#patient-form')[0].reset();
                        window.location.href = jQuery('#patient-form').attr('action');
                    }
                    else {
                        jQuery('<div class="notice notice-error is-dismissible"><p>'+ response.message +'</p></div>').insertAfter('.wp-heading-inline');
                        jQuery('html, body').animate({ scrollTop: jQuery( '.wrm-active' ).offset().top }, 1000);
                    }
                }
            });
        });
    }
    if( input != '' ){
        var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

        function getIp(callback) {
            fetch('https://ipinfo.io', { headers: { 'Accept': 'application/json' }})
            .then((resp) => resp.json())
            .catch(() => {
                return {
                country: '',
                };
            })
            .then((resp) => callback(resp.country));
        }

        var iti = window.intlTelInput(input, {
            hiddenInput: "full_number",                         
            nationalMode: false,
            formatOnDisplay: true,           
            separateDialCode: true,
            autoHideDialCode: false, 
            autoPlaceholder: "aggressive" ,
            placeholderNumberType: "MOBILE",
            preferredCountries: ['in'],           
            geoIpLookup: getIp,
            initialCountry: "in",
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.15/js/utils.js",
        });


        input.addEventListener('keyup', formatIntlTelInput);
        input.addEventListener('change', formatIntlTelInput);

        function formatIntlTelInput() {
            if (typeof intlTelInputUtils !== 'undefined') { // utils are lazy loaded, so must check
                var currentText = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                if (typeof currentText === 'string') { // sometimes the currentText is an object :)
                    iti.setNumber(currentText); // will autoformat because of formatOnDisplay=true
                }
            }
        }

        input.addEventListener('keyup', function () {     
            reset();
            if (input.value.trim()) {
                if (iti.isValidNumber()) {
                    jQuery(input).addClass('form-control is-valid');
                    jQuery('input[type="submit"]').removeClass('button-disabled');
                } else {                  
                    jQuery(input).addClass('form-control is-invalid');
                    jQuery('input[type="submit"]').addClass('button-disabled');
                    var errorCode = iti.getValidationError();
                }
            }
        });        
        input.addEventListener('change', reset);        
        input.addEventListener('keyup', reset);

        var reset = function () {
            jQuery(input).removeClass('form-control is-invalid');
            jQuery('input[type="submit"]').removeClass('button-disabled');
                   
        };
        jQuery("form").submit(function() {
            jQuery(input).val(iti.getNumber(intlTelInputUtils.numberFormat.E164));
        });
    }
});