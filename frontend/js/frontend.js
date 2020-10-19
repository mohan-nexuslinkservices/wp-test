jQuery(document).ready(function($) {
    //do jQuery stuff when DOM is ready

    //vehicle type dropdown event
    var select_vehicle_type = '';
    $('#vb_vehicle_type').on('change',function(){
        select_vehicle_type = $(this).val();
        if( select_vehicle_type != ''){
            load_selected_vehicle_details(select_vehicle_type);
        }
    });

    //vehicle name dropdown event
    $('#vb_vehicle_name').on('change',function(){
        select_vehicle_name = $(this).val();
        if( select_vehicle_name != ''){
            load_selected_vehicle_price(select_vehicle_name);
        }
    });

    //booking form submited
    
    $('#submit_booking_form').on('click',function(){
        var postdata = jQuery("#vehicle_booking_form input, #vehicle_booking_form select").serialize();
        console.log(postdata);

        //ajax to save bookings
        $.ajax({
            url: wpvb_ajax_object.ajaxurl,
            data: {
                'action': 'wpvb_booking_save',
                'postdata' : postdata,
                'nonce' : wpvb_ajax_object.nonce,
                'dataType': 'JSON'
            },
            beforeSend: function() { },
            success:function(data) {
                debugger;
                // This outputs the result of the ajax request
                data = JSON.parse(data);
                //if success
                if( data.status == 'success'){
                    jQuery('#vehicle_booking_form').hide();
                    jQuery('#vehicle_booking_form_output').show().html(data.msg);
                }else{

                }
            },
            error: function(errorThrown){
                console.log(errorThrown);
            },
            complete: function() { }
        });  

        //reset form
        jQuery('#vehicle_booking_form')[0].reset();
        return false;
    });

});

function load_selected_vehicle_details(vehile_type=''){
    if( vehile_type == ''){ return; }

    //make ajax request and append to dropdown
    jQuery.ajax({
        url: wpvb_ajax_object.ajaxurl,
        data: {
            'action': 'get_vehicle_by_vehicle_type',
            'vehicle_type_id' : vehile_type,
            'nonce' : wpvb_ajax_object.nonce
        },
        success:function(vehicles) {
            //debugger;
            // This outputs the result of the ajax request
            var vehicles_json = JSON.parse( vehicles );
            
            jQuery('select#vb_vehicle_name').find('option').remove().end().append('<option value="whatever">--select vehicle--</option>').val('');

            //jQuery("#vb_vehicle_name").prepend("<option value='' selected='selected'>--select vehicle--</option>");
            
            jQuery.each(vehicles_json, function(index, value) {
                console.log(value.ID);
                jQuery('#vb_vehicle_name').append(jQuery('<option>').text(value.post_title).attr('value', value.ID));
            });
            
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });  
}

function load_selected_vehicle_price(vehicle_id=''){
    if( vehicle_id == ''){ return; }

    //make ajax request and append to dropdown
    jQuery.ajax({
        url: wpvb_ajax_object.ajaxurl,
        data: {
            'action': 'get_vehicle_price_by_vehicle_id',
            'vehicle_id' : vehicle_id,
            'nonce' : wpvb_ajax_object.nonce
        },
        success:function(price) {
            console.log(price);
            if( price != ''){
                jQuery('#vehicle_price_ajax_info').hide();
                jQuery('#vehicle_price_ajax_price').show().html(price);
                jQuery('#vb_vehicle_price').val(price);
            }else{
                jQuery('#vehicle_price_ajax_info').show();
                jQuery('#vehicle_price_ajax_price').hide().html('');
                jQuery('#vb_vehicle_price').val('');
            }
            
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });  
}