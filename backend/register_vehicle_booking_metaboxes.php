<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly
add_action( 'add_meta_boxes', function(){
    
    //email
    add_meta_box(
        'vb_email', // $id
        __('Email','wp-vehicle-book'), // $title
        'vb_show_email', // $callback
        'vehicle_booking', // $screen
        'normal', // $context
        'high' // $priority
    );

    //email
    add_meta_box(
        'vb_phone', // $id
        __('Phone','wp-vehicle-book'), // $title
        'vb_show_phone', // $callback
        'vehicle_booking', // $screen
        'normal', // $context
        'high' // $priority
    );

    //vehicle_name
    add_meta_box(
        'vb_vehicle_name', // $id
        __('Vehicle Name','wp-vehicle-book'), // $title
        'vb_show_vehicle_name', // $callback
        'vehicle_booking', // $screen
        'normal', // $context
        'high' // $priority
    );

    //vehicle_price
    add_meta_box(
        'vb_vehicle_price', // $id
        __('Vehicle Price','wp-vehicle-book'), // $title
        'vb_show_vehicle_price', // $callback
        'vehicle_booking', // $screen
        'normal', // $context
        'high' // $priority
    );

    //vehicle_type
    add_meta_box(
        'vb_vehicle_type', // $id
        __('Vehicle Type','wp-vehicle-book'), // $title
        'vb_show_vehicle_type', // $callback
        'vehicle_booking', // $screen
        'normal', // $context
        'high' // $priority
    );

    //vehicle_name
    add_meta_box(
        'vb_booking_status', // $id
        __('Booking Status','wp-vehicle-book'), // $title
        'vb_show_booking_status', // $callback
        'vehicle_booking', // $screen
        'side', // $context
        'high' // $priority
    );
    
});

function vb_show_email(){
    global $post;
    $vb_email = get_post_meta( $post->ID, 'vb_email', true ); 
    ?>
    	<!-- All fields will go here -->
        <p>
            <label for="vb_email"><?php echo $vb_email; ?></label><br>
            <!-- <input type="text" name="vb_email" id="vb_email" class="regular-text" value="<?php //echo $vb_email; ?>"> -->
        </p>        
    <?php 
}

function vb_show_phone(){
    global $post;
    $vb_phone = get_post_meta( $post->ID, 'vb_phone', true ); 
    ?>
    	<!-- All fields will go here -->
        <p>
            <label for="vb_phone"><?php echo $vb_phone; ?></label><br>
        </p>        
    <?php 
}

function vb_show_vehicle_name(){
    global $post;
    $vb_vehicle_id = get_post_meta( $post->ID, 'vb_vehicle_name', true ); 
    $vb_vehicle_name = get_the_title( $vb_vehicle_id );
    ?>
    	<!-- All fields will go here -->
        <p>
            <label for="vb_vehicle_name"><?php echo $vb_vehicle_name; ?></label><br>
        </p>        
    <?php 
}

function vb_show_vehicle_price(){
    global $post;
    $vb_vehicle_price = get_post_meta( $post->ID, 'vb_vehicle_price', true ); 
    ?>
    	<!-- All fields will go here -->
        <p>
            <label for="vb_vehicle_price"><?php echo $vb_vehicle_price; ?></label><br>
        </p>        
    <?php 
}

function vb_show_vehicle_type(){
    global $post;
    $vb_vehicle_type_id = get_post_meta( $post->ID, 'vb_vehicle_type', true ); 
    $vb_vehicle_type = get_term( $vb_vehicle_type_id )->name;
    ?>
    	<!-- All fields will go here -->
        <p>
            <label for="vb_vehicle_type"><?php echo $vb_vehicle_type; ?></label><br>
        </p>        
    <?php 
}

function vb_show_booking_status(){
    global $post;
    $vb_booking_status = get_post_meta( $post->ID, 'vb_booking_status', true );
    $all_booking_status = array( 'pending','approved','reject','complete');
    ?>
        <input type="hidden" name="vb_booking_status_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
    	<!-- All fields will go here -->
        <p>
            <!-- <label for="vb_booking_status"><?php //echo ucwords($vb_booking_status); ?></label><br> -->
            <select name="vb_booking_status" id="vb_booking_status">
            <?php foreach ($all_booking_status as $value) {
                $selected = '';
                if( $value == $vb_booking_status){ $selected = 'selected'; }
              echo '<option '.$selected.' value='.$value.'>'.ucwords($value).'</option>';    
            } ?>
            </select>
        </p>

    <?php 
}

/**
 * When status updated
 */
add_action( 'save_post_vehicle_booking', function( $post_id ){
   
    // verify nonce
    if ( !wp_verify_nonce( $_POST['vb_booking_status_nonce'], basename(__FILE__) ) ) {
        return $post_id;
    }
    // check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // check permissions
    if ( 'page' === $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    $old = get_post_meta( $post_id, 'vb_booking_status', true );
    $new = $_POST['vb_booking_status'];

    if ( $new && $new !== $old ) {
        update_post_meta( $post_id, 'vb_booking_status', $new );
    } elseif ( '' === $new && $old ) {
        delete_post_meta( $post_id, 'vb_booking_status', $old );
    }
    //get customer email for send mail
    $customer_email = get_post_meta( $post_id, 'vb_email', true );
    $customer_name = get_the_title( $post_id );
    $customer_email_sent = $this->mail_to_customer($new, $customer_email, $customer_name);
    
});