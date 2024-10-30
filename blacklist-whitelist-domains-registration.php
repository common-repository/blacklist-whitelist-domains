<?php
/**
 * Plugin Name: Blacklist & Whitelist Domains
 * Description: A simple plugin that helps you to allow or disallow specific domains for registration on your site. You have option to save the blacklisted domains registration.
 * Version: 1.0
 * Tags: blacklist, whitelist, domain, email, registraion, spam registration
 * Author: codicone
 * Author URI: https://codicone.com
 * Text Domain: bwdr-registration
 * Domain Path: /languages
 * License: GPLv2
 */

if ( ! class_exists( 'bwdr_domain_registration' ) ) :
	final class bwdr_domain_registration {

		// Plugin Version
		public $version             = '1.0';

		// Instnace
		protected static $_instance = NULL;

		/**
		 * Setup Instance
		 * @since 1.0
		 * @version 1.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __clone() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Define
		 * @since 1.0
		 * @version 1.0
		 */
		private function define( $name, $value, $definable = true ) {
			if ( ! defined( $name ) )
				define( $name, $value );
			elseif ( ! $definable && defined( $name ) )
				_doing_it_wrong( 'bwdr_domain_registration->define()', 'Could not define: ' . $name . ' as it is already defined somewhere else!', $this->version );
		}

		/**
		 * Require File
		 * @since 1.0
		 * @version 1.0
		 */
		public function file( $required_file ) {
			if ( file_exists( $required_file ) )
				require_once $required_file;
			else
				_doing_it_wrong( 'bwdr_domain_registration->file()', 'Requested file ' . $required_file . ' not found.', $this->version );
		}

		/**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
		public function __construct() {
			register_activation_hook(__FILE__, array( $this, 'create_log_table_in_db' ) );
			$this->define_constants();
			$this->load_module();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) ); 
			add_action( 'init', array( $this, 'add_translation' ) );
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'plugin_page_settings_link') );
			
		}

		/**
		 * Plugins page settings link
		 * @since 1.0
		 * @version 1.0
		**/
		public function plugin_page_settings_link( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=user-registration-options' ) . '">' . __('Settings', 'bwdr-registration') . '</a>';
	
			return $links;

		}

		/**
		 * Insert DB table for logs
		 * @since 1.0
		 * @version 1.0
		**/
		public function create_log_table_in_db() {
			global $wpdb;
			// set the default character set and collation for the table
			$charset_collate = $wpdb->get_charset_collate();
			// Check that the table does not already exist before continuing
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}bwdr_log` (
			id bigint(50) NOT NULL AUTO_INCREMENT,
			registration_time varchar(100),
			user_name varchar(100),
			user_email varchar(100),
			user_data varchar(255),
			PRIMARY KEY (id)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			//$is_error = empty( $wpdb->last_error );

		}

		/**
		 * Add translation support
		 * @since 1.0
		 * @version 1.0
		**/
		public function add_translation() {
			load_plugin_textdomain('bwdr-registration', FALSE,  basename( dirname( __FILE__ ) ) . '/languages/');
		}
		
		/**
		 * Enqueue related admin scripts
		 * @since 1.0
		 * @version 1.0
		**/
		public function admin_scripts() {
			wp_enqueue_style( 'bwdr-admin-style', plugins_url('assets/css/bwdr-admin-style.css',__FILE__ ), array(), $this->version );
			wp_register_script( 'bwdr-admin-script', plugins_url('assets/js/bwdr-admin-script.js',__FILE__ ), array('jquery'), $this->version );
			wp_enqueue_script( 'bwdr-admin-script' );
		}

		/**
		 * Define Constants
		 * @since 1.0
		 * @version 1.0
		 */
		private function define_constants() {

			$this->define( 'bwdr_VERSION',        $this->version );
			$this->define( 'bwdr_SLUG',          'user-registration-options' );

			$this->define( 'URO',               __FILE__ );
			$this->define( 'bwdr_ROOT',          plugin_dir_path( URO  ) );
			$this->define( 'bwdr_INCLUDE', plugin_dir_path( URO ) . 'inc/' );
			$this->define( 'bwdr_ASSETS', plugin_dir_path( URO)  . 'assets/' );
						
		}
		
		/**
		 * Load Module
		 * @since 1.0
		 * @version 1.0
		 */
		public function load_module() {
			$bwdr_settings = get_option( 'bwdr_settings' );
			$this->file( bwdr_INCLUDE . 'bwdr-settings.php' );

			if (isset($bwdr_settings['enable']) && 'yes' == $bwdr_settings['enable']) {
				
				$this->file( bwdr_INCLUDE . 'bwdr-approval.php' );

				if (isset($bwdr_settings['enable_log']) && 'yes' == $bwdr_settings['enable_log']) {
					$this->file( bwdr_INCLUDE . 'bwdr-log.php' );
				}
			}

			
			
		}

	}
endif;

function bwdr_plugin() {
	return bwdr_domain_registration::instance();
}
//add_action( 'init', 'bwdr_plugin' );
bwdr_plugin();