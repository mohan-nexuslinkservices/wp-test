<?php
/**
 * Plugin Name: WP Vehicle Book
 * Plugin URI: https://nexuslinkservices.com
 * Description: This plugin allow to create and book feature for WordPress.
 * Author: NexusLinkServices
 * Version: 1.0.0
 * Author URI: https://nexuslinkservices.com
 *
 * Text Domain: wp-vehicle-book
 * Domain Path: /languages/
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

define( 'WPVB_VERSION', '1.0.1' );
define( 'WPVB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Main WPCP Class
 */
class WP_Vehicle_Book {
    function __construct()
	{
        /**
         * Vehicle Post
         */
		//register vehicle post
		add_action( 'init', array( $this,'wpvb_create_vehicle_post') );
		//register cateogries taxonomy for vehicle posts
        add_action( 'init', array( $this,'wpvb_create_vehicle_post_taxonomy') );
        //add class for wpvb body tag
        add_filter( 'body_class', array( $this,'wpvb_add_bodyclass'), 10, 3 );
        //add dynamic taxonomy name in post class in body tag
        add_filter( 'post_class', array( $this,'wpcp_add_taxonomy_postclass'), 10, 3 );
        // add starting price per day
        add_action( 'add_meta_boxes', array( $this,'add_price_custom_field_vehicle_post') );
        //saving starting price per day field
        add_action( 'save_post', array( $this,'save_price_custom_field_vehicle_post') );

        /**
         * Form
         */
        //frontend shortcode
        add_shortcode('vehicle_booking_form',array( $this, 'wpvb_show_vehicle_booking_form' ));
        //frontend style and script
        add_action( 'wp_enqueue_scripts', array( $this, 'wpvb_vehicle_booking_form_style_script' ) );
        //get vehicle name from vehicle type
        add_action( 'wp_ajax_get_vehicle_by_vehicle_type', array( $this, 'get_vehicle_by_vehicle_type_cb' ) );
        add_action( 'wp_ajax_nopriv_get_vehicle_by_vehicle_type', array( $this, 'get_vehicle_by_vehicle_type_cb' ) );

        //get vehicle name from vehicle type
        add_action( 'wp_ajax_get_vehicle_price_by_vehicle_id', array( $this, 'get_vehicle_price_by_vehicle_id_cb' ) );
        add_action( 'wp_ajax_nopriv_get_vehicle_price_by_vehicle_id', array( $this, 'get_vehicle_price_by_vehicle_id_cb' ) );

        add_action( 'wp_ajax_wpvb_booking_save', array($this, 'wpvb_booking_save_cb') );
        add_action( 'wp_ajax_nopriv_wpvb_booking_save', array($this, 'wpvb_booking_save_cb') );
        
        /**
         * Bookings
         */
        //register booking post
        add_action( 'init', array( $this,'wpvb_create_vehicle_booking_post') );
        $this->register_vehicle_booking_metaboxes();


    }
    
    /* ----------------------------------------------------- */
	/* Vehicle Custom Post Type */
	/* ----------------------------------------------------- */
	public static function wpvb_create_vehicle_post()
	{
		$labels_vehicles = array(
	        'name'                  => __( 'Vehicles', 'wp-vehicle-book' ),
			'singular_name'         => __( 'Vehicle', 'wp-vehicle-book' ),
			'all_items'             => __( 'View Vehicles', 'wp-vehicle-book' ),
			'menu_name'             => _x( 'Vehicles', 'Admin menu name', 'wp-vehicle-book' ),
			'add_new'               => __( 'Add Vehicle', 'wp-vehicle-book' ),
			'add_new_item'          => __( 'Add new vehicle', 'wp-vehicle-book' ),
			'edit'                  => __( 'Edit vehicle', 'wp-vehicle-book' ),
			'edit_item'             => __( 'Edit vehicle', 'wp-vehicle-book' ),
			'new_item'              => __( 'New vehicle', 'wp-vehicle-book' ),
			'view_item'             => __( 'View vehicle', 'wp-vehicle-book' ),
			'view_items'            => __( 'View vehicles', 'wp-vehicle-book' ),
			'search_items'          => __( 'Search vehicles', 'wp-vehicle-book' ),
			'not_found'             => __( 'No vehicles found', 'wp-vehicle-book' ),
			'not_found_in_trash'    => __( 'No vehicles found in trash', 'wp-vehicle-book' ),
			'parent'                => __( 'Parent vehicle', 'wp-vehicle-book' ),
			'featured_image'        => __( 'Vehicle image', 'wp-vehicle-book' ),
			'set_featured_image'    => __( 'Set vehicle image', 'wp-vehicle-book' ),
			'remove_featured_image' => __( 'Remove vehicle image', 'wp-vehicle-book' ),
			'use_featured_image'    => __( 'Use as vehicle image', 'wp-vehicle-book' ),
			'insert_into_item'      => __( 'Insert into vehicle', 'wp-vehicle-book' ),
			'uploaded_to_this_item' => __( 'Uploaded to this vehicle', 'wp-vehicle-book' ),
			'filter_items_list'     => __( 'Filter vehicles', 'wp-vehicle-book' ),
			'items_list_navigation' => __( 'Vehicles navigation', 'wp-vehicle-book' ),
			'items_list'            => __( 'Vehicles list', 'wp-vehicle-book' ),
	    );
	    $supports   = array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'publicize', 'wpcom-markdown' );

	    $args = array(
	    	'labels'				=> $labels_vehicles,
	    	'description'         => __( 'This is where you can add new vehicles to your site.', 'wp-vehicle-book' ),
	    	'public' => true,
	    	'show_ui' => true,
	        'capability_type'     => 'post',//'capability_type' => 'post'
	        'publicly_queryable'  => true,
	        'exclude_from_search' => false,
			'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
			'rewrite' 			  => array( 'slug' => 'vehicles'),
			'query_var'           => true,
			'has_archive'         => true,
			'supports'            => $supports,
	        //'menu_position' => 24,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-car',
			'show_in_nav_menus' => false,
			'can_export' => true,
			//'map_meta_cap'        => true, //this is for to show woocommerce vehicles
			'show_in_rest'        => true
	    );
	    register_post_type( 'vehicle', $args );
    }
    
    /* ----------------------------------------------------- */
	/* Filter Taxonomy */
	/* ----------------------------------------------------- */
	public static function wpvb_create_vehicle_post_taxonomy()
	{

		$taxonomy_labels = array(
	        'name'              => __( 'Vehicle Types', 'wp-vehicle-book' ),
			'singular_name'     => __( 'Vehicle type', 'wp-vehicle-book' ),
			'menu_name'         => _x( 'Vehicle Types', 'Admin menu name', 'wp-vehicle-book' ),
			'search_items'      => __( 'Search vehicle types', 'wp-vehicle-book' ),
			'all_items'         => __( 'All vehicle types', 'wp-vehicle-book' ),
			'parent_item'       => __( 'Parent vehicle types', 'wp-vehicle-book' ),
			'parent_item_colon' => __( 'Parent vehicle type:', 'wp-vehicle-book' ),
			'edit_item'         => __( 'Edit vehicle type', 'wp-vehicle-book' ),
			'update_item'       => __( 'Update vehicle type', 'wp-vehicle-book' ),
			'add_new_item'      => __( 'Add new vehicle type', 'wp-vehicle-book' ),
			'new_item_name'     => __( 'New vehicle type', 'wp-vehicle-book' ),
			'not_found'         => __( 'No vehicle type found', 'wp-vehicle-book' ),
	    );
	    $args = array(
	        'labels' => $taxonomy_labels,
	        'hierarchical' => true,
	        'show_ui' => true,
	        'show_in_nav_menus' => true,
	        //'show_admin_column' => true, //since wp 3.5
	        'query_var' => true,
	        'rewrite' => false,
            'public' => true,
            'show_in_rest' =>true //to support in gutenberg 
	    );
        register_taxonomy( 'vehicle_type', array('vehicle'), $args );
        
        flush_rewrite_rules();

    }

    public static function wpvb_add_bodyclass( $classes ) {
		$classes[] = 'wp-vehicle-booking';
		return $classes;
    }
    
    /*
	 * Adds terms from a custom taxonomy to post_class
	 */
	public static function wpcp_add_taxonomy_postclass( $classes, $class, $ID )
	{
		$taxonomy = 'vehicle_type';
	    $terms = get_the_terms( (int) $ID, $taxonomy );
	    if( !empty( $terms ) ) {
	        foreach( (array) $terms as $order => $term ) {
	            if( !in_array( $term->slug, $classes ) ) {
	                $classes[] = $term->slug;
	            }
	        }
	    }
	    return $classes;
    }
    

    /**
     * Price custom field start
     */
    /**
     * Starting price custom field for vehicle book
     */
    public function add_price_custom_field_vehicle_post() {
    	add_meta_box(
    		'starting_price_vehicle', // $id
    		'Starting price per day', // $title
            array( $this, 'show_price_custom_field_vehicle_post' ), // $callback
    		'vehicle', // $screen
    		'normal', // $context
    		'high' // $priority
    	);
    }
    
    /**
     * Display starting price for vehicle
     */
    public function show_price_custom_field_vehicle_post() {
        global $post;
        $starting_price = get_post_meta( $post->ID, 'starting_price_vehicle', true ); 
        ?>
    	<input type="hidden" name="vehicle_booking_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
        
        <!-- All fields will go here -->
        <p>
            <!-- <label for="starting_price_vehicle">Input Text</label><br> -->
            <input type="number" name="starting_price_vehicle" id="starting_price_vehicle" class="regular-text" value="<?php echo $starting_price; ?>">
        </p>        
        <?php 
    }

    /**
     * Save custom field value starting price
     */
    public function save_price_custom_field_vehicle_post( $post_id ) {
    	// verify nonce
    	if ( !wp_verify_nonce( $_POST['vehicle_booking_nonce'], basename(__FILE__) ) ) {
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

    	$old = get_post_meta( $post_id, 'starting_price_vehicle', true );
    	$new = $_POST['starting_price_vehicle'];

    	if ( $new && $new !== $old ) {
    		update_post_meta( $post_id, 'starting_price_vehicle', $new );
    	} elseif ( '' === $new && $old ) {
    		delete_post_meta( $post_id, 'starting_price_vehicle', $old );
    	}
    }
    /**
     * Price custom field end
     */

     /**
      * Form part start
      */
    public function wpvb_show_vehicle_booking_form( $atts ) {
        require_once('frontend/frontend-form.php');
        wp_enqueue_style( 'wpvb-frontend-style' );
        wp_enqueue_script( 'wpvb-frontend-script' );

        // if ( isset( $_POST['submit_booking_form'] ) ) {
        //     $post = array(
        //         'post_content' => $_POST['content'], 
        //         'post_title'   => $_POST['title']
        //     );
        //     //$id = wp_insert_post( $post, $wp_error );
        // }
        ?> 
        <!-- <form method = "post">
            <input type="text" name="title">
            <input type="text" name="content">
            <input type="submit" name="submit_booking_form">
        </form> -->
        <?php
    }

    public static function wpvb_vehicle_booking_form_style_script() {
        $test = plugins_url( '/frontend/css/frontend.css', __FILE__ );
			wp_register_style( 'wpvb-frontend-style', plugins_url( '/frontend/css/frontend.css', __FILE__ ), array(), '1.0.0', 'all' );

			// Enqueue javascript on the frontend.
			// The wp_localize_script allows us to output the ajax_url path for our script to use.
			wp_register_script( 'wpvb-frontend-script', plugins_url( '/frontend/js/frontend.js', __FILE__ ), array('jquery'), '1.0', true );
            wp_localize_script( 'wpvb-frontend-script', 'wpvb_ajax_object', 
                array( 
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce('booking-form-nonce'),
                )
            );
			
			
    }
    
    /**
     * Ajax callback on vehicle type selection
     */
    function get_vehicle_by_vehicle_type_cb() {
        $nonce = $_REQUEST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'booking-form-nonce' ) ) {
            die( 'Nonce value cannot be verified.' );
        }
        // The $_REQUEST contains all the data sent via ajax
        if ( isset($_REQUEST) ) {
            
            $vehicle_type_id = $_REQUEST['vehicle_type_id'];
            // Let's take the data that was sent and do something with it

            $posts_array = get_posts(
                array(
                    'posts_per_page' => -1,
                    'post_type' => 'vehicle',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'vehicle_type',
                            'field' => 'term_id',
                            'terms' => $vehicle_type_id,
                        )
                    )
                )
            );

            echo json_encode($posts_array);
        }
        // Always die in functions echoing ajax content
        die();
    }

    /**
     * Ajax callback on vehicle type selection
     */
    function get_vehicle_price_by_vehicle_id_cb() {
        global $wpdb;
        $nonce = $_REQUEST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'booking-form-nonce' ) ) {
            die( 'Nonce value cannot be verified.' );
        }
        // The $_REQUEST contains all the data sent via ajax
        if ( isset($_REQUEST) ) {
            
            $vehicle_id = (int)$_REQUEST['vehicle_id'];
            // Let's take the data that was sent and do something with it
            
            $price = get_post_meta( $vehicle_id, 'starting_price_vehicle', true );

            if( $price ){
                echo $price;
            }
        }
        // Always die in functions echoing ajax content
        die();
    }
    
    function wpvb_booking_save_cb() {	
        global $wpdb;
        //$currency = "INR"; 
        $data = array();
        $result = array(
            'status' => 'failed',
            'data' => '',
            'msg' => ''
        );
        $nonce = $_REQUEST['nonce'];

        $nonce_error = false;
        if ( ! wp_verify_nonce( $nonce, 'booking-form-nonce' ) ) {
            //die( 'Nonce value cannot be verified.' );
            $statusMsg = "Don't change verification value.";
            $nonce_error = true;
        }
    
        // The $_REQUEST contains all the data sent via ajax
        if ( isset($_REQUEST) && $nonce_error == false) {
            $params = array();
            parse_str($_REQUEST['postdata'], $params);
            $vb_first_name  = $params['vb_first_name']; 
            $vb_last_name = $params['vb_last_name']; 
            $vb_email = $params['vb_email']; 
            $vb_phone = $params['vb_phone']; 
            $vb_vehicle_type_id = $params['vb_vehicle_type']; 
            $vb_vehicle_id = $params['vb_vehicle_name'];
            $vb_vehicle_price = $params['vb_vehicle_price'];
            if(!empty($vb_first_name) || !empty($vb_last_name) || !empty($vb_email) || !empty($vb_phone) ){			
                
                $booking_post = array(
                    'post_title' => wp_strip_all_tags( $vb_first_name . $vb_last_name ),
                    //'post_content' => 'This is my post.',
                    'post_status' => 'publish',
                    'post_type' => 'vehicle_booking',
                );
                // Insert the post into the database
                $post_id = wp_insert_post( $booking_post );
                
                if( $post_id ){
                    global $post;
                    $data = array();

                    if( $vb_email ){
                        update_post_meta( $post_id, 'vb_email', $vb_email );
                        $data['email'] = $vb_email;
                    }
                    if( $vb_phone ){
                        update_post_meta( $post_id, 'vb_phone', $vb_phone ); 
                        $data['phone'] = $vb_phone;
                    }
                    if( $vb_vehicle_type_id ){
                        
                        $vb_vehicle_type = get_term( $vb_vehicle_type_id )->name;
                        update_post_meta( $post_id, 'vb_vehicle_type', $vb_vehicle_type_id );
                        $data['vehicle_type'] = $vb_vehicle_type;
                    }
                    if( $vb_vehicle_id ){ 
                        update_post_meta( $post_id, 'vb_vehicle_name', $vb_vehicle_id );
                        $vb_vehicle_name = get_the_title( $vb_vehicle_id );
                        $data['vehicle_name'] = $vb_vehicle_name;
                    }
                    if( $vb_vehicle_price ){
                        update_post_meta( $post_id, 'vb_vehicle_price', $vb_vehicle_price );
                        $data['vehicle_price'] = $vb_vehicle_price;
                    }
                    $booking_status = 'pending';
                    update_post_meta( $post_id, 'vb_booking_status', $booking_status );
                    
                    //send mail
                    $mail_sent = $this->mail_to_admin($booking_status,$data);

                    $ordStatus = 'success'; 
                    $statusMsg = 'Booking done!'; 
                    $result['status'] = 'success';
                    $result['data'] = $data;

                }else{ 
                    $statusMsg = "Booking not doen. Contact administrator!"; 
                } 

            }else{ 
                $statusMsg = "Error on form submission."; 
            }
            
            //$result['msg'] = $statusMsg;
            
            // Now we'll return it to the javascript function
            // Anything outputted will be returned in the response
            //echo json_encode($result);
            
            // If you're debugging, it might be useful to see what was sent in the $_REQUEST
            // print_r($_REQUEST);
        
        }
        $result['msg'] = $statusMsg;
        echo json_encode($result);
        
        // Always die in functions echoing ajax content
        die();
    }

    /* ----------------------------------------------------- */
	/* Vehicle Booking Custom Post Type */
	/* ----------------------------------------------------- */
	public static function wpvb_create_vehicle_booking_post()
	{
		$labels_vehicles = array(
	        'name'                  => __( 'Vehicles Booking', 'wp-vehicle-book' ),
			'singular_name'         => __( 'Vehicle Booking', 'wp-vehicle-book' ),
			'all_items'             => __( 'View Booking', 'wp-vehicle-book' ),
			'menu_name'             => _x( 'Vehicles Bookings', 'Admin menu name', 'wp-vehicle-book' ),
			'add_new'               => __( 'Add Booking', 'wp-vehicle-book' ),
			'add_new_item'          => __( 'Add new booking', 'wp-vehicle-book' ),
			'edit'                  => __( 'Edit booking', 'wp-vehicle-book' ),
			'edit_item'             => __( 'Edit booking', 'wp-vehicle-book' ),
			'new_item'              => __( 'New booking', 'wp-vehicle-book' ),
			'view_item'             => __( 'View booking', 'wp-vehicle-book' ),
			'view_items'            => __( 'View booking', 'wp-vehicle-book' ),
			'search_items'          => __( 'Search booking', 'wp-vehicle-book' ),
			'not_found'             => __( 'No bookings found', 'wp-vehicle-book' ),
			'not_found_in_trash'    => __( 'No bookings found in trash', 'wp-vehicle-book' ),
			'parent'                => __( 'Parent booking', 'wp-vehicle-book' ),
			'featured_image'        => __( 'Booking image', 'wp-vehicle-book' ),
			'set_featured_image'    => __( 'Set booking image', 'wp-vehicle-book' ),
			'remove_featured_image' => __( 'Remove booking image', 'wp-vehicle-book' ),
			'use_featured_image'    => __( 'Use as booking image', 'wp-vehicle-book' ),
			'insert_into_item'      => __( 'Insert into booking', 'wp-vehicle-book' ),
			'uploaded_to_this_item' => __( 'Uploaded to this booking', 'wp-vehicle-book' ),
			'filter_items_list'     => __( 'Filter bookings', 'wp-vehicle-book' ),
			'items_list_navigation' => __( 'Booking navigation', 'wp-vehicle-book' ),
			'items_list'            => __( 'Bookings list', 'wp-vehicle-book' ),
	    );
	    $supports   = array( 'title', 'custom-fields', 'publicize', 'wpcom-markdown' );

	    $args = array(
	    	'labels'				=> $labels_vehicles,
	    	'description'         => __( 'This is where you can add new bookings to your site.', 'wp-vehicle-book' ),
	    	'public' => true,
	    	'show_ui' => true,
	        'capability_type'     => 'post',//'capability_type' => 'post'
	        'publicly_queryable'  => true,
	        'exclude_from_search' => false,
			'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
			'rewrite' 			  => array( 'slug' => 'vehicle_bookins'),
			'query_var'           => true,
			'has_archive'         => true,
			'supports'            => $supports,
	        //'menu_position' => 24,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-editor-ol',
			'show_in_nav_menus' => false,
			'can_export' => true,
			//'map_meta_cap'        => true, //this is for to show woocommerce vehicles
			'show_in_rest'        => true
	    );
	    register_post_type( 'vehicle_booking', $args );
    }

    public function register_vehicle_booking_metaboxes(){
        require_once('backend/register_vehicle_booking_metaboxes.php');
    }

    /**
     * Mail functions
     */
    //mail to admin
    public function mail_to_admin($booking_status='', $data = array()){
        $admin_email = get_option( 'admin_email' );
        if( $admin_email && $booking_status == 'pending' ){
            $subject = __('You got an vehicle booking enquiry','') . '-' . get_bloginfo('name');
            //array to string
            $body  = '';
            foreach ($data as $key => $value) {
                $body .= '<b>'.ucwords($key).' : </b>'.$value.'<br/>';
            }            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $admin_mail_sent = wp_mail( $admin_email, $subject, $body, $headers );
        }
    }

    //mail to customer
    public function mail_to_customer($booking_status='', $customer_email='', $customer_name=''){
        if( customer_email != ''){
            $subject = __('Booking status updated','') . '-' . ucwords($booking_status);
            $body  = '';
            $body .= 'Dear ' .$customer_name.'<br/>';
            $body .= 'Your booking status update to ' .$booking_status.'<br/>';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $customer_mail_sent = wp_mail( $customer_email, $subject, $body, $headers );
        }        
    }
}
new WP_Vehicle_Book();