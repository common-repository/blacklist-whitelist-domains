<?php

if ( ! class_exists( 'bwdr_settings' ) ) :
    class bwdr_settings {

        /**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
        public function __construct() {

            add_action( 'admin_menu',  array( $this, 'admin_menu_page') );
            add_action( 'admin_init', array( $this, 'bwdr_settings_init') );
        }


        /**
		 * Admin menu
		 * @since 1.0
		 * @version 1.0
		 */
        public function admin_menu_page() {
        
            add_menu_page( __('User Registration Options', 'bwdr-registration' ), __('Domains for Registration', 'bwdr-registration' ), 'manage_options', 'user-registration-options', array($this, 'bwdr_settings_callback'), 'dashicons-welcome-widgets-menus', 90 );
        
        }

        /**
		 * Settings Page Content
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_settings_callback() {
            ?>
            <h1> <?php esc_html_e( 'User Registration Options', 'bwdr-registration' ); ?> </h1>
            <form method="POST" action="options.php">
            <?php
            settings_fields( 'bwdr-settings-group' );
            do_settings_sections( 'bwdr-settings' );
            submit_button();
            ?>
            </form>
            <?php
        }

        /**
		 * Setting fields
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_settings_init() {
            add_settings_section(
                'bwdr_setting_section',
                __( 'Plugin Settings', 'bwdr-registration' ),
                array($this, 'bwdr_setting_section_callback_function'),
                'bwdr-settings'
            );
        
            add_settings_field(
                'bwdr_enable_setting',
                 __( 'Enable Plugin Functionality', 'bwdr-registration' ),
                array($this, 'bwdr_enable_markup'),
                'bwdr-settings',
                'bwdr_setting_section'
            );

            add_settings_field(
                'bwdr_whitelisted_domains',
                 __( 'Whitelisted Domains', 'bwdr-registration' ),
                array($this, 'bwdr_whitelisted_domains_markup'),
                'bwdr-settings',
                'bwdr_setting_section'
            );

            add_settings_field(
                'bwdr_blacklisted_domains',
                 __( 'Blacklisted Domains', 'bwdr-registration' ),
                array($this, 'bwdr_blacklisted_domains_markup'),
                'bwdr-settings',
                'bwdr_setting_section'
            );

            add_settings_field(
                'bwdr_blacklisted_error',
                 __( 'Error Message for Blacklisted Domains', 'bwdr-registration' ),
                array($this, 'bwdr_blacklisted_error_markup'),
                'bwdr-settings',
                'bwdr_setting_section'
            );

            add_settings_field(
                'bwdr_blacklisted_log',
                 __( 'Save Blacklisted Log', 'bwdr-registration' ),
                array($this, 'bwdr_blacklisted_log_markup'),
                'bwdr-settings',
                'bwdr_setting_section'
            );
        
                register_setting( 'bwdr-settings-group', 'bwdr_settings',  array($this, 'bwdr_sanitize') );
        }

        function bwdr_setting_section_callback_function() {
            // heading or any other info
        }
        
        function bwdr_enable_markup() {
            $bwdr_settings = get_option( 'bwdr_settings' );

            if ( !isset($bwdr_settings['enable']) ) {
                $bwdr_settings['enable'] = 'no';
            }

            ?>
            <div class="bwdr-admin-fields">
                <input id="bwdr_enable" name="bwdr_settings[enable]" type="checkbox" value="yes" <?php echo esc_html( checked( $bwdr_settings['enable'], 'yes', false ) ); ?> />
            </div>
            <?php
        }

        function bwdr_whitelisted_domains_markup() {
            $bwdr_settings = get_option( 'bwdr_settings' );
            ?>
            <div class="bwdr-admin-fields">
                <textarea id="bwdr_whitelisted" name="bwdr_settings[whitelisted]" class="bwdr-textarea bwdr-whitelisted"><?php echo esc_html( $bwdr_settings['whitelisted'] ); ?></textarea>
                <p class="description"><?php  _e( 'Enter one domain per line.', 'bwdr-registration' ); ?> </p><br /><br />
            </div>
            <?php
        }

        function bwdr_blacklisted_domains_markup() {
            $bwdr_settings = get_option( 'bwdr_settings' );
            ?>
            <div class="bwdr-admin-fields">
                <textarea id="bwdr_blacklisted" name="bwdr_settings[blacklisted]" class="bwdr-textarea bwdr-blacklisted"><?php echo esc_html( $bwdr_settings['blacklisted'] ); ?></textarea>
                <p class="description"><?php  _e( 'Enter one domain per line.', 'bwdr-registration' ); ?> </p><br /><br />
            </div>
            <?php
        }

        function bwdr_blacklisted_error_markup() {
            $bwdr_settings = get_option( 'bwdr_settings' );
            ?>
            <div class="bwdr-admin-fields">
                <input id="bwdr_blacklisted_err" class="bwdr-input-field bwdr-blacklisted-err" name="bwdr_settings[blacklisted_err]" size="40" type="text" value="<?php echo esc_html( $bwdr_settings['blacklisted_err'] ); ?>" />
            </div>    
            <?php
        }

        function bwdr_blacklisted_log_markup() {
            $bwdr_settings = get_option( 'bwdr_settings' );

            if ( !isset($bwdr_settings['enable_log']) ) {
                $bwdr_settings['enable_log'] = 'no';
            }
            ?>
            <div class="bwdr-admin-fields">
                <input id="bwdr_enable" name="bwdr_settings[enable_log]" type="checkbox" value="yes" <?php echo esc_html( checked( $bwdr_settings['enable_log'], 'yes', false ) ); ?> />
            </div>
            <?php
        }
    }

    new bwdr_settings();
endif;
