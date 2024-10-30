<?php
class BSKPostsGmapSettingPage{
	
	var $_bsk_posts_gmap_plugin_version = '';
	var $_bsk_posts_gmap_option_page_slug = 'bsk-posts-gmap';
	var $_bsk_posts_gmap_zoom_level_option_name = '';
	var $_bsk_posts_gmap_post_type_option_name = '';
	var $_bsk_posts_gmap_demo_post_ID_option_name = '';
	
	public function __construct( $arg ) {
		$this->_bsk_posts_gmap_plugin_version = $arg['plugin_version'];
		$this->_bsk_posts_gmap_zoom_level_option_name = $arg['map_zoom_level_opt_name'];
		$this->_bsk_posts_gmap_post_type_option_name = $arg['map_zoom_posttype_opt_name'];
		$this->_bsk_posts_gmap_demo_post_ID_option_name = $arg['demo_post_ID_opt_name'];
		
		//add option page
		add_action( 'admin_menu', array($this, 'bsk_posts_gmap_option_page') );
		
		add_action( 'bsk_posts_gmap_save_setting', array($this, 'bsk_save_setting_page') );
	}
	
	function bsk_posts_gmap_option_page(){
		add_options_page( $this->_bsk_posts_gmap_option_page_slug, 'BSK Posts Google Map', 'manage_options', $this->_bsk_posts_gmap_option_page_slug, array($this, 'show') );
	}
	
	function show() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$action = 'options.php';
		$post_types = get_post_types();
		unset($post_types['attachment']);
		unset($post_types['revision']);
		unset($post_types['nav_menu_item']);
		
		$saved_posttypes = get_option( $this->_bsk_posts_gmap_post_type_option_name );
		$saved_zoomlevel = get_option( $this->_bsk_posts_gmap_zoom_level_option_name, 5 );		
		?>
		
		<div class="wrap" style="padding-left:15px; width:80%;">
			<h2>BSK Posts Google Map</h2>
			<form action="<?php echo admin_url( 'options-general.php?page='.$this->_bsk_posts_gmap_option_page_slug ); ?>" method="POST" id="stock_ticker">
			<h3>Plugin Settings</h3>
			<h4>Supported post type</h4>
			<p>A mexta box on post edit screen will let you enter Latitude and Longitude data for every post of the type you chosen here.</p> 
			<table style="text-align:left;">
				<thead>
					<th>Post Type</th>
					<th style="padding-left:20px;">Shortcode</th>
				</thead>
				<?php foreach($post_types as $key => $post_type){ ?>
				<tr>
					<td style="width:150px;">
                    	<label>
                        	<input type="checkbox" name="bsk_posts_gmap_posttypes[]" value="<?php echo $key; ?>" <?php if(in_array($key, $saved_posttypes)){ echo 'checked="checked"'; } ?> /><?php echo $post_type; ?>
                        </label>
                    </td>
					<td style="padding-left:20px;">[bsk-posts-gmap posttype="<?php echo $key; ?>"]</td>
				</tr>
				<?php } ?>
			</table>
            <h4>Map zoom level</h4>
            <p>Maps on Google Maps have an integer "zoom level" which defines the resolution of the current view. Zoom levels between 0 (the lowest zoom level, in which the entire world can be seen on one map) to 21+ (down to individual buildings) are possible within the default roadmap maps view.</p>
            <select name="bsk_posts_gmap_zoom_level" id="bsk_posts_gmap_zoom_level_ID">
			<?php 
            for( $i = 0; $i <= 21; $i++ ){ 
                if( $saved_zoomlevel == $i ){
                    echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                }else{
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
            }
            ?>
            </select>
			<p style="margin-top: 20px"><button class="button-primary" type="submit" id="admin_stock_submit">Save Settings</button></p>
            <input type="hidden" name="bsk_posts_gmap_action" value="save_setting" />
			</form>
            <?php
			$demo_post_ID = get_option( $this->_bsk_posts_gmap_demo_post_ID_option_name, 0 );
			if( $demo_post_ID > 0 ){
				$demo_post_permalink = get_permalink( $demo_post_ID );
			?>
                <h4>Demo posts</h4>
                <p>Demo posts for BSK Posts Goolge Map have been created. View them from <a href="<?php echo $demo_post_permalink; ?>" target="_blank">here</a>.</p>
            <?php
			}
			?>
			<h3>Plugin Support Centre</h3>
			<ul>
				<li><a href="http://www.bannersky.com/html/bsk-posts-google-map.html" target="_blank">Visit the Support Centre</a> if you have a question on using this plugin</li>
			</ul>
		</div>
	<?php
	}
	
	function bsk_save_setting_page(){
		if( !isset($_POST['bsk_posts_gmap_action']) || $_POST['bsk_posts_gmap_action'] != 'save_setting' ){
			return;
		}
		update_option( $this->_bsk_posts_gmap_post_type_option_name, $_POST['bsk_posts_gmap_posttypes'] );
		update_option( $this->_bsk_posts_gmap_zoom_level_option_name, $_POST['bsk_posts_gmap_zoom_level'] );
		
		add_action('admin_notices', array($this, 'bsk_setting_saved_message'));	
	}
	
	function bsk_setting_saved_message(){
	?>
		<div class='updated' style='padding:15px; position:relative;'>Settings saved.</div>
    <?php
	}
}

