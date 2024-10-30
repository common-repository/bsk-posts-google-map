<?php

/*
Plugin Name: BSK Posts Google Map
Plugin URI: http://www.bannersky.com/html/bsk-posts-map-marker.html
Description: The plugin help you to show posts/pages as markers on Google map. The custom post type is supported as well. With widget option to let you show map with a marker on widget area.
Version: 1.2
Author: Banner Sky
Author URI: http://www.bannersky.com

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

*/



class BSKPostsGmap{
	var $_bsk_posts_gmap_plugin_version = '1.2';
	
	var $_bsk_posts_gmap_zoom_level_option_name = '_bsk_posts_gmap_zoom_level_';
	var $_bsk_posts_gmap_post_type_option_name = '_bsk_posts_gmap_posttypes_';
	var $_bsk_posts_gmap_post_meta_option_name = '_bsk_posts_gmap_post_meta_';
	var $_bsk_posts_gmap_demo_post_ID_option_name = '_bsk_posts_gmap_demo_post_ID_';
	
	var $_bsk_posts_gmap_front_OBJECT = NULL;
	var $_bsk_posts_gmap_plugin_settings_OBJECT = NULL;
	
	public function __construct() {
		global $wpdb;
		
		add_action('init', array($this, 'bsk_posts_gmap_post_action'));
		if( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array($this, 'bsk_posts_gmap_css_scripts') );
			add_action( 'load-post.php', array($this, 'bsk_posts_gmap_meta_box_setup') );
			add_action( 'load-post-new.php', array($this, 'bsk_posts_gmap_meta_box_setup') );
			add_action( 'save_post', array($this, 'bsk_posts_gmap_meta_box_save_all') );
		}else{
			add_action( 'wp_enqueue_scripts', array($this, 'bsk_posts_gmap_css_scripts') );
		}
		
		//hooks
		register_activation_hook( __FILE__, array($this, 'bsk_posts_gmap_activate') );
		register_deactivation_hook(  __FILE__, array(&$this, 'bsk_posts_gmap_deactivate') );
		register_uninstall_hook( __FILE__, 'BSKPostsGmap::bsk_posts_gmap_uninstall' );
		
		require_once( 'inc/bsk-posts-gmap-front.php' );
		require_once( 'inc/bsk-posts-gmap-widget.php' );
		require_once( 'inc/bsk-posts-gmap-setting.php' );
		
		$init_arg = array();
		$init_arg['plugin_version'] = $this->_bsk_posts_gmap_plugin_version;
		$init_arg['map_zoom_level_opt_name'] = $this->_bsk_posts_gmap_zoom_level_option_name;
		$init_arg['map_zoom_posttype_opt_name'] = $this->_bsk_posts_gmap_post_type_option_name;
		$init_arg['post_meta_opt_name'] = $this->_bsk_posts_gmap_post_meta_option_name;
		$init_arg['demo_post_ID_opt_name'] = $this->_bsk_posts_gmap_demo_post_ID_option_name;
		
		$this->_bsk_posts_gmap_front_OBJECT = new BSKPostsGmapFront( $init_arg );
		$this->_bsk_posts_gmap_plugin_settings_OBJECT = new BSKPostsGmapSettingPage( $init_arg );
		
		add_action( 'widgets_init', create_function( '', 'register_widget( "BSKPostsGmapWidget" );' ) );
	}
	
	function bsk_posts_gmap_activate() {
		$current_options =	get_option('_bsk_posts_gmap_posttypes_');
		if( !$current_options ){
			update_option('_bsk_posts_gmap_posttypes_', array('post', 'page'));
		}
		$current_options =	get_option('_bsk_posts_gmap_zoom_level_');
		if( !$current_options ){
			update_option('_bsk_posts_gmap_zoom_level_', 5);
		}
		//create demo posts
		$this->bsk_posts_gmap_insert_demo_posts();
		
		// Clear the permalinks
		flush_rewrite_rules();
	}
	
	
	function bsk_posts_gmap_deactivate(){
		// Clear the permalinks
		flush_rewrite_rules();
	}

    function bsk_posts_gmap_uninstall(){
		delete_option('_bsk_posts_gmap_posttypes_');
		delete_option('_bsk_posts_gmap_zoom_level_');
		
		return;
	}

	function bsk_posts_gmap_post_action(){
		if( isset( $_POST['bsk_posts_gmap_action'] ) && strlen($_POST['bsk_posts_gmap_action']) >0 ) {
			do_action( 'bsk_posts_gmap_' . $_POST['bsk_posts_gmap_action'], $_POST );
		}
	}
	
	function bsk_posts_gmap_admin_css_scripts(){
		//add datapicker for administrator options meta box
		$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

		wp_enqueue_script( 'jquery' );
		
	}
	
	function bsk_posts_gmap_css_scripts(){
		$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
		
		wp_enqueue_script( 'jquery' );
		if( is_admin() ){
			//wp_enqueue_script( 'bsk-posts-gmap-admin', $pluginfolder . '/js/bsk-posts-gmap-admin.js', array('jquery'), $this->_bsk_posts_gmap_plugin_version );
			wp_enqueue_style( 'bsk-posts-gmap-admin', $pluginfolder . '/css/bsk-posts-gmap-admin.css', array(), $this->_bsk_posts_gmap_plugin_version );
		}else{
			//wp_enqueue_script( 'bsk-posts-gmap', $pluginfolder . '/js/bsk-posts-gmap.js', array('jquery'), $this->_bsk_posts_gmap_plugin_version );
			wp_enqueue_style( 'bsk-posts-gmap', $pluginfolder . '/css/bsk-posts-gmap.css', array(), $this->_bsk_posts_gmap_plugin_version );
		}
	}
	
	
	
	function bsk_posts_gmap_meta_box_setup(){
		add_action( 'add_meta_boxes', array( $this, 'bsk_posts_gmap_add_post_meta_box' ) );
	}
	
	function bsk_posts_gmap_add_post_meta_box() {
		$saved_option = get_option('_bsk_posts_gmap_posttypes_');
		if(!$saved_option || count($saved_option) < 1){
			return;
		}
		
		foreach($saved_option as $post_type){
			add_meta_box(
				'bsk-posts-gmap-meta-box-'.$post_type,   // Unique ID
				esc_html__( 'Position data - BSK Posts Google Map'),      // Title
				array( $this, 'bsk_posts_gmap_extras_meta_box'),      // Callback function
				$post_type,             		  // Admin page (or post type)
				'normal',                				// Context
				'high'                   		// Priority
			);
		}
	}
	
	function bsk_posts_gmap_extras_meta_box( $object, $box ){
		$bsk_post_meta = get_post_meta($object->ID, $this->_bsk_posts_gmap_post_meta_option_name, true);
		$bsk_post_gmap_latitude = '';
		$bsk_post_gmap_longitude = '';
		if( $bsk_post_meta && is_array($bsk_post_meta) && count($bsk_post_meta) > 0 ){
			$bsk_post_gmap_latitude_longitude = $bsk_post_meta['latitude_longitude'];
		}
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'bsk_posts_gmap_extras_meta_box_nonce' );
		?>
        <div class="bsk-posts-google-map-metabox">
            <ul>
	            <li>
                	<label>Latitude &amp; Longitude</label>
                    <input type="text" name="bsk_post_gmap_latitude_longitude" value="<?php echo $bsk_post_gmap_latitude_longitude; ?>" style="width:350px;" />
                </li>
            </ul>
            <input type="hidden" name="bsk_posts_gmap_extra_save" value="true" />
        </div>
        <?php
	}
	
	function bsk_posts_gmap_meta_box_save_all( $post_id ){
		if ( !(isset($_POST['bsk_posts_gmap_extra_save']) && $_POST['bsk_posts_gmap_extra_save'] == 'true') ){ 
			return;
		}
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['bsk_posts_gmap_extras_meta_box_nonce'], plugin_basename( __FILE__ ) ) ){
			return;
		}
		
		$bsk_post_gmap_latitude_longitude = trim($_POST['bsk_post_gmap_latitude_longitude']);
		
		update_post_meta( $post_id, $this->_bsk_posts_gmap_post_meta_option_name, array('latitude_longitude' => $bsk_post_gmap_latitude_longitude) );
	}
	
	function bsk_posts_gmap_insert_demo_posts(){
		$demo_post_1 = array(
						  'post_title'    => 'BSK Posts Goole Map Marker 1',
						  'post_content'  => 'Hello BannerSky!',
						  'post_status'   => 'publish'
						);
		$post_ID_of_demo_1 = wp_insert_post( $demo_post_1 );
		update_post_meta( $post_ID_of_demo_1, $this->_bsk_posts_gmap_post_meta_option_name, array('latitude_longitude' => '39.740986,-101.011963') );
		
		$demo_post_2 = array(
						  'post_title'    => 'BSK Posts Goole Map Marker 2',
						  'post_content'  => 'Hello BannerSky!',
						  'post_status'   => 'publish'
						);
		$post_ID_of_demo_2 = wp_insert_post( $demo_post_2 );
		update_post_meta( $post_ID_of_demo_2, $this->_bsk_posts_gmap_post_meta_option_name, array('latitude_longitude' => '38.255436,-102.813721') );
		
		$demo_post_3 = array(
						  'post_title'    => 'BSK Posts Goole Map Demo',
						  'post_content'  => 'This is demo post of BSK Posts Google Map!'."\n".'[bsk-posts-gmap posttype="post"]',
						  'post_status'   => 'publish'
						);
		$post_ID_of_demo_3 = wp_insert_post( $demo_post_3 );
		
		update_option( '_bsk_posts_gmap_demo_post_ID_', $post_ID_of_demo_3 );
	}
}

$bsk_posts_gmap_sample = new BSKPostsGmap();        
        