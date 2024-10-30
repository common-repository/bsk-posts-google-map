<?php
class BSKPostsGmapWidget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'bsk_posts_gmap_widget', // Base ID
			'BSK Post Google Map Widget', // Name
			array( 'description' => __( 'Output Google Map with a mark  in widget. The mark\'s latitude & longitude are from what you set when add/edit post.', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		if(!is_singular() && $instance['bsk_posts_gmap_show_singular']){
			return;
		}
		echo $args['before_widget'];
		if( $instance['bsk_posts_gmap_title'] ){
			echo '<h3 class="widget-title">'.$instance['bsk_posts_gmap_title'].'</h3>';
		}
		//post latitude & longitude center
		$saved_latitude = '';
		$saved_longitude = '';
		$saved_latitude_longitude = $instance['bsk_posts_gmap_latitude_longitude'];
		$saved_latitude_longitude_array = explode(',', $saved_latitude_longitude);
		if( is_array($saved_latitude_longitude_array) && count($saved_latitude_longitude_array) > 1 ){
			$saved_latitude = trim($saved_latitude_longitude_array[0]);
			$saved_longitude = trim($saved_latitude_longitude_array[1]);
		}
		if(!$saved_latitude || !$saved_longitude){
			echo $args['after_widget'];
			return;	
		}
		$widget_id = str_replace('-', '_', $args['widget_id']);
		$str = '
				<div id="bsk_posts_gmap_widget_'.$widget_id.'" class="bsk_posts_gmap_widget">
				</div>
				<script type="text/javascript">
					var bsk_posts_gmap_widget_'.$widget_id.';
					bsk_posts_gmap_widget_'.$widget_id.' = new GMaps({
						el: \'#bsk_posts_gmap_widget_'.$widget_id.'\',
						lat: '.$saved_latitude.',
						lng: '.$saved_longitude.',
						zoom: '.get_option( '_bsk_posts_gmap_zoom_level_', 5).',
						mapType: "TERRAIN",
						zoomControl: false,
						panControl: false,
						overviewMapControl: false,
						streetViewControl: false,
						mapTypeControl: false
					});'."\n";
			
		$str .= '
				bsk_posts_gmap_widget_'.$widget_id.'.addMarker({
					lat: '.$saved_latitude.',
					lng: '.$saved_longitude.'
				});'."\n";
				
		$str .= '</script>';
		
		echo $str;
		
		echo $args['after_widget'];
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['bsk_posts_gmap_title'] = strip_tags( trim($new_instance['bsk_posts_gmap_title']) );
		$instance['bsk_posts_gmap_latitude_longitude'] = strip_tags( trim($new_instance['bsk_posts_gmap_latitude_longitude']) );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
			$bsk_posts_gmap_title = isset( $instance['bsk_posts_gmap_title'] ) ? $instance['bsk_posts_gmap_title'] : '';
			$bsk_posts_gmap_latitude_longitude = isset( $instance['bsk_posts_gmap_latitude_longitude'] ) ? $instance['bsk_posts_gmap_latitude_longitude'] : '';
		?>
		<p>Widget Title:<br />
		   <input name="<?php echo $this->get_field_name( 'bsk_posts_gmap_title' ); ?>" id="<?php echo $this->get_field_id( 'bsk_posts_gmap_title' ); ?>" value="<?php echo $bsk_posts_gmap_title; ?>" style="width:100%;" />
		</p>
		<p>Latitude&amp;Longitude: <br />
		   <input name="<?php echo $this->get_field_name( 'bsk_posts_gmap_latitude_longitude' ); ?>" id="<?php echo $this->get_field_id( 'bsk_posts_gmap_latitude_longitude' ); ?>" value="<?php echo $bsk_posts_gmap_latitude_longitude; ?>" style="width:100%;" />
		</p>
		<?php
	}
} //calss BSKPostsGmapWidget