<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly
$vehicle_types = get_terms( 'vehicle_type', array(
    'hide_empty' => false,
) );
?>
<form method="post" name="booking_form" id="vehicle_booking_form">
    <label> <?php _e( 'First Name:', 'wp-vehicle-book' ); ?>
        <input type="text" name="vb_first_name">
    </label>
    
    <label> <?php _e( 'Last Name:', 'wp-vehicle-book' ); ?>
        <input type="text" name="vb_last_name">
    </label>

    <label> <?php _e( 'Email:', 'wp-vehicle-book' ); ?>
        <input type="text" name="vb_email">
    </label>
    <label> <?php _e( 'Phone:', 'wp-vehicle-book' ); ?>
        <input type="text" name="vb_phone">
    </label>
    
    <label> <?php _e( 'Vehicle Type:', 'wp-vehicle-book' ); ?>
        <select name="vb_vehicle_type" id="vb_vehicle_type">
            <option value=""><?php _e( 'select vehicle type', 'wp-vehicle-book' ); ?></option>
            <?php
            foreach ($vehicle_types as $key => $value) {
                echo '<option value="'.$value->term_id.'">'.$value->name.'</option>';
            }
            ?>
        </select>
    </label>

    <label> <?php _e( 'Vehicle Name:', 'wp-vehicle-book' ); ?>
        <select name="vb_vehicle_name" id="vb_vehicle_name">
            <option value=""><?php _e( '--select vehicle--', 'wp-vehicle-book' ); ?></option>
        </select>
    </label>

    <label> <?php _e( 'Vehicle Price:', 'wp-vehicle-book' ); ?><br>
        <strong id="vehicle_price_ajax_info"><?php _e( 'Will be displayed after vehicle name selected.', 'wp-vehicle-book' ); ?></strong>
        <strong id="vehicle_price_ajax_price"></strong>
        <input type="hidden" name="vb_vehicle_price" id="vb_vehicle_price">
    </label>

    <label> <?php _e( 'Message:', 'wp-vehicle-book' ); ?>
        <textarea ame="vb_message"></textarea>
    </label>
    
    <input type="submit" name="submit_booking_form" id="submit_booking_form" value="<?php _e( 'Book Now', 'wp-vehicle-book' ); ?>">
</form>
<div id="vehicle_booking_form_output"><strong></strong></div>