<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Sldr_Settings_Tabs' ) ) {
	class Sldr_Settings_Tabs extends Bws_Settings_Tabs {
		public $is_general_settings = true;
		
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $sldr_options, $sldr_plugin_info, $sldr_id, $wpdb;

			$this->is_general_settings = ( isset( $_GET['page'] ) && 'slider-settings.php' == $_GET['page'] );

			if ( $this->is_general_settings ) {
				$tabs = array(
					'settings' 		=> array( 'label' => __( 'Settings', 'slider-bws' ) ),
					'misc' 			=> array( 'label' => __( 'Misc', 'slider-bws' ) ),
					'custom_code' 	=> array( 'label' => __( 'Custom Code', 'slider-bws' ) ),
				);
			} else {
				$tabs = array(
					'images' 		=> array( 'label' => __( 'Images', 'slider-bws' ) ),
					'settings' 		=> array( 'label' => __( 'Settings', 'slider-bws' ) )
				);
			}

			if ( $this->is_general_settings ) {
				$options = $sldr_options;
			} else {
				if ( empty( $sldr_id ) ) {
					$options = sldr_get_options_default();
				} else {
					$slider_single_setting 	= $wpdb->get_var( $wpdb->prepare( "SELECT `settings` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $sldr_id ) );
				
					$options = unserialize( $slider_single_setting );
				}
			}

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $sldr_plugin_info,
				'prefix' 			 => 'sldr',
				'default_options' 	 => sldr_get_options_default(),
				'options' 			 => $options,
				'tabs' 				 => $tabs,				
				'wp_slug'			 => 'slider-bws'
			) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {

			/* Global Settings */
			if ( $this->is_general_settings ) {
				$slider_request_options = array();
				/* Set lazy load for slideshow */
				$slider_request_options['lazy_load']			= ( isset( $_POST['sldr_lazy_load'] ) ) ? true : false;
				/* Set slide auto height */
				$slider_request_options['auto_height']			= ( isset( $_POST['sldr_auto_height'] ) ) ? true : false;
				
				$this->options = array_merge( $this->options, $slider_request_options );

				update_option( 'sldr_options', $this->options );
				$message = __( "Settings saved.", 'slider-bws' );
			}			

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *
		 */
		public function tab_images() {
			$wp_gallery_media_table = new Sldr_Media_Table();
			$wp_gallery_media_table->prepare_items(); ?>
			<h3 class="bws_tab_label"><?php _e( 'Slider Images', 'slider-bws' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>		
			<div>
				<div class="error hide-if-js">
					<p><?php _e( 'Adding images requires JavaScript.', 'slider-bws' ); ?></p>
				</div>
				<div class="wp-media-buttons">
					<a href="#" id="sldr-media-insert" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'slider-bws' ); ?></a>
				</div>
				<?php $wp_gallery_media_table->views(); ?>
			</div>
			<div class="clear"></div>
			<ul tabindex="-1" class="attachments ui-sortable ui-sortable-disabled hide-if-no-js" id="sldr-attachments">
				<?php $wp_gallery_media_table->display_rows(); ?>
			</ul>
			<div class="clear"></div>
			<div id="hidden"></div>
		<?php }

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Slider Settings', 'slider-bws' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table sldr_settings_form">
				<?php if ( $this->is_general_settings ) { ?>
					<tr>
						<th><?php _e( 'Lazy Load', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sldr_lazy_load" value="1" <?php checked( 1, $this->options['lazy_load'] ); ?> /> 
								<span class="bws_info"><?php _e( 'Enable to use lazy load for images (recommended for long pages). Images outside of viewport are not loaded until user scrolls to them.', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Auto Height', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sldr_auto_height" value="1" <?php checked( 1, $this->options['auto_height'] ); ?> /> 
								<span class="bws_info"><?php _e( 'Enable to change slider height automatically (according to the highest slide).', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<th><?php _e( 'Autoplay', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sldr_autoplay" class="bws_option_affect" data-affect-show=".sldr_autoplay" value="1" <?php checked( 1, $this->options['autoplay'] ); ?> /> <span class="bws_info"><?php _e( 'Enable to turn autoplay on for the slideshow.', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
					<tr class="sldr_autoplay">
						<th><?php _e( 'Autoplay Timeout', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="number" name="sldr_autoplay_timeout" min="1000" max="10000" value="<?php echo $this->options["autoplay_timeout"]; ?>" /> <?php _e( 'ms', 'slider-bws' ); ?>
							</label>
						</td>
					</tr>
					<tr class="sldr_autoplay">
						<th><?php _e( 'Autoplay Pause', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sldr_autoplay_hover_pause" value="1" <?php checked( 1, $this->options['autoplay_hover_pause'] ); ?> /> 
								<span class="bws_info"><?php _e( 'Enable to pause autoplay on mouse hover.', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Loop', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sldr_loop" value="1" <?php checked( 1, $this->options['loop'] ); ?> /> 
								<span class="bws_info"><?php _e( 'Enable to loop the slideshow.', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Navigation', 'slider-bws' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="sldr_nav" value="1" <?php checked( 1, $this->options['nav'] ); ?> /> 
									<?php _e( 'Arrows', 'slider-bws' ); ?>
								</label>
								<br/>
								<label>
									<input type="checkbox" name="sldr_dots" value="1" <?php checked( 1, $this->options['dots'] ); ?> /> 
									<?php _e( 'Dots', 'slider-bws' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Number of Visible Images per Slide', 'slider-bws' ); ?></th>
						<td>
							<label>
								<input type="number" name="sldr_items" min="1" max="10" value="<?php echo $this->options['items']; ?>" /> 
								<span class="bws_info"><?php _e( 'Number of Images which are displayed simultaneously on a single slide.', 'slider-bws' ); ?></span>
							</label>
						</td>
					</tr>
					
				<?php } ?>	
			</table>
		<?php }
	}
}