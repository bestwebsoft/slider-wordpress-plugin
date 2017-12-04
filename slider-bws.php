<?php
/*
Plugin Name: Slider by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/slider/
Description: Simple and easy to use plugin adds a slider to your web site.
Author: BestWebSoft
Text Domain: slider-bws
Domain Path: /languages
Version: 1.0.1
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2017 BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Add Wordpress page 'bws_plugins' and sub-page of this plugin to admin-panel.
 */
if ( ! function_exists( 'sldr_add_admin_menu' ) ) {
	function sldr_add_admin_menu() {
		/* Add custom menu page */
		$slider_general_page = add_menu_page( __( 'Sliders', 'slider-bws' ), __( 'Sliders', 'slider-bws' ), 'manage_options', 'slider.php', 'sldr_table_page_render', 'none', 44.1 );
		$slider_slide_page = add_submenu_page( 'slider.php', __( 'Add new', 'slider-bws' ), __( 'Add New', 'slider-bws' ), 'manage_options', 'slider-new.php', 'sldr_add_new_render' );
		$slider_category_page = add_submenu_page( 'slider.php', __( 'Slider Categories', 'slider-bws' ), __( 'Slider Categories', 'slider-bws' ), 'manage_options', 'slider-categories.php', 'sldr_categories_render' );
		$slider_settings_page = add_submenu_page( 'slider.php', __( 'Slider Global Settings', 'slider-bws' ), __( 'Global Settings', 'slider-bws' ), 'manage_options', 'slider-settings.php', 'sldr_settings_page' );

		add_submenu_page( 'slider.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'sldr-bws-panel', 'bws_add_menu_render' );

		/* Add help tabs */
		add_action( 'load-' . $slider_settings_page, 'sldr_add_tabs' );
		add_action( 'load-' . $slider_general_page, 'sldr_add_tabs' );
		add_action( 'load-' . $slider_general_page, 'sldr_screen_options' );
		add_action( 'load-' . $slider_slide_page, 'sldr_add_tabs' );
		add_action( 'load-' . $slider_category_page, 'sldr_add_tabs' );
		add_action( 'load-' . $slider_category_page, 'sldr_cat_screen_options' );
	}
}

/**
 * Plugin initialization on frontend and backend.
 */
if ( ! function_exists( 'sldr_init' ) ) {
	function sldr_init() {
		global $sldr_plugin_info;
		/* Add bws menu. Use in slider_admin_menu */
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		/* Get plugin data */
		if ( ! $sldr_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$sldr_plugin_info = get_plugin_data( __FILE__ );
		};

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $sldr_plugin_info, '3.9' );
		/* Call register settings function */
		sldr_settings();
	}
}

/**
* Plugin initialization on backend.
*/
if ( ! function_exists ( 'sldr_admin_init' ) ) {
	function sldr_admin_init() {
		global $bws_plugin_info, $sldr_plugin_info, $bws_shortcode_list;
		
		/* Add variable for bws_menu. */
		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array(
				'id' 		=> '650',
				'version' 	=> $sldr_plugin_info['Version']
			);
		/* Add slider to global $bws_shortcode_list  */
		$bws_shortcode_list['sldr'] = array(
			'name' 			=> 'Slider',
			'js_function' 	=> 'sldr_shortcode_init'
		);
	}
}

/**
 * Add localization.
 */
if ( ! function_exists( 'sldr_plugins_loaded' ) ) {
	function sldr_plugins_loaded() {
		/* Internationalization */
		load_plugin_textdomain( 'slider-bws', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Register settings.
 */
if ( ! function_exists( 'sldr_settings' ) ) {
	function sldr_settings() {
		global $sldr_options, $sldr_plugin_info;
		$plugin_db_version = '0.1';

		/* Install the option defaults. */
		if ( ! get_option( 'sldr_options' ) ) {
			$option_defaults = sldr_get_options_default();
			add_option( 'sldr_options', $option_defaults );
		}

		/* Get options from the database. */
		$sldr_options = get_option( 'sldr_options' );

		if ( ! isset( $sldr_options['plugin_option_version'] ) || $sldr_options['plugin_option_version'] != $sldr_plugin_info['Version'] ) {
			$option_defaults = sldr_get_options_default();
			$sldr_options = array_merge( $option_defaults, $sldr_options );
			$sldr_options['plugin_option_version'] = $sldr_plugin_info['Version'];
			$update_option = true;
		}

		/**
		 * Update pugin database and options
		 */
		if ( ! isset( $sldr_options['plugin_db_version'] ) || $sldr_options['plugin_db_version'] != $plugin_db_version ) {
			sldr_create_table();
			$sldr_options['plugin_db_version'] = $plugin_db_version;
			$update_option = true;
		}

		if ( isset( $update_option ) )
			update_option( 'sldr_options', $sldr_options );
	}
}

/**
 * Get Plugin default options
 */
if ( ! function_exists( 'sldr_get_options_default' ) ) {
	function sldr_get_options_default() {
		global $sldr_plugin_info;

		$option_defaults = array(
			/* internal general */
			'plugin_option_version' 	=> $sldr_plugin_info["Version"],
			'first_install'				=> strtotime( "now" ),
			'suggest_feature_banner'	=> 1,
			'display_settings_notice'	=> 1,
			/* general */
			'loop'						=> false,
			'nav'						=> false,
			'dots'						=> false,
			'items'						=> '1',
			'autoplay'					=> false,
			'autoplay_timeout'			=> '2000',
			'autoplay_hover_pause'		=> false,
			'lazy_load'					=> false,
			'auto_height'				=> '1',
			'order_by'					=> 'meta_value_num',
			'order'						=> 'ASC'
		);
		return $option_defaults;
	}
}

/**
 * Function for plugin activation.
 */
if ( ! function_exists( 'sldr_plugin_activate' ) ) {
	function sldr_plugin_activate() {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );				
				register_uninstall_hook( __FILE__, 'sldr_plugin_uninstall' );
				restore_current_blog();
			}
			switch_to_blog( $old_blog );
			return;
		}

		register_uninstall_hook( __FILE__, 'sldr_plugin_uninstall' );
	}
}

/**
 * Function to create a new tables in database.
 */
if ( ! function_exists( 'sldr_create_table' ) ) {
	function sldr_create_table() {
		global $wpdb;

		/* Require db Delta */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/* Create table for sliders */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "sldr_slider` (
			`slider_id` INT NOT NULL AUTO_INCREMENT,
			`datetime` DATE NOT NULL,
			`title` VARCHAR( 255 ) NOT NULL,
			`settings` BLOB NOT NULL,
			PRIMARY KEY (slider_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );

		/* Create table for sliders category */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "sldr_category` (
			`category_id` INT NOT NULL AUTO_INCREMENT,
			`title` VARCHAR( 255 ) NOT NULL,
			PRIMARY KEY (category_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );

		/* Create table for slides */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "sldr_slide` (
			`slide_id` INT NOT NULL AUTO_INCREMENT,
			`attachment_id` INT NOT NULL,
			`title` VARCHAR( 255 ) NOT NULL,
			`description` VARCHAR( 255 ) NOT NULL,
			`url` VARCHAR( 255 ) NOT NULL,
			`button` VARCHAR( 255 ) NOT NULL,
			`order` INT NOT NULL,
			PRIMARY KEY (slide_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );

		/* create table for sliders meta */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "sldr_relation` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`slider_id` INT  NOT NULL,
			`attachment_id` INT,
			`category_id` INT,
			PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );
	}
}

/* Function create filter for custom slides sorting */
if ( ! function_exists( 'sldr_edit_attachment_join' ) ) {
	function sldr_edit_attachment_join( $join_paged_statement ) {
		global $wpdb;

		$join_paged_statement = "LEFT JOIN `" . $wpdb->prefix . "sldr_slide` ON `" . $wpdb->prefix . "sldr_slide`.`attachment_id` = `" . $wpdb->prefix . "posts`.`ID`";			

		return $join_paged_statement;
	}
}

/* Function create filter for custom slides sorting */
if ( ! function_exists( 'sldr_edit_attachment_orderby' ) ) {
	function sldr_edit_attachment_orderby( $orderby_statement ) {
		global $wpdb;
		$orderby_statement  = "( `" . $wpdb->prefix . "sldr_slide`.order ) ASC";
		return $orderby_statement;
	}
}

/**
 * Extends WP_List_Table and WP_Media_List_Table classes.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! class_exists( 'WP_Media_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );
}
if ( ! class_exists( 'Sldr_List_Table' ) ) {
	/* WP_List_Table extends for render of slider table */
	class Sldr_List_Table extends WP_List_Table {

		/* Declare constructor */
		function __construct() {
			parent::__construct( array(
				'singular'	=> __( 'slider', 'slider-bws' ),
				'plural'	=> __( 'sliders', 'slider-bws' ),
			) );
		}

		/**
		 * Declare column renderer
		 *
		 * @param $item - row (key, value array)
		 * @param $column_name - string (key)
		 * @return HTML
		 */
		function column_default( $item, $column_name ) {
			global $wpdb;

			/* Get array of sliders image */
			$slider_attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT `attachment_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE `attachment_id` IS NOT NULL AND `slider_id` = %d", $item['slider_id'] ) );

			$slider_attachment_ids = array_map( 'esc_attr', $slider_attachment_ids );

			switch ( $column_name ) {
				case 'thumbnail':
					/* Thumbnail is first sliders picture */
					$thumbnail = wp_get_attachment_image( array_shift( $slider_attachment_ids ), array( 100, 100 ) );

					if ( ! empty( $thumbnail ) ) {
						echo '<a href="?page=slider-new.php&sldr_id=' . $item['slider_id'] . '"><span class="sldr_media_icon image-icon">' . $thumbnail . '</span></a>';
					} else {
						echo '<i>' . __( 'No Thumbnail Set.', 'slider-bws' ) . '</i>';
					}
					break;
				case 'shortcode':
					bws_shortcode_output( '[print_sldr id=' . $item['slider_id'] . ']' );
					break;
				case 'images_count':
					if ( ! empty( $slider_attachment_ids ) ) {
						echo count( $slider_attachment_ids );
					} else {
						echo '<i>' . __( 'No Images Set.', 'slider-bws' ) . '</i>';
					}
					break;
				case 'category':
					/* Get category list for slider with current ID in table. */
					$slider_current_categories_ids = $wpdb->get_col( $wpdb->prepare( "SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE `slider_id` = %d", $item['slider_id'] ) );

					$slider_current_categories_ids = array_map( 'esc_attr', $slider_current_categories_ids );

					/* Get category title for selected category ID. */
					$slider_category_title_array = array();

					foreach ( $slider_current_categories_ids as $slider_current_categories_id ) {

						if ( ! empty( $slider_current_categories_id ) ) {
							$slider_category_title = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "sldr_category` WHERE `category_id` = %d", $slider_current_categories_id ) );
							$slider_category_title_array[] = $slider_category_title;
						}
						
					}
					unset( $slider_current_categories_id );

					if ( ! empty( $slider_category_title_array ) ) {
						/* Display category with comma. */
						echo implode( ', ', $slider_category_title_array );
					} else {
						echo '<i>' . __( 'No Category Set.', 'slider-bws' ) . '</i>';
					}
					break;
				case 'datetime':
				case 'title':
					return $item[ $column_name ];
					break;
				default:
					return print_r( $item, true ) ;
			}
		}

		/**
		 * Render column with actions
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_title( $item ) {
			$actions = array(
				'edit'		=> sprintf( '<a href="?page=slider-new.php&sldr_id=%d">%s</a>', $item['slider_id'], __( 'Edit', 'slider-bws' ) ),
				'delete'	=> sprintf( '<a href="?page=%s&action=delete&sldr_id=%s">%s</a>', esc_html( $_REQUEST['page'] ), $item['slider_id'], __( 'Delete', 'slider-bws' ) ),
			);

			$title = empty( $item['title'] ) ? '(' . __( 'no title', 'slider-bws' ) . ')' : $item['title']; 

			return sprintf(
				'<strong><a href="?page=slider-new.php&sldr_id=%d">%s</strong></a>%s',
				$item['slider_id'], $title, $this->row_actions( $actions )
			);
		}

		/**
		 * Checkbox column renders
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="sldr_id[]" value="%s" />',
				$item['slider_id']
			);
		}

		/**
		 * Return columns to display in table
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = array(
				'cb'			=> '<input type="checkbox" />',
				'thumbnail'		=> __( 'Thumbnail', 'slider-bws' ),
				'title'			=> __( 'Title', 'slider-bws' ),
				'images_count'	=> __( 'Images', 'slider-bws' ),
				'category'		=> __( 'Category', 'slider-bws' ),
				'shortcode'		=> __( 'Shortcode', 'slider-bws' ),
				'datetime'		=> __( 'Date', 'slider-bws' )	
			);
			return $columns;
		}

		function no_items() {
			_e( 'No Sliders Found', 'slider-bws' );
		}

		/* Generate the table navigation above or below the table */
		function display_tablenav( $which )  { ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php $this->extra_tablenav( $which );
				$this->pagination( $which ); ?>
				<br class="clear" />
			</div>
		<?php }

		/* Add Dropdown categories for slider filter */
		function extra_tablenav( $which ) {			
			if ( 'top' == $which ) {
				global $wpdb;

				$slider_categories = $wpdb->get_results( "SELECT `category_id`, `title` FROM `" . $wpdb->prefix . "sldr_category`", ARRAY_A );
				/* Get selected category for current slider */
				$slider_category_selected = isset( $_POST['sldr_cat_select'] ) ? intval( $_POST['sldr_cat_select'] ) : ''; ?>
				<div class="alignleft actions">
					<select name="sldr_cat_select">
						<option value=""><?php _e( 'All Categories', 'slider-bws' ); ?></option>
						<?php foreach ( $slider_categories as $slider_category_value ) {
							$selected = ( $slider_category_value['category_id'] == $slider_category_selected ) ? ' selected="selected"' : '';
							echo '<option value="' . $slider_category_value['category_id'] . '"' . $selected . '>' . $slider_category_value['title'] . '</option>';
						} ?>
					</select>
					<input name="sldr_filter_action" type="submit" class="button" value="<?php _e( 'Filter', 'slider-bws' ); ?>" />				
				</div>
			<?php }
		}

		/**
		 * Return array of bulk actions if has any
		 *
		 * @return array
		 */
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'slider-bws' )
			);
			return $actions;
		}

		/**
		 * Processes bulk actions
		 *
		 */
		function process_bulk_action() {
			global $wpdb;

			$slider_deleted_id = isset( $_REQUEST['sldr_id'] ) ? (array) $_REQUEST['sldr_id'] : array();

			$slider_deleted_id = array_map( 'esc_attr', $slider_deleted_id );

			if ( 'delete' === $this->current_action() ) {
				/* If deleted some slider */
				if ( ! empty( $slider_deleted_id ) && is_array( $slider_deleted_id ) ) {
					/* If delete more 1 slider */
					foreach ( $slider_deleted_id as $slider_id ) {
						$wpdb->delete( $wpdb->prefix . 'sldr_slider', array( 'slider_id' => $slider_id ) );
						$wpdb->delete( $wpdb->prefix . 'sldr_relation', array( 'slider_id' => $slider_id ) );
					}
				} elseif ( ! empty( $slider_deleted_id ) ) {
					$wpdb->delete( $wpdb->prefix . 'sldr_slider', array( 'slider_id' => $slider_deleted_id ) );
					$wpdb->delete( $wpdb->prefix . 'sldr_relation', array( 'slider_id' => $slider_deleted_id ) );
				}
			}
		}

		/**
		 * Get rows from database and prepare them to be showed in table
		 */
		function prepare_items() {
			global $wpdb;

			$columns 	= $this->get_columns();
			$hidden 	= array();
			$sortable 	= $this->get_sortable_columns();

			/* Configure table headers, defined in this methods */
			$this->_column_headers = array( $columns, $hidden, $sortable, 'title' );

			/* Process bulk action if any */
			$this->process_bulk_action();

			$per_page_option 	= get_current_screen()->get_option( 'per_page' );
			$current_page 		= $this->get_pagenum();

			/* Display selected category  */
			$search = ( isset( $_POST['s'] ) ) ? '%' . stripslashes( esc_html( $_POST['s'] ) ) . '%' : '%%';				

			if ( ! empty( $_POST['sldr_cat_select'] ) ) {

				/* Show sliders by selected category */
				$slider_category = intval( $_POST['sldr_cat_select'] );
				$per_page = $per_page_option['default'];
				/* Prepare query params, as usual current page, order by and order direction */
				$paged =  0;

				/* Show selected slider categories */
				$this->items = $wpdb->get_results( $wpdb->prepare(
					"SELECT `" . $wpdb->prefix . "sldr_slider`.`slider_id`, `" . $wpdb->prefix . "sldr_slider`.`title`, `" . $wpdb->prefix . "sldr_slider`.`datetime` 
					FROM `" . $wpdb->prefix . "sldr_slider` INNER JOIN `" . $wpdb->prefix . "sldr_relation` 
					WHERE `" . $wpdb->prefix . "sldr_slider`.`slider_id` = `" . $wpdb->prefix . "sldr_relation`.`slider_id`
						AND `" . $wpdb->prefix . "sldr_relation`.`category_id` = %d
						AND `" . $wpdb->prefix . "sldr_relation`.`attachment_id` IS NULL
						AND `" . $wpdb->prefix . "sldr_slider`.`title` LIKE %s 
					LIMIT %d OFFSET %d", 
					$slider_category, $search, $per_page, $paged
				), ARRAY_A );

				/* Will be used in pagination settings */
				$total_items = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT( `" . $wpdb->prefix . "sldr_slider`.`slider_id` )
					FROM `" . $wpdb->prefix . "sldr_slider` INNER JOIN `" . $wpdb->prefix . "sldr_relation` 
					WHERE `" . $wpdb->prefix . "sldr_slider`.`slider_id` = `" . $wpdb->prefix . "sldr_relation`.`slider_id`
						AND `" . $wpdb->prefix . "sldr_relation`.`category_id` = %d
						AND `" . $wpdb->prefix . "sldr_relation`.`attachment_id` IS NULL
						AND `" . $wpdb->prefix . "sldr_slider`.`title` LIKE %s", 
					$slider_category, $search
				) );

			/* If no category selected, display all */
			} else {
				/* Show all categories */
				$per_page_query = get_user_meta( get_current_user_id(), $per_page_option['option'] );

				$per_page_value = intval( implode( ',', $per_page_query ) );

				$per_page = ! empty( $per_page_value ) ? $per_page_value : $per_page_option['default'];

				/* Prepare query params, as usual current page, order by and order direction */
				$paged = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] * $per_page ) - $per_page ) : 0;
				/* Will be used in pagination settings */
				$total_items = $wpdb->get_var( "SELECT COUNT( slider_id ) FROM  `" . $wpdb->prefix . "sldr_slider`" );

				/* Show all slider categories */
				$this->items = $wpdb->get_results( $wpdb->prepare(
					"SELECT `slider_id`, `title`, `datetime` 
					FROM `" . $wpdb->prefix . "sldr_slider`
					WHERE `title` LIKE %s 
					LIMIT %d OFFSET %d", 
					$search, $per_page, $paged
				), ARRAY_A );
			}

			/* Сonfigure pagination */
			$this->set_pagination_args( array(
				'total_items'	=> intval( $total_items ), /* total items defined above */
				'per_page'		=> $per_page, /* per page constant defined at top of method */
				'total_pages'	=> ceil( $total_items / $per_page ) /* calculate pages count */
			) );
		}
	}
}

if ( ! class_exists( 'Sldr_Category_List_Table' ) ) {
	/* WP_List_Table extends for render of slider categories */
	class Sldr_Category_List_Table extends WP_List_Table {

		/**
		 * Declare constructor
		 */
		function __construct() {
			parent::__construct( array(
				'singular'	=> 'category',
				'plural'	=> 'categories',
			) );
		}

		/**
		 * Declare column renderer
		 *
		 * @param $item - row (key, value array)
		 * @param $column_name - string (key)
		 * @return HTML
		 */
		function column_default( $item, $column_name ) {
			global $wpdb;

			switch ( $column_name ) {
				case 'count':
					/* Count items with current category */
					$sliders_with_current_category_count = intval( $wpdb->get_var( "SELECT COUNT( category_id ) FROM `" . $wpdb->prefix . "sldr_relation` WHERE `category_id` = '" . $item['category_id'] . "'" ) );				
					echo $sliders_with_current_category_count;
					break;
				case 'shortcode':
					bws_shortcode_output( '[print_sldr cat_id=' . $item['category_id'] . ']' );
					break;
				case 'name':
					return $item['title'];
				default:
					return print_r( $item, true ) ;
			}
		}

		/**
		 * Render column with actions,
		 * when you hover row "Edit | Delete" links showed
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_name( $item ) {
			$actions = array(
				'edit'		=> sprintf( '<a href="?page=slider-categories.php&action=edit&sldr_category_id=%s">%s</a>', $item['category_id'], __( 'Edit', 'slider-bws' ) ),
				'delete'	=> sprintf( '<a href="?page=slider-categories.php&action=delete&sldr_category_id=%s">%s</a>', $item['category_id'], __( 'Delete', 'slider-bws' ) ),
			);

			return sprintf(
					'<strong><a href="?page=slider-categories.php&sldr_category_id=%d&action=edit">%s</strong></a>%s',
					$item['category_id'], $item['title'], $this->row_actions( $actions )
				);
		}

		/**
		 * Checkbox column renders
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="sldr_category_id[]" value="%s" />',
				$item['category_id']
			);
		}

		/**
		 * Return columns to display in table
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = array(
				'cb'			=> '<input type="checkbox" />',
				'name'			=> __( 'Name', 'slider-bws' ),
				'count'			=> __( 'Count', 'slider-bws' ),
				'shortcode'		=> __( 'Shortcode', 'slider-bws' ),
			);
			return $columns;
		}

		function no_items() {
			_e( 'No Categories Found', 'slider-bws' );
		}

		/**
		 * Return columns that may be used to sort table
		 *
		 * @return array
		 */
		function get_sortable_columns() {
			$sortable_columns = array(
				'name'			=> array( 'name', true ), /* default sort */
			);
			return $sortable_columns;
		}

		/**
		 * Return array of bulk actions if has any
		 *
		 * @return array
		 */
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'slider-bws')
			);
			return $actions;
		}

		/**
		 * Processes bulk actions
		 *
		 */
		function process_bulk_action() {
			global $wpdb;

			$slider_category_deleted_id = isset( $_REQUEST['sldr_category_id'] ) ? (array) $_REQUEST['sldr_category_id'] : array();

			$slider_category_deleted_id = array_map( 'esc_attr', $slider_category_deleted_id );

			if ( 'delete' === $this->current_action() ) {
				/* If deleted more one category */
				if ( ! empty( $slider_category_deleted_id ) && is_array( $slider_category_deleted_id ) ) {
					foreach ( $slider_category_deleted_id as $slider_deleted_id ) {
						$wpdb->delete( $wpdb->prefix . 'sldr_category', array( 'category_id' => $slider_deleted_id ) );
						$wpdb->delete( $wpdb->prefix . 'sldr_relation', array( 'category_id' => $slider_deleted_id ) );
					}
					unset( $slider_deleted_id );
				/* If deleted one category */
				} elseif ( ! empty( $slider_category_deleted_id ) ) {
					$wpdb->delete( $wpdb->prefix . 'sldr_category', array( 'category_id' => $slider_category_deleted_id ) );
					$wpdb->delete( $wpdb->prefix . 'sldr_relation', array( 'category_id' => $slider_category_deleted_id ) );
				}
			}
		}
		
		/**
		 * Get rows from database and prepare them to be showed in table
		 *
		 */
		function prepare_items() {
			global $wpdb;

			$columns 	= $this->get_columns();
			$hidden 	= array();
			$sortable 	= $this->get_sortable_columns();

			/* Configure table headers, defined in this methods */
			$this->_column_headers = array( $columns, $hidden, $sortable );

			/* Process bulk action if any */
			$this->process_bulk_action();

			/* Pagination */
			$per_page_option 	= get_current_screen()->get_option('per_page');
			$current_page 		= $this->get_pagenum();
			/* Prepare query params, as usual current page, order by and order direction */
			$order = isset( $_REQUEST['order'] ) ? esc_html( $_REQUEST['order'] ) : 'asc';

			/* Pagination */
			$per_page_query = get_user_meta( get_current_user_id(), $per_page_option['option'] );
			$per_page_value = intval( implode( ',', $per_page_query ) );
			$per_page 		= ! empty( $per_page_value ) ? $per_page_value : $per_page_option['default'];			
			$paged 			= isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] * $per_page ) - $per_page ) : 0;

			/* Search result for categories */
			if ( isset( $_REQUEST['s'] ) ) {
				/* Get search query */
				$cat_search_title 	= '%' . stripslashes( esc_html( $_REQUEST['s'] ) ) . '%';
				/* Display total items for search results */
				$total_items = count( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "sldr_category` WHERE `title` LIKE %s", $cat_search_title ), ARRAY_A ) );
			} else {
				/* If search query is empty, display all table content */
				$cat_search_title 	= '%%';				
				/* Will be used in pagination settings */
				$total_items  = $wpdb->get_var( "SELECT COUNT( category_id ) FROM  `" . $wpdb->prefix . "sldr_category`" );
			}

			/* Define $items array */
			/* notice that last argument is ARRAY_A, so we will retrieve array */
			$this->items 		= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "sldr_category` WHERE `title` LIKE %s ORDER BY `title` " . $order . " LIMIT %d OFFSET %d", $cat_search_title, $per_page, $paged ), ARRAY_A );

			/* Сonfigure pagination */
			$this->set_pagination_args( array(
				'total_items'	=> intval( $total_items ), /* total items defined above */
				'per_page'		=> $per_page, /* per page constant defined at top of method */
			) );
		}
	}
}

if ( ! class_exists( 'Sldr_Media_Table' ) ) {

	if ( ! class_exists( 'WP_List_Table' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	if ( ! class_exists( 'WP_Media_List_Table' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );

	class Sldr_Media_Table extends WP_Media_List_Table {

		public function __construct( $args = array() ) {
			parent::__construct( array(
				'plural' => 'media',
				'screen' => isset( $args['screen'] ) ? $args['screen'] : '',
			) );
		}

		function no_items() {
			_e( 'No images found.', 'slider-bws' );
		}

		function display_tablenav( $which ) { ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<?php $this->extra_tablenav( $which ); ?>
				<br class="clear" />
			</div>
		<?php }

		function display_rows() {
			global $post, $wp_query, $sldr_id, $wpdb;

			/* Get slider images */
			$slider_attachment_ids	= $wpdb->get_col( $wpdb->prepare( "SELECT `attachment_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE `attachment_id` IS NOT NULL AND `slider_id` = %d", $sldr_id ) );
			if ( ! empty( $slider_attachment_ids ) ) {
				add_filter( 'posts_orderby', 'sldr_edit_attachment_orderby' );
				add_filter( 'posts_join','sldr_edit_attachment_join' );

				/* Loop for media items */
				query_posts( array(
					'order'				=> 'asc',
					'post__in'			=> 	$slider_attachment_ids,
					'post_type'			=> 'attachment',
					'posts_per_page'	=> -1,
					'post_status'		=> 'inherit'
				) );

				while ( have_posts() ) {
					the_post();
					$this->single_row( $post );
				}
				
				wp_reset_postdata();
				wp_reset_query();

				remove_filter( 'posts_orderby', 'sldr_edit_attachment_orderby' );
				remove_filter( 'posts_join','sldr_edit_attachment_join' );
			}
		}

		function get_views() {
			return false;
		}

		public function views() { ?>
			<div class="sldr-wp-filter hide-if-no-js">
				<a href="#" class="button media-button sldr-media-bulk-select-button hide-if-no-js"><?php _e( 'Bulk Select', 'slider-bws' ); ?></a>
				<a href="#" class="button media-button sldr-media-bulk-cansel-select-button hide-if-no-js"><?php _e( 'Cancel Selection', 'slider-bws' ); ?></a>
				<a href="#" class="button media-button button-primary sldr-media-bulk-delete-selected-button hide-if-no-js" disabled="disabled"><?php _e( 'Delete Selected', 'slider-bws' ); ?></a>
			</div>
		<?php }

		function single_row( $post ) {
			global $sldr_options, $sldr_plugin_info, $wpdb, $sldr_id;

			$slide_attribute = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "sldr_slide` WHERE `attachment_id` = %d", $post->ID ), ARRAY_A );
			$attachment_metadata = wp_get_attachment_metadata( $post->ID );
			$image_attributes_medium = wp_get_attachment_image_src( $post->ID, 'medium' ); 
			$image_attributes_large = wp_get_attachment_image_src( $post->ID, 'large' ); ?>
			<li tabindex="0" id="post-<?php echo $post->ID; ?>" class="sldr-media-attachment">
				<div class="sldr-media-attachment-preview">
					<div class="sldr-media-thumbnail">
						<div class="centered">
							<img src="<?php echo $image_attributes_medium[0]; ?>" class="thumbnail" draggable="false" />
							<input type="hidden" name="_sldr_order[<?php echo $post->ID; ?>]" value="<?php echo $slide_attribute['order']; ?>" />
						</div>
					</div>
					<div class="sldr-media-attachment-details">
						<?php echo $post->post_title; ?>						
					</div>
				</div>
				<a href="#" class="sldr-media-actions-delete dashicons dashicons-trash" title="<?php _e( 'Remove Image from Slider', 'slider-bws' ); ?>"></a>
				<input type="hidden" class="sldr_attachment_id" name="_sldr_attachment_id" value="<?php echo $post->ID; ?>" />
				<input type="hidden" class="sldr_slider_id" name="_sldr_slider_id" value="<?php echo $sldr_id; ?>" />
				<a class="thickbox sldr-media-actions-edit dashicons dashicons-edit" href="<?php echo get_edit_post_link( $post->ID ); ?>#TB_inline?width=800&height=450&inlineId=sldr-media-attachment-details-box-<?php echo $post->ID; ?>" title="<?php _e( 'Edit Image Info', 'slider-bws' ); ?>"></a>
				<a class="sldr-media-check" tabindex="-1" title="<?php _e( 'Deselect', 'slider-bws' ); ?>" href="#"><div class="media-modal-icon"></div></a>
				<div id="sldr-media-attachment-details-box-<?php echo $post->ID; ?>" class="sldr-media-attachment-details-box">
					<?php  ?>
					<div class="sldr-media-attachment-details-box-left">
						<img src="<?php echo $image_attributes_large[0]; ?>" alt="<?php echo $post->post_title; ?>" title="<?php echo $post->post_title; ?>" height="auto" width="<?php echo $image_attributes_large[1]; ?>" />
					</div>
					<div class="sldr-media-attachment-details-box-right">
						<div class="attachment-details">
							<div class="attachment-info">
								<div class="details">
									<div><?php _e( 'File name', 'slider-bws' ); ?>: <?php echo $post->post_title; ?></div>
									<div><?php _e( 'File type', 'slider-bws' ); ?>: <?php echo get_post_mime_type( $post->ID ); ?></div>
									<?php if ( ! empty( $attachment_metadata ) ) { ?>
										<div><?php _e( 'Dimensions', 'slider-bws' ); ?>: <?php echo $attachment_metadata['width']; ?> &times; 	<?php echo $attachment_metadata['height']; ?></div>
									<?php } ?>
								</div>
							</div>						
							<label class="setting" data-setting="title">
								<span class="name">
									<?php _e( 'Title', 'slider-bws' ); ?>
								</span>
								<input type="text" name="sldr_image_title[<?php echo $post->ID; ?>]" value="<?php echo $slide_attribute['title']; ?>" />
							</label>
							<label class="setting" data-setting="description">
								<span class="name"><?php _e( 'Description', 'slider-bws' ); ?></span>
								<textarea name="sldr_image_description[<?php echo $post->ID; ?>]"><?php echo $slide_attribute['description']; ?></textarea>
							</label>							
							<label class="setting" data-setting="alt">
								<span class="name"><?php _e( 'Button URL', 'slider-bws' ); ?></span>
								<input type="text" name="sldr_link_url[<?php echo $post->ID; ?>]" value="<?php echo $slide_attribute['url']; ?>" />
							</label>
							<label class="setting">
								<span class="name">
									<?php _e( 'Button Text', 'slider-bws' ); ?>
								</span>
								<input type="text" name="sldr_button_text[<?php echo $post->ID; ?>]" value="<?php echo $slide_attribute['button']; ?>" />		
							</label>
							<div class="clear"></div>
							<div class="sldr-media-attachment-actions">
								<a href="post.php?post=<?php echo $post->ID; ?>&amp;action=edit"><?php _e( 'Edit more details', 'slider-bws' ); ?></a>
								<span class="sldr-separator">|</span>
								<a href="#" class="sldr-media-actions-delete"><?php _e( 'Remove from Slider', 'slider-bws' ); ?></a>
								<input type="hidden" class="sldr_attachment_id" name="_sldr_attachment_id" value="<?php echo $post->ID; ?>" />
								<input type="hidden" class="sldr_slider_id" name="_sldr_slider_id" value="<?php echo $sldr_id; ?>" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</li>
		<?php }
	}
}

/**
 * Function will render slider page.
 */
if ( ! function_exists( 'sldr_table_page_render' ) ) {
	function sldr_table_page_render() {
		$slider_table = new Sldr_List_Table();
		$slider_table->prepare_items();

		$message = ''; ?>
		<form class="bws_form sldr_form" method="POST" action="admin.php?page=slider.php">
			<?php if ( 'delete' === $slider_table->current_action() ) {
				$message =  __( 'Slider deleted.', 'slider-bws' );
			} ?>
			<div class="wrap">
				<?php printf(
					'<h1> %s <a class="add-new-h2" href="%s" >%s</a></h1>',
					esc_html__( 'Sliders', 'slider-bws' ),
					esc_url( admin_url( 'admin.php?page=slider-new.php' ) ),
					esc_html__( 'Add New', 'slider-bws' )
				); ?>
				<div id="sldr_settings_message" class="notice is-dismissible updated below-h2 fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $message; ?></strong></p>
				</div>
				<?php $slider_table->search_box( __( 'Search Sliders', 'slider-bws' ), 'sldr_slider' );
				$slider_table->display(); ?>
			</div>
			<input type="hidden" name="sldr_form_submit" value="submit" />
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'sldr_nonce_form_name' ); ?>
		</form>
	<?php }
}

/**
 * Handler form for slide
 */
if ( ! function_exists ( 'sldr_form_handler' ) ) {
	function sldr_form_handler() {
		global $wpdb, $sldr_id;

		$plugin_basename	= plugin_basename( __FILE__ );
		/* Get slider ID. */
		$sldr_id = ! empty( $_REQUEST['sldr_id'] ) ? intval( $_REQUEST['sldr_id'] ) : "";

		/* Handler for slider form. Save by click on "publish" button */
		if ( isset( $_POST['sldr_publish'] ) && check_admin_referer( $plugin_basename, 'sldr_nonce_form_name' ) ) {
			/* Handler for settings */
			$sldr_request_options 							= array();
			/* Set loop in slideshow. */
			$sldr_request_options['loop']					= ( isset( $_POST['sldr_loop'] ) ) ? true : false;
			/* Display navigation button */
			$sldr_request_options['nav']					= ( isset( $_POST['sldr_nav'] ) ) ? true : false;
			/* Display navigation Dots */
			$sldr_request_options['dots']					= ( isset( $_POST['sldr_dots'] ) ) ? true : false;
			/* Set items per page */
			$sldr_request_options['items']					= ( ! empty( $_POST['sldr_items'] ) ) ? intval( $_POST['sldr_items'] ) : 1;
			/* Set autoplay */
			$sldr_request_options['autoplay']				= ( isset( $_POST['sldr_autoplay'] ) ) ? true : false;
			/* Autoplay timeout */
			$sldr_request_options['autoplay_timeout'] 		= ( ! empty( $_POST['sldr_autoplay_timeout'] ) ) ? intval( $_POST['sldr_autoplay_timeout']  ) : '2000';
			/* Autoplay hover pause */
			$sldr_request_options['autoplay_hover_pause']	= ( isset( $_POST['sldr_autoplay_hover_pause'] ) ) ? true : false;

			$sldr_options = serialize( $sldr_request_options );

			/* Slider title */
			$slider_title 	= esc_html( trim( wp_unslash( $_POST['sldr_slider_title'] ) ) );
			if ( ! empty( $sldr_id ) ) {
				$wpdb->update( $wpdb->prefix . 'sldr_slider',
					array(
						'title' => $slider_title,
						'settings' => $sldr_options
					),
					array( 'slider_id' => $sldr_id )
				);
			} else {
				$wpdb->insert( $wpdb->prefix . 'sldr_slider',
					array(
						'title' => $slider_title,
						'datetime' => date( 'Y-m-d' ),
						'settings' => $sldr_options
					)
				);
				/* Get slider ID for new slider. */
				$sldr_id = $wpdb->insert_id;
			}

			/* Slider category from category metabox. */
			/* Get all categories list from hidden input */
			$slider_all_category_ids = isset( $_POST['sldr_category_unchecked_id'] ) ? $_POST['sldr_category_unchecked_id'] : "";
			/* Save slider category for current slider. */
			if ( isset( $_POST['sldr_category_id'] ) ) {
				$slider_category_id = $_POST['sldr_category_id'];
				/* Add selected category to DB. */
				foreach ( (array)$slider_all_category_ids as $slider_all_category_id ) {
					if ( ! in_array( $slider_all_category_id, $slider_category_id ) ) {
						$wpdb->delete(
							$wpdb->prefix . 'sldr_relation',
							array( 'category_id' => $slider_all_category_id , 'slider_id' => $sldr_id )
						);
					} else {
						$wpdb->query( $wpdb->prepare(
							"INSERT INTO `" . $wpdb->prefix . "sldr_relation` ( `slider_id`, `category_id` )
							SELECT * FROM (SELECT %d as `slider_id` , %d as `category_id` ) AS tmp
							WHERE NOT EXISTS (
								SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE slider_id = %d AND category_id = %d
							) LIMIT 1",
							$sldr_id, $slider_all_category_id, $sldr_id, $slider_all_category_id
						) );
					}
				}
			/* Delete selected category from DB. */
			} elseif ( ! empty( $slider_all_category_ids ) ) {
				foreach ( (array)$slider_all_category_ids as $slider_all_category_id ) {
					$wpdb->delete(
						$wpdb->prefix . 'sldr_relation',
						array( 'category_id' => $slider_all_category_id, 'slider_id' => $sldr_id )
					);
				}
			}

			/* Save adding image to DB. */
			if ( isset( $_POST['sldr_new_image'] ) ) {
				$slider_new_attachments = $_POST['sldr_new_image'];
				/* Get added images. */
				/* Add new image to slider images array. */
				foreach ( $slider_new_attachments as $slider_new_attachment ) {

					/* If slide already exist in DB, don't insert slide */
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO `" . $wpdb->prefix . "sldr_relation` ( `slider_id`, `attachment_id` )
						SELECT * FROM ( SELECT %d as `slider_id` , %d as `attachment_id` ) AS tmp
							WHERE NOT EXISTS (
							SELECT `attachment_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE slider_id = %d AND attachment_id = %d
							) LIMIT 1", 
						$sldr_id, $slider_new_attachment, $sldr_id, $slider_new_attachment
					) );
				}
			}

			/* Slide title\description\URL */
			if ( ! empty( $_POST['sldr_image_title'] ) ) {
				foreach ( $_POST['sldr_image_title' ] as $slider_attachment_id => $slider_attachment_title ) {
					/* Prepare data to save in DB */
					$slider_attachment_title 	= htmlspecialchars( trim( wp_unslash( $slider_attachment_title ) ) );
					$slider_attachment_description = esc_html( trim( wp_unslash( $_POST['sldr_image_description'][ $slider_attachment_id ] ) ) );
					$slider_attachment_url = esc_url( trim( $_POST['sldr_link_url'][ $slider_attachment_id ] ) );
					if ( filter_var( $slider_attachment_url, FILTER_VALIDATE_URL ) === FALSE )
						$slider_attachment_url = '';
					$slider_attachment_button_text = esc_html( trim( wp_unslash( $_POST['sldr_button_text'][ $slider_attachment_id ] ) ) );
					
					/* Check: if data exist in DB, then update the data. If not exist, then insert the data */
					$slide_id = $wpdb->get_var( $wpdb->prepare( "SELECT `slide_id` FROM `" . $wpdb->prefix . "sldr_slide` WHERE `attachment_id` = %d", $slider_attachment_id ) );

					if ( ! empty( $slide_id ) ) {
						$wpdb->update( $wpdb->prefix . 'sldr_slide',
							array(
								'title' => $slider_attachment_title,
								'description' => $slider_attachment_description,
								'url' => $slider_attachment_url,
								'button' => $slider_attachment_button_text
							),
							array( 'attachment_id' => $slider_attachment_id )
						);
					} else {
						$wpdb->insert( $wpdb->prefix . 'sldr_slide',
							array(
								'attachment_id' => $slider_attachment_id,
								'title' => $slider_attachment_title,
								'description' => $slider_attachment_description,
								'url' => $slider_attachment_url,
								'button' => $slider_attachment_button_text
							)
						);
					}
				}
			}

			/* Slide order */
			if ( isset( $_POST['_sldr_order'] ) ) {
				/* Set counter for slide order */
				$i = 1;
				foreach ( $_POST['_sldr_order'] as $slider_attachment_order_id => $order_id ) {
					$wpdb->update(
						$wpdb->prefix . 'sldr_slide',
						array( 'order' => $i ),
						array( 'attachment_id' => $slider_attachment_order_id )
					);
					$i++;
				}
			}
		}

		return $sldr_id;
	}
}

/**
 * Function will render new slider page.
 */
if ( ! function_exists( 'sldr_add_new_render' ) ) {
	function sldr_add_new_render( $item ) {
		global $sldr_id, $wpdb, $post;

		$sldr_id = sldr_form_handler();
		$plugin_basename = plugin_basename( __FILE__ );
		$message = $error  = "";		

		/* Add admin notice to slider menu. */
		if ( isset( $_REQUEST['sldr_publish'] ) && check_admin_referer( $plugin_basename, 'sldr_nonce_form_name' ) ) {
			$message = __( 'Slider updated.', 'slider-bws' );
		} ?>
		<div class="wrap sldr_wrap">
			<?php /* Add page name and add new button to page */
			if ( ! empty( $sldr_id ) ) {
				echo '<h1>'. __( 'Edit Slider', 'slider-bws' ) . '<a class="page-title-action" href="' . admin_url( 'admin.php?page=slider-new.php' ) . '">' . __( 'Add New', 'slider-bws' ) . '</a></h1>';
			} else {
				echo '<h1>'. __( 'Add New Slider', 'slider-bws' ) . '</h1>';
			} ?>					
			<form class="bws_form" method="POST" action="admin.php?page=slider-new.php&amp;sldr_id=<?php echo $sldr_id; ?>">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
						<div id="post-body-content">							
							<div id="sldr_settings_message" class="notice is-dismissible updated below-h2 fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>>
								<p><strong><?php echo $message; ?></strong></p>
							</div>
							<div id="titlediv">
								<div id="titlewrap">
									<input name="sldr_slider_title" size="30" value="<?php echo esc_html( $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $sldr_id ) ) ); ?>" id="title" spellcheck="true" autocomplete="off" type="text" placeholder="<?php _e( 'Enter title here', 'slider-bws' ); ?>" />
								</div>
								<div class="inside"></div>
							</div>
							<?php require_once( dirname( __FILE__ ) . '/includes/class-sldr-settings.php' );
							$page = new Sldr_Settings_Tabs( plugin_basename( __FILE__ ) );
							$page->display_tabs(); ?>
						</div><!-- end .post-body-content -->
						<div id="postbox-container-1" class="postbox-container">
							<?php /* Used to save closed meta boxes and their order */
							wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
							wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
							/* Add metaboxes to media list page */				
							add_meta_box( 'sldr_submit', __( 'Publish', 'slider-bws' ), 'sldr_submit_metabox', 'sldr_metabox', 'side', 'high' );
							add_meta_box( 'sldr_category', __( 'Slider Categories', 'slider-bws' ), 'sldr_category_metabox', 'sldr_metabox', 'side', 'default' );
							if ( ! empty( $_REQUEST['sldr_id'] ) )
								add_meta_box( 'sldr_shortcode', __( 'Slider Shortcode', 'slider-bws' ), 'sldr_shortcode_metabox', 'sldr_metabox', 'side', 'default' );
							do_meta_boxes( 'sldr_metabox', 'side', null ); ?>
						</div>
						<div class="clear"></div>
					</div><!-- end .post-body -->
				</div><!-- end .poststuff -->
			</form>
		</div><!-- end wrap -->
	<?php }
}

/**
 * Function will render slider category page.
 */
if ( ! function_exists( 'sldr_categories_render' ) ) {
	function sldr_categories_render() {
		global $wpdb;

		$plugin_basename 	= plugin_basename( __FILE__ );
		$message 			= "";
		$id					= isset( $_REQUEST['sldr_category_id'] ) ? intval( $_REQUEST['sldr_category_id'] ) : "";

		/* Message for deleted categories */
		if ( isset( $_REQUEST['action'] ) && 'delete' == $_REQUEST['action'] ) {
			$message =  __( 'Category deleted.', 'slider-bws' );
		}

		/* Handler for category form */
		if ( isset( $_POST['sldr_category_submit'] ) && check_admin_referer( $plugin_basename, 'sldr_nonce_category_name' ) ) {
			if ( isset( $_POST['sldr_category_title'] ) ) {
				/* Prepare data to insert in DB */
				$slider_category_title = esc_html( trim( $_POST['sldr_category_title'] ) );

				/* Check for new category. If category not isset - insert new, else update */
				if ( isset( $id ) ) {
					$slider_get_category_id = $wpdb->get_var( $wpdb->prepare( "SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_category` WHERE `category_id` = %d", $id ) );
				}
				/* Send data to DB */
				if ( ! empty( $slider_get_category_id ) ) {
					$wpdb->update( $wpdb->prefix . 'sldr_category', array( 'title' => $slider_category_title ), array( 'category_id' => $id ) );
				} else {
					$wpdb->insert( $wpdb->prefix . 'sldr_category', array( 'title' => $slider_category_title ) );
				}
			}
			$message = __( 'Category updated.', 'slider-bws' );
		} ?>
		<div class="wrap">
			<?php /* If select "Edit category", render edit page with form for slider category */
			if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
				$slider_category_title_value = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "sldr_category` WHERE `category_id` = %d", $id ) ); ?>
				<h1 style="line-height: normal;"><?php _e( 'Edit Category', 'slider-bws' ); ?></h1>
				<div id="sldr_settings_message" class="notice is-dismissible updated below-h2 fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $message; ?></strong></p>
				</div>
				<form method="post" action="admin.php?page=slider-categories.php&amp;sldr_category_id=<?php echo $id; ?>&amp;action=edit">
					<table class="form-table">
						<tr class="form-field form-required term-name-wrap">
							<th><label for="tag-name"><?php _e( 'Name', 'slider-bws' ); ?></label></th>
							<td>
								<input name="sldr_category_title" id="tag-name" required value="<?php echo esc_html( $slider_category_title_value ); ?>" size="40" type="text">
							</td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e( 'Update', 'slider-bws' ); ?>" />
						<input type="hidden" name="sldr_category_submit" value="submit" />
						<?php wp_nonce_field( $plugin_basename, 'sldr_nonce_category_name' ); ?>
					</p>
				</form>
			<?php } else { ?>
				<h1><?php _e( 'Slider Categories', 'slider-bws' ); ?></h1>
				<div id="sldr_settings_message" class="notice is-dismissible updated below-h2 fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $message; ?></strong></p>
				</div>
				<div id="col-container" class="wp-clearfix">
					<div id="col-right">
						<form id="sldr_categories_table" method="POST">
							<?php /* Display category table */
							$slider_categories_table = new Sldr_Category_List_Table();
							$slider_categories_table->prepare_items();
							$slider_categories_table->search_box( __( 'Search Slider Categories', 'slider-bws' ), 'sldr_categories' );
							$slider_categories_table->display(); ?>
						</form>
					</div><!-- #col-right -->
					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h2><?php _e( 'Add New Slider Category', 'slider-bws' ); ?></h2>
								<form id="addtag" method="POST" action="admin.php?page=slider-categories.php">
									<div class="form-field form-required term-name-wrap">
										<label for="tag-name"><?php _e( 'Name', 'slider-bws' ); ?></label>
										<input name="sldr_category_title" id="tag-name" value="" size="40" required type="text">
									</div>
									<p class="submit">
										<input type="submit" class="button-primary" value="<?php _e( 'Add New Category', 'slider-bws' ) ?>" />
										<input type="hidden" name="sldr_category_submit" value="submit" />
										<?php wp_nonce_field( $plugin_basename, 'sldr_nonce_category_name' ); ?>
									</p>
								</form>
							</div><!-- .form-wrap -->
						</div><!-- #col-wrap -->
					</div><!-- #col-left -->
				</div><!-- #col-container -->
			<?php } ?>
		</div><!-- .wrap -->
	<?php }
}

/**
 * This function will render metabox for shortcode
 */
if ( ! function_exists( 'sldr_shortcode_metabox' ) ) {
	function sldr_shortcode_metabox( $item, $obj = '', $box = '' ) { 
		if ( ! empty( $_REQUEST['sldr_id'] ) ) {
			$sldr_id = intval( $_REQUEST['sldr_id'] );
		} ?>
		<div>
			<?php _e( "Add a slider to your posts, pages, custom post types or widgets by using the following shortcode:", 'slider-bws' );
			bws_shortcode_output( '[print_sldr id=' . $sldr_id . ']' ); ?>	
		</div>
	<?php }
}

/**
 * This function will render submit metabox
 */
if ( ! function_exists( 'sldr_submit_metabox' ) ) {
	function sldr_submit_metabox( $item ) { ?>
		<div class="sldr_submit">
			<div id="publishing-action">
				<span class="spinner"></span>
				<input id="publish" class="button button-primary button-large" name="sldr_publish" value="<?php echo ( isset( $_GET['sldr_id'] ) ) ? __( 'Update', 'slider-bws' ) : __( 'Publish', 'slider-bws' ); ?>" type="submit" />
				<input type="hidden" name="sldr_form_submit" value="submit" />
				<?php wp_nonce_field( plugin_basename( __FILE__ ), 'sldr_nonce_form_name' ); ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php }
}

/**
 * This function will render category metabox
 */
if ( ! function_exists( 'sldr_category_metabox' ) ) {
	function sldr_category_metabox( $item ) {
		global $wpdb, $sldr_id;

		if ( ! empty( $_REQUEST['sldr_id'] ) ) {
			$sldr_id = isset( $_REQUEST['sldr_id'] ) ? intval( $_REQUEST['sldr_id'] ) : "";
		}		
		/* Take all category ID from category table */
		$slider_categories = $wpdb->get_results( "SELECT `category_id`, `title` FROM `" . $wpdb->prefix . "sldr_category`", ARRAY_A ); ?>
		<div class="categorydiv">
			<ul id="sldr_categories-all" class="categorychecklist form-no-clear">
				<?php /* Take category for current slider */
				if ( ! empty( $slider_categories ) ) {
					/* Take current category ID from DB */
					if ( ! empty( $sldr_id ) ) {
						$slider_current_categories = $wpdb->get_col( $wpdb->prepare( "SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE `slider_id` = %d", $sldr_id ) );
					}
					/* Display all categories in metabox */
					foreach ( $slider_categories as $slider_category ) { ?>
						<li>
							<input type="checkbox" name="sldr_category_id[]" value="<?php echo $slider_category['category_id']; ?>"<?php if ( isset( $slider_current_categories ) && in_array( $slider_category['category_id'], $slider_current_categories ) ) echo 'checked="checked"'; ?> />
							<input id="sldr_hidden_checbox" type="hidden" value="<?php echo $slider_category['category_id']; ?>" name="sldr_category_unchecked_id[]">
							<?php echo esc_html( $slider_category['title'] ); ?>
						</li>
					<?php }
				} else { ?>
					<i><?php _e( 'No Slider Category Set', 'slider-bws' ); ?></i>
				<?php } ?>
			<ul>
		</div>
		<div id="category-adder" class="wp-hidden-children">
			<a id="category-add-toggle" href="admin.php?page=slider-categories.php" class="taxonomy-add-new">+ <?php _e( 'Add New Category', 'slider-bws' ); ?></a>
		</div>
	<?php }
}

/**
 * Slider general settings page.
 */
if ( ! function_exists( 'sldr_settings_page' ) ) {
	function sldr_settings_page() {
		require_once( dirname( __FILE__ ) . '/includes/class-sldr-settings.php' );
		$page = new Sldr_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
		<div class="wrap">
			<h1><?php _e( 'Slider Global Settings', 'slider-bws' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
	<?php }
}

/**
*	Add place for notice in media upoader for slider
*
*	See wp_print_media_templates() in "wp-includes/media-template.php"
*/
if ( ! function_exists( 'sldr_print_media_notice' ) ) {
	function sldr_print_media_notice() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'slider-new.php' ) {
			$image_info = '<# sldr_notice_view( data.id ); #><div id="sldr_media_notice" class="upload-errors"></div>'; ?>
			<script type="text/javascript">
				( function ($) {
					$( '#tmpl-attachment-details' ).html(
						$( '#tmpl-attachment-details' ).html().replace( '<div class="attachment-info"', '<?php echo $image_info; ?>$&' )
					);
				} )(jQuery);
			</script>
		<?php }
	}
}

/**
*	Add notises in media upoader for slider
*/
if ( ! function_exists( 'sldr_media_check_ajax_action' ) ) {
	function sldr_media_check_ajax_action() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'sldr_ajax_nonce_field' );

		if ( isset( $_POST['thumbnail_id'] ) ) {
			$thumbnail_id = intval( $_POST['thumbnail_id'] );
			/*get information about the selected item */
			$atachment_detail = get_post( $thumbnail_id );
			if ( ! empty( $atachment_detail ) ) {
				if ( ! ( preg_match( '!^image/!', $atachment_detail->post_mime_type ) || preg_match( '!^video/mp4!', $atachment_detail->post_mime_type ) || preg_match( '!^video/ogg!', $atachment_detail->post_mime_type ) || preg_match( '!^video/webm!', $atachment_detail->post_mime_type ) ) ) {

					$notice_attach = "<div class='upload-error'><strong>" . __( 'Warning', 'slider-bws' ) . ": </strong>" . __( 'You can add only images or video (in format: MP4, WebM or Ogg) to the slider.', 'slider-bws' ) . "</div>";
					wp_send_json_success( $notice_attach );
				}
			}
		}
		die();
	}
}

/**
 *	Delete slide by click on "Delete" button.
 */
if ( ! function_exists( 'sldr_delete_image' ) ) {
	function sldr_delete_image() {
		global $wpdb;
		check_ajax_referer( plugin_basename( __FILE__ ), 'sldr_ajax_nonce_field' );

		$action				= isset( $_POST['action'] ) ? $_POST['action'] : "";
		$delete_id_array	= isset( $_POST['delete_id_array'] ) ? $_POST['delete_id_array'] : "";
		$slider_id			= $_POST['slider_id'];

		if ( $action == 'sldr_delete_image' && ! empty( $delete_id_array ) && ! empty( $slider_id ) ) {
			$delete_ids = explode( ',', trim( $delete_id_array, ',' ) );

			foreach ( $delete_ids as $delete_id ) {
				$wpdb->delete( $wpdb->prefix . 'sldr_relation',
					array(
						'slider_id' => $slider_id,
						'attachment_id' => $delete_id
					)
				);
			}
			echo 'updated';
		}
		die();
	}
}


/**
 *	Add new media in slider from AJAX.
 */
if ( ! function_exists( 'sldr_add_from_media' ) ) {
	function sldr_add_from_media() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'sldr_ajax_add_nonce' );

		$action				= isset( $_POST['action'] ) ? $_POST['action'] : "";
		$add_id				= isset( $_POST['add_id'] ) ? intval( $_POST['add_id'] ) : "";

		if ( 'sldr_add_from_media' == $action && ! empty( $add_id ) ) {
			$post = get_post( $add_id );
			if ( ! empty( $post ) ) {
				if ( preg_match( '!^image/!', $post->post_mime_type ) || preg_match( '!^video/mp4!', $post->post_mime_type ) || preg_match( '!^video/ogg!', $post->post_mime_type ) || preg_match( '!^video/webm!', $post->post_mime_type ) ) {
					$GLOBALS['hook_suffix'] = 'slider';
					$wp_slider_media_table = new Sldr_Media_Table();
					$wp_slider_media_table->prepare_items();
					$wp_slider_media_table->single_row( $post );
				}
			}
		}
		die();
	}
}

/**
 *	Add slider shortcode to BWS plugin shortcode menu.
 */
if ( ! function_exists( 'sldr_shortcode_button_content' ) ) {
	function sldr_shortcode_button_content( $content ) {
		global $wpdb; ?>
		<div id="sldr" style="display:none;">
			<fieldset>
				<label for="sldr_shortcode_list">
					<?php $slider_id_array = $wpdb->get_col( "SELECT `slider_id` FROM `" . $wpdb->prefix . "sldr_slider`" );
					if ( ! empty( $slider_id_array ) ) { ?>
						<input type="radio" name="sldr_type" class="sldr_radio_shortcode_list" checked="checked">
						<span class="title"><?php _e( 'slider', 'slider-bws' ); ?></span>
						<select name="sldr_list" id="sldr_shortcode_list" style="max-width: 350px;">
							<?php foreach ( $slider_id_array as $slider_id ) {
								/* Get slider title from DB */
								$slider_title = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $slider_id ) );
								/* If slider don't have title, display "no title" */
								if ( empty( $slider_title ) )
									$slider_title = '(' . __( 'no title', 'slider-bws' ) . ')';
								/* Get slider date from DB */
								$slider_date = $wpdb->get_var( $wpdb->prepare( "SELECT `datetime` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $slider_id ) ); ?>
								<option value="<?php echo $slider_id; ?>"><?php echo $slider_title; ?>(<?php echo $slider_date; ?>)</option>
							<?php } ?>
						</select>
					<?php } else { ?>
						<span class="title"><?php _e( 'Sorry, no slider found.', 'slider-bws' ); ?></span>
					<?php } ?>
				</label>
				<br/>
				<label for="sldr_category_shortcode_list">
					<?php /* Get category ID from DB */
					$slider_category_id_array = $wpdb->get_col( "SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_category`" );
					if ( ! empty( $slider_category_id_array ) ) { ?>
						<input type="radio" name="sldr_type" class="sldr_radio_category_list">
						<span class="title"><?php _e( 'slider category', 'slider-bws' ); ?></span>
						<select name="sldr_category_list" id="sldr_category_shortcode_list" style="max-width: 350px;">
							<?php 
							foreach ( $slider_category_id_array as $slider_category_id ) {
								/* Get slider category title from DB */
								$slider_category_title = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "sldr_category` WHERE `category_id` = %d", $slider_category_id ) ); ?>
								<option value="<?php echo $slider_category_id; ?>"><?php echo $slider_category_title; ?></option>
							<?php unset ( $slider_category_id );
							} /* end foreach */ ?>
						</select>
					<?php } else { ?>
						<span class="title"><?php _e( 'Sorry, no sliders categories found.', 'slider-bws' ); ?></span>
					<?php } ?>
				</label>
			</fieldset>
			<?php foreach ( $slider_id_array as $slider_id ) {
				echo '<input class="bws_default_shortcode" type="hidden" name="default" value="[print_sldr id=' . $slider_id . ']" />';
			} ?>
			<script type="text/javascript">
				function sldr_shortcode_init() {
					( function( $ ) {
						$( '.mce-reset #sldr_shortcode_list, .mce-reset #sldr_display_short, .mce-reset .sldr_radio_shortcode_list' ).on( 'click', function() {
							var sldr_list = $( '.mce-reset #sldr_shortcode_list option:selected' ).val();
							var shortcode = '[print_sldr id=' + sldr_list + ']';
							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						});

						$( '.mce-reset #sldr_category_shortcode_list, .mce-reset .sldr_radio_category_list' ).on( 'click', function() {
							var sldr_category_list = $( '.mce-reset #sldr_category_shortcode_list option:selected' ).val();
							var shortcode = '[print_sldr cat_id=' + sldr_category_list + ']';
							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						});

						$( '[name="sldr_type"]' ).on( 'click', function() {
							$( this ).parent().find( 'select' ).focus();
						} );

						$( '#sldr_shortcode_list, #sldr_category_shortcode_list' ).on( 'focus', function() {
							$( this ).parent().find( '[type="radio"]' ).attr( 'checked', true );
						} );
					} )(jQuery);
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/**
 *	Shortcodes content output function
 */
if ( ! function_exists ( 'sldr_shortcode' ) ) {
	function sldr_shortcode( $attr ) { 
		global $wpdb, $sldr_options;

		extract( shortcode_atts( array(
			'id'		=>	'',
			'cat_id'	=>	''
			), $attr )
		);

		ob_start();

		if ( empty( $sldr_options ) )
			$sldr_options = get_option( 'sldr_options' );

		/* Get slider ID by categories*/
		/* Check: if this category exists in DB */
		$slider_category_id = $wpdb->get_var( $wpdb->prepare( "SELECT `category_id` FROM `" . $wpdb->prefix . "sldr_category` WHERE `category_id` = %d", $cat_id  ) );

		if ( ! empty( $cat_id ) && isset( $slider_category_id  ) ) {
			$slider_categories_ids = $wpdb->get_col( $wpdb->prepare( "SELECT `slider_id` FROM `" . $wpdb->prefix . "sldr_relation` WHERE `category_id` = %d", $cat_id ) );
		}

		/* Get media ID for slider shortcode */
		if ( ! empty( $id ) ) {
			$slider_attachment_ids = $wpdb->get_col( $wpdb->prepare( 
				"SELECT `" . $wpdb->prefix . "sldr_slide`.`attachment_id` 
				FROM `" . $wpdb->prefix . "sldr_relation` INNER JOIN `" . $wpdb->prefix . "sldr_slide`
				WHERE `" . $wpdb->prefix . "sldr_slide`.`attachment_id` = `" . $wpdb->prefix . "sldr_relation`.`attachment_id`
					AND `" . $wpdb->prefix . "sldr_relation`.`attachment_id` IS NOT NULL 
					AND `" . $wpdb->prefix . "sldr_relation`.`slider_id` = %d
				ORDER BY `" . $wpdb->prefix . "sldr_slide`.`order` ASC",
				$id  
			) );
		}

		/* Get slider settings */
		$slider_single_setting = $wpdb->get_var( $wpdb->prepare( "SELECT `settings` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $id ) );
		$slider_single_settings = unserialize( $slider_single_setting );

		/* If this shortcode with slider ID */
		if ( ! empty( $slider_attachment_ids ) ) { ?>
			<script type="text/javascript">
				( function($) {
					$( document ).ready( function() {
						var slider_single_settings = '<?php echo json_encode( $slider_single_settings ); ?>';
						slider_single_settings 	= JSON.parse( slider_single_settings );

						var slider_options 		= '<?php echo json_encode( $sldr_options ); ?>';
						slider_options 			= JSON.parse( slider_options );

						var id = <?php echo json_encode( $id ); ?>;

						if ( $( 'body' ).hasClass( 'rtl' ) ) {
							$( '.sldr_carousel_' + id  ).owlCarousel( {
								loop: 				slider_single_settings.loop,
								nav: 				slider_single_settings.nav,
								dots: 				slider_single_settings.dots,
								items: 				slider_single_settings.items,
								smartSpeed: 		450,
								autoplay: 			slider_single_settings.autoplay,
								autoplayTimeout: 	slider_single_settings.autoplay_timeout,
								autoplayHoverPause: slider_single_settings.autoplay_hover_pause,
								center: 			true,
								lazyLoad: 			slider_options.lazy_load,
								autoHeight: 		slider_options.auto_height,
								navText:[
													"<i class='dashicons dashicons-arrow-left-alt2'></i>",
													"<i class='dashicons dashicons-arrow-right-alt2'></i>"
								],
								rtl: true
							} );
						} else {
							$( '.sldr_carousel_' + id  ).owlCarousel({
								loop: 				slider_single_settings.loop,
								nav: 				slider_single_settings.nav,
								dots: 				slider_single_settings.dots,
								items: 				slider_single_settings.items,
								smartSpeed: 		450,
								autoplay: 			slider_single_settings.autoplay,
								autoplayTimeout: 	slider_single_settings.autoplay_timeout,
								autoplayHoverPause: slider_single_settings.autoplay_hover_pause,
								center: 			true,
								lazyLoad: 			slider_options.lazy_load,
								autoHeight: 		slider_options.auto_height,
								navText:[
													"<i class='dashicons dashicons-arrow-left-alt2'></i>",
													"<i class='dashicons dashicons-arrow-right-alt2'></i>"
								]
							});
						}
					});
				}) (jQuery);
			</script>
			<?php /* Display images and images attributes from slider. */
			echo '<div class="sldr_wrapper"><div class="owl-carousel owl-theme sldr_carousel_' . $id . '">';

				foreach ( $slider_attachment_ids as $slider_attachment_id ) {
					/* Get slides properties */
					$slide_attribute = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "sldr_slide` WHERE `attachment_id` = %d", $slider_attachment_id ) );
			
					/* Slide title */
					$slide_title 		= $slide_attribute->title;
					/* Slide description */
					$slide_description 	= $slide_attribute->description;
					/* Slide url */
					$slide_url 			= $slide_attribute->url;
					/* Slide button text */
					$slide_button_text 	= $slide_attribute->button;
					/* Get image for media ID */
					$slider_attachment	= wp_get_attachment_url( $slider_attachment_id, 'full' );

					echo '<div class="sldr_textoverlay">';

						if ( ! empty( $slide_title ) ) {
							/* Display slides title if exist */
							echo '<h2>' . $slide_title  . '</h2>';
						}
						if ( ! empty( $slide_description ) ) {
							/* Display slides description if exist */
							echo '<p>' . $slide_description . '</p>';
						}
						if ( ! empty( $slide_button_text ) && ! empty( $slide_url ) ) {
							/* Display slides button if exist */
							echo '<a href="' . $slide_url . '">' . $slide_button_text . '</a>';
						}

						if ( wp_attachment_is_image( $slider_attachment_id ) && $sldr_options['lazy_load'] ) {
							/* If attachment is image and lazy load on */
							echo '<img class="owl-lazy" data-src="' . $slider_attachment . '" />';
						} elseif( wp_attachment_is_image( $slider_attachment_id ) ) {
							/* If attachment is image */
							echo '<img src="' . $slider_attachment . '" />';
						} else {
							/* Else attachment is video */
							echo '<video src="' . $slider_attachment . '" controls></video>';
						}

					echo '</div>';
				}
			echo '</div></div>';

		/* If this shortcode with slider category ID */
		} elseif ( ! empty( $slider_categories_ids ) ) {

			/* Get slider settings */
			$slider_category_setting 	= $wpdb->get_var( $wpdb->prepare( "SELECT `settings` FROM `" . $wpdb->prefix . "sldr_slider` WHERE `slider_id` = %d", $slider_categories_ids[0] ) ); /* Get options of first slider */
			$slider_category_settings	= unserialize( $slider_category_setting ); ?>
			<script type="text/javascript">
				( function( $ ) {
					$( document ).ready( function() {
						var slider_options = '<?php echo json_encode( $sldr_options ); ?>';
						slider_options = JSON.parse( slider_options );

						var slider_category_settings = '<?php echo json_encode( $slider_category_settings ); ?>';
						slider_category_settings = JSON.parse( slider_category_settings );

						var cat_id = <?php echo json_encode( $cat_id ); ?>;

						$( '.sldr_cat_carousel_'+ cat_id ).find( '.owl-item' );

						if ( $( 'body' ).hasClass( 'rtl' ) ) {
							$( '.sldr_cat_carousel_' + cat_id ).owlCarousel( {
								loop: 				slider_category_settings.loop,
								nav: 				slider_category_settings.nav,
								dots: 				slider_category_settings.dots,
								items: 				slider_category_settings.items,
								autoplay: 			slider_category_settings.autoplay,
								autoplayTimeout: 	slider_category_settings.autoplay_timeout,
								autoplayHoverPause: slider_category_settings.autoplay_hover_pause,
								center: 			true,
								smartSpeed:			450,
								lazyLoad:			slider_options.lazy_load,
								autoHeight: 		slider_options.auto_height,
								navText:[
													"<i class='dashicons dashicons-arrow-left-alt2'></i>",
													"<i class='dashicons dashicons-arrow-right-alt2'></i>"
								],									
								rtl: true
							} );
						} else {
							$( '.sldr_cat_carousel_' + cat_id ).owlCarousel( {
								loop: 				slider_category_settings.loop,
								nav: 				slider_category_settings.nav,
								dots: 				slider_category_settings.dots,
								items: 				slider_category_settings.items,
								autoplay: 			slider_category_settings.autoplay,
								autoplayTimeout: 	slider_category_settings.autoplay_timeout,
								autoplayHoverPause: slider_category_settings.autoplay_hover_pause,
								center: 			true,
								smartSpeed:			450,
								lazyLoad:			slider_options.lazy_load,
								autoHeight: 		slider_options.auto_height,
								navText:[
													"<i class='dashicons dashicons-arrow-left-alt2'></i>",
													"<i class='dashicons dashicons-arrow-right-alt2'></i>"
								]
							} );
						}
					} );
				} ) (jQuery);
			</script>

			<?php echo '<div class="sldr_wrapper"><div class="sldr_cat_carousel_' . $cat_id . ' owl-carousel owl-theme">';

			foreach ( $slider_categories_ids as $slider_categories_id ) {

				/* Get media ID for slider category shortcode */
				if ( ! empty( $slider_categories_id ) ) {
					$slider_attachment_ids 	= $wpdb->get_col( $wpdb->prepare( 
						"SELECT `" . $wpdb->prefix . "sldr_slide`.`attachment_id` 
						FROM `" . $wpdb->prefix . "sldr_relation` INNER JOIN `" . $wpdb->prefix . "sldr_slide`
						WHERE `" . $wpdb->prefix . "sldr_slide`.`attachment_id` = `" . $wpdb->prefix . "sldr_relation`.`attachment_id`
							AND `" . $wpdb->prefix . "sldr_relation`.`attachment_id` IS NOT NULL 
							AND `" . $wpdb->prefix . "sldr_relation`.`slider_id` = %d
						ORDER BY `" . $wpdb->prefix . "sldr_slide`.`order` ASC",
						$slider_categories_id
					) );
				}

				/* If slider have image */
				if ( ! empty( $slider_attachment_ids  ) ) {
					
					foreach ( $slider_attachment_ids as $slider_attachment_id ) {
						/* Get slides properties */
						$slide_attribute = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "sldr_slide` WHERE `attachment_id` = %d", $slider_attachment_id ) );
						/* Get image for media ID */
						$slider_attachment = wp_get_attachment_url( $slider_attachment_id, 'full' );

						echo '<div class="sldr_textoverlay">';
						if ( ! empty( $slide_attribute->title ) ) {
							/* Display slides title if exist */
							echo '<h2>' . $slide_attribute->title . '</h2>';
						}
						if ( ! empty( $slide_attribute->description ) ) {
							/* Display slides description if exist */
							echo '<p>' . $slide_attribute->description . '</p>';
						}
						if ( ! empty( $slide_attribute->button ) && ! empty( $slide_attribute->url ) ) {
							/* Display slides button if exist */
							echo '<a href="' . $slide_attribute->url . '">' . $slide_attribute->button . '</a>';
						}
						if ( wp_attachment_is_image( $slider_attachment_id ) && $sldr_options['lazy_load'] ) {
							/* If attachment is image and lazy load on */
							echo '<img class="owl-lazy" data-src="' . $slider_attachment . '" />';
						} elseif( wp_attachment_is_image( $slider_attachment_id ) ) {
							/* If attachment is image */
							echo '<img src="' . $slider_attachment . '" />';
						} else {
							/* Else attachment is video */
							echo '<video src="' . $slider_attachment . '" controls></video>';
						}
						echo '</div>';
					}
				}
			}
			echo '</div></div>';
		/* If nothing found. */
		} else {
			echo '<div class="sldr_wrapper"><p class="not_found">' . __( 'Sorry, nothing found.', 'slider-bws' ) . '</p></div>';
		}
		$slider_output = ob_get_clean();
		return $slider_output;
	}
}

/**
 * Add style to dashboard
 */
if ( ! function_exists ( 'sldr_admin_head' ) ) {
	function sldr_admin_head() {
		wp_enqueue_style( 'sldr_stylesheet_icon', plugins_url( '/css/style-icon.css', __FILE__ ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'slider.php' ) {
			wp_enqueue_style( 'sldr_stylesheet', plugins_url( '/css/style.css', __FILE__ ) );
		} else if ( isset( $_GET['page'] ) && $_GET['page'] == 'slider-new.php' ) {
			wp_enqueue_style( 'editor-buttons' );
			wp_enqueue_style( 'sldr_stylesheet', plugins_url( '/css/style.css', __FILE__ ) );
			
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_media();			
			add_thickbox();
			
			wp_enqueue_script( 'sldr_script', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'jquery' ) );
			
			/* Plugin script */
			wp_localize_script( 'sldr_script', 'sldr_vars',
				array(
					'sldr_nonce'				=> wp_create_nonce( plugin_basename( __FILE__ ), 'sldr_ajax_nonce_field' ),
					'sldr_add_nonce'			=> wp_create_nonce( plugin_basename( __FILE__ ), 'sldr_ajax_add_nonce' ),
					'warnBulkDelete'			=> __( "You are about to remove these items from this slider.\n 'Cancel' to stop, 'OK' to delete.", 'slider-bws' ),
					'warnSingleDelete'			=> __( "You are about to remove this item from the slider.\n 'Cancel' to stop, 'OK' to delete.", 'slider-bws' ),
					'wp_media_title'			=> __( 'Insert Media', 'slider-bws' ),
					'wp_media_button'			=> __( 'Insert', 'slider-bws' ),
					'no_items'					=> __( 'No images found', 'slider-bws' )
				)
			);

			bws_enqueue_settings_scripts();
		} elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'slider-settings.php' ) {
			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}
	}
}

/**
 * List of JavaScript / CSS files
 * @return void
 */
if ( ! function_exists( 'sldr_register_scripts' ) ) {
	function sldr_register_scripts() {				
		/* Owl carousel style */
		wp_enqueue_style( 'owl.carousel.css', plugins_url( '/css/owl.carousel.css', __FILE__ ) );
		wp_enqueue_style( 'owl.theme.default.css', plugins_url( '/css/owl.theme.default.css', __FILE__ ) );
		/* Include dashicons */
		wp_enqueue_style( 'dashicons' );
		/* Plugin style */
		wp_enqueue_style( 'sldr_stylesheet', plugins_url( '/css/frontend_style.css', __FILE__ ) );
		/* Include jquery */
		wp_enqueue_script( 'jquery' );
		/* Slider script */
		wp_enqueue_script( 'owl.carousel.js', plugins_url( '/js/owl.carousel.js', __FILE__ ) );
	}
}

/**
 * Function to display table screen options.
 */
if ( ! function_exists ( 'sldr_cat_screen_options' ) ) {
	function sldr_cat_screen_options() {
		sldr_add_tabs();
		$args = array(
			'label'		=> __( 'Category per page', 'slider-bws' ),
			'default'	=> 10,
			'option'	=> 'sldr_cat_per_page',
		);
		add_screen_option( 'per_page', $args );
	}
}

if ( ! function_exists ( 'sldr_screen_options' ) ) {
	function sldr_screen_options() {
		sldr_add_tabs();
		$args = array(
			'label'		=> __( 'Sliders per page', 'slider-bws' ),
			'default'	=> 10,
			'option'	=> 'sldr_per_page',
		);
		add_screen_option( 'per_page', $args );
	}
}

/**
 * Function to set up table screen options.
 */
if ( ! function_exists ( 'sldr_set_screen_options' ) ) {
	function sldr_set_screen_options( $status, $option, $value ) {
		if ( 'sldr_cat_per_page' == $option || 'sldr_per_page' == $option ) {
			return $value;
		}
		return $status;
	}
}

/**
 * Add links to plugin.
 */
if ( ! function_exists( 'sldr_register_plugin_links' ) ) {
	function sldr_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=slider-settings.php">' . __( 'Settings', 'slider-bws' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/slider-bws/faq/" target="_blank">' . __( 'FAQ', 'slider-bws' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'slider-bws' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Add links to plugin.
 */
if ( ! function_exists( 'sldr_plugin_action_links' ) ) {
	function sldr_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=slider-settings.php">' . __( 'Settings', 'slider-bws' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

/**
 * Add BWS notice to plugin page.
 */
if ( ! function_exists ( 'sldr_admin_notices' ) ) {
	function sldr_admin_notices() {
		global $hook_suffix, $sldr_plugin_info, $sldr_options;

		if ( 'plugins.php' == $hook_suffix || ( isset( $_GET['page'] ) && $_GET['page'] == 'slider-settings.php' ) ) {
			if ( 'plugins.php' == $hook_suffix ) {
				if ( isset( $sldr_options['first_install'] ) && strtotime( '-1 week' ) > $sldr_options['first_install'] )
					bws_plugin_banner_to_settings( $sldr_plugin_info, 'sldr_options', 'slider', 'admin.php?page=slider-settings.php', 'admin.php?page=slider-new.php' );
			} else {
				bws_plugin_suggest_feature_banner( $sldr_plugin_info, 'sldr_options', 'slider' );
			}
		}
	}
}

/**
 * Add help tab to plugins page.
 */
if ( ! function_exists( 'sldr_add_tabs' ) ) {
	function sldr_add_tabs() {
		$screen = get_current_screen();
		$args 	= array(
			'id' 		=> 'sldr',
			'section' 	=> '115000726446'
		);
		bws_help_tab( $screen, $args );
	}
}

/**
 * Perform at uninstall
 */
if ( ! function_exists( 'sldr_plugin_uninstall' ) ) {
	function sldr_plugin_uninstall() {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				$wpdb->query( "DROP TABLE IF EXISTS  `" . $wpdb->prefix . "sldr_slider`, `" . $wpdb->prefix . "sldr_slide`, `" . $wpdb->prefix . "sldr_relation`, `" . $wpdb->prefix . "sldr_category` " );
				delete_option( 'sldr_options' );				
			}
			switch_to_blog( $old_blog );
		} else {
			$wpdb->query( "DROP TABLE IF EXISTS  `" . $wpdb->prefix . "sldr_slider`, `" . $wpdb->prefix . "sldr_slide`, `" . $wpdb->prefix . "sldr_relation`, `" . $wpdb->prefix . "sldr_category` " );
			delete_option( 'sldr_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'sldr_plugin_activate' );

add_action( 'admin_menu', 'sldr_add_admin_menu' );

add_action( 'init', 'sldr_init' );
add_action( 'admin_init', 'sldr_admin_init' );

add_action( 'plugins_loaded', 'sldr_plugins_loaded' );

add_action( 'admin_enqueue_scripts', 'sldr_admin_head' );
add_action( 'wp_enqueue_scripts', 'sldr_register_scripts' );

/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'sldr_register_plugin_links', 10, 2 );
add_filter( 'plugin_action_links', 'sldr_plugin_action_links', 10, 2 );

add_action( 'admin_notices', 'sldr_admin_notices' );

add_filter( 'set-screen-option', 'sldr_set_screen_options', 10, 3 );

/*	Add place for notice in media upoader for portfolio	*/
add_action( 'print_media_templates', 'sldr_print_media_notice', 11 );
/*	Add notises in media upoader for slider	*/
add_action( 'wp_ajax_sldr_media_check', 'sldr_media_check_ajax_action' );

add_action( 'wp_ajax_sldr_delete_image', 'sldr_delete_image' );
add_action( 'wp_ajax_sldr_add_from_media', 'sldr_add_from_media' );

add_filter( 'bws_shortcode_button_content', 'sldr_shortcode_button_content' );

add_shortcode( 'print_sldr', 'sldr_shortcode' );

add_filter( 'widget_text', 'do_shortcode' );