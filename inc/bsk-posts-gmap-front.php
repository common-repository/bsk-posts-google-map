<?php

class BSKPostsGmapFront{
	
	var $_bsk_posts_gmap_plugin_version = '';
	var $_bsk_posts_gmap_zoom_level_option_name = '';
	var $_bsk_posts_gmap_post_type_option_name = '';
	var $_bsk_posts_gmap_post_meta_option_name = '';
	
	public function __construct( $arg ) {
		global $wpdb;
		
		$this->_bsk_posts_gmap_plugin_version = $arg['plugin_version'];
		$this->_bsk_posts_gmap_zoom_level_option_name = $arg['map_zoom_level_opt_name'];
		$this->_bsk_posts_gmap_post_type_option_name = $arg['map_zoom_posttype_opt_name'];
		$this->_bsk_posts_gmap_post_meta_option_name = $arg['post_meta_opt_name'];
		
		add_action('wp_enqueue_scripts', array($this, 'bsk_posts_gmap_css_and_scripts'));
		add_shortcode('bsk-posts-gmap', array($this, 'bsk_posts_gmap_show_posttype_all'));
	}
	
	
	function bsk_posts_gmap_css_and_scripts(){
		//add datapicker for administrator options meta box
		$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
		$pluginfolder = str_replace('/inc', '', $pluginfolder);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'bsk-posts-gmap-google-api', 'http://maps.google.com/maps/api/js?sensor=true', array('jquery') );
		wp_enqueue_script( 'bsk-posts-gmap-gmaps', $pluginfolder . '/js/gmaps.js', array('jquery'), '0.3.3' );
		
		wp_enqueue_style( 'bsk-posts-gmap', $pluginfolder . '/css/bsk-posts-gmap.css', array(), $this->_bsk_posts_gmap_plugin_version );
	}
	
	function bsk_posts_gmap_show_posttype_all( $atts ){
		
		$attr = shortcode_atts( array('posttype' => ''), $atts );
		$posttype = trim($attr['posttype']);
		if(!$posttype){
			//get current post type
			global $post;
			$posttype = $post->post_type;
			if($posttype == 'attachment' || $posttype == 'revision' || $posttype == 'nav_menu_item'){
				return;
			}
		}
					
		$search_arg = array('numberposts'     => 999999,
							'post_type'		  => $posttype,
							'post_status'     => 'publish' );
		$results = get_posts( $search_arg );
		if(!$results || count($results) < 1){
			return '<p>No resuts found.<p>';
		}
		
		$markers = array();
		$default_center_lat = '';
		$default_center_lng = '';
		foreach($results as $post){
			$latitude = '';
			$longitude = '';
			$post_meta = get_post_meta($post->ID, $this->_bsk_posts_gmap_post_meta_option_name, true);
			if( $post_meta && is_array($post_meta) && count($post_meta) > 0 ){
				$latitude_longitude = $post_meta['latitude_longitude'];
				$latitude_longitude_array = explode(',', $latitude_longitude);
				if( is_array($latitude_longitude_array) && count($latitude_longitude_array) > 1 ){
					$latitude = trim($latitude_longitude_array[0]);
					$longitude = trim($latitude_longitude_array[1]);
				}
			}
			if( !$latitude || !$longitude ){
				continue;
			}
			$markers[ $post->ID ] = array('lat' => $latitude, 
									      'lng' => $longitude, 
									      'title' => $post->post_title, 
									      'url' => get_permalink( $post->ID ));
			$default_center_lat = $latitude;
			$default_center_lng = $longitude;
		}
		if(count($markers) < 1){
			return '';
		}
		
		
		$str = '
										
		<div id="bsk_posts_gmap_'.$posttype.'" class="bsk_posts_gmap_results">
        </div>
        <script type="text/javascript">
			var bsk_posts_gmap_results;
			bsk_posts_gmap_results = new GMaps({
				el: \'#bsk_posts_gmap_'.$posttype.'\',
				lat: '.$default_center_lat.',
				lng: '.$default_center_lng.',
				zoom: '.get_option( $this->_bsk_posts_gmap_zoom_level_option_name, 5).',
				mapType: "TERRAIN"
			});'."\n";
		foreach($markers as $mark){
			$str .= '
					bsk_posts_gmap_results.addMarker({
						lat: '.$mark['lat'].',
						lng: '.$mark['lng'].',
						title: \''.str_replace("'", "\'", $mark['title']).'\',
						infoWindow: {
						  content: \'<h4>'.$mark['title'].'</h4><a href="'.$mark['url'].'">more information</a>\'
						}
					});'."\n";
		}
		$str .= '</script>';
		
		return $str;
	}
}

        