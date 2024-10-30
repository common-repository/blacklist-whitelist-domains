<?php

if ( ! class_exists( 'bwdr_approval' ) ) :
    class bwdr_approval {

        /**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
        public function __construct() {

            add_filter( 'registration_errors',array( $this, 'bwdr_blacklisted_domain'), 7, 3 ); // Default WP
            add_filter( 'woocommerce_process_registration_errors',array( $this, 'bwdr_woo_blacklisted_domain'),11,4); // woocommerce
            add_filter( 'woocommerce_registration_auth_new_customer', '__return_false'); // woocommerce disable auto login
            add_filter( 'user_registration_response_array', array( $this,'bwdr_ur_blacklisted_domain'), 10, 3); // user registration
            add_action( 'um_submit_form_errors_hook__registration',array( $this, 'bwdr_um_blacklisted_domain'), 35 ); // ultimate member
            add_action( 'um_registration_after_auto_login',array( $this, 'bwdr_um_disable_auto_login'), 35 ); // ultimate member disable auto login
            add_action( 'wppb_output_field_errors_filter',array( $this, 'bwdr_wppb_blacklisted_domain'), 10, 4 ); // Profile Builder
            add_action( 'wpuf_process_registration_errors',array( $this, 'bwdr_wpuf_blacklisted_domain'), 10, 4 ); // WP User Frontend
            

            add_action( 'user_register', array( $this, 'bwdr_user_status' ) ); 
            add_filter( 'wp_authenticate_user', array( $this, 'bwdr_authenticate_user' ) );
            add_filter( 'bwdr_default_user_status', array( $this, 'bwdr_allow_whitelisted_domains' ),10, 2  );
            add_filter( 'manage_users_columns', array( $this, 'bwdr_user_status_column' ) );
            add_filter( 'manage_users_custom_column', array( $this, 'bwdr_populate_user_status_column' ), 10, 3 );
            add_action( 'edit_user_profile', array( $this, 'bwdr_profile_status_field' ) );
            add_action( 'edit_user_profile_update', array( $this, 'bwdr_save_profile_status_field' ) );
            add_filter( 'bp_core_validate_user_signup', array( $this, 'bwdr_bp_blacklisted_domain' ) ); // Buddypress
        }

        /**
		 * Logs out the user if he is auto logged in in UM
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_um_disable_auto_login( $user_id ) {

            if (!empty($user_id) && $user_id == get_current_user_id() ) {
                $status = get_user_meta($user_id, 'bwdr_user_status', true);
                if ('bwdr_pending' == $status) {
                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    wp_set_current_user( 0 );
                }
            }

            return $validation_error;
        }

        /**
		 * Block blacklisted domains from registering through WP User Frontend
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_wpuf_blacklisted_domain( $validation_error, $reg_fname, $reg_lname, $reg_email ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if (!empty($reg_email) && isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                
                $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                if ($this->bwdr_check_if_domain_blacklisted( $reg_email, $blacklisted_domains )) {
                    $validation_error->add( 'reg_email', $bwdr_settings['blacklisted_err'] );
                    do_action( 'bwdr_blacklisted_registration_errors', $validation_error, $reg_fname.' '.$reg_lname, $reg_email, $bwdr_settings );
                }
                    
            }

            return $validation_error;
        }

        /**
		 * Block blacklisted domains from registering through Profile Builder
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_wppb_blacklisted_domain( $output_field_errors, $form_fields, $global_request, $form_type ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if ('register' == $form_type && !empty($global_request) && isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                
                    if (isset($global_request['email']) && !empty($global_request['email'])) {
                        $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                        if ($this->bwdr_check_if_domain_blacklisted( $global_request['email'], $blacklisted_domains )) {
                            $field_id = 0;
                            foreach ($form_fields as $ffields) {
                                if( 'Default - E-mail' == $ffields['field'] ) {
                                    $field_id = $ffields['id'];
                                }
                            }
                            $output_field_errors[$field_id] = '<span class="wppb-form-error">' . $bwdr_settings['blacklisted_err'] . '</span>';
                            do_action( 'bwdr_blacklisted_registration_errors', $output_field_errors, $global_request['username'], $global_request['email'], $bwdr_settings );
                        }
                    
                }
            }

            return $output_field_errors;
        }

        /**
         * Block blacklisted domains from registering through User Registration
         * @since 1.0
		 * @version 1.0
         */
        public function bwdr_ur_blacklisted_domain($response_array, $form_fields, $form_id) {
            $bwdr_settings   =  get_option('bwdr_settings');
            
            if (!empty($form_fields) && isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                foreach ($form_fields as $field) {
                    if ('email' ==  $field->field_type && !empty($field->value)) {
                        $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);
                       
                        if ($this->bwdr_check_if_domain_blacklisted( $field->value, $blacklisted_domains )) {
                            array_push( $response_array, $bwdr_settings['blacklisted_err'] );
                            do_action( 'bwdr_blacklisted_registration_errors', $response_array, '', $field->value, $bwdr_settings );
                            break;
                        }
                    }
                }
            }
            return $response_array;
        }
        /**
         * Block blacklisted domains from registering through Buddypress
         * @since 1.0
		 * @version 1.0
         */
        public function bwdr_bp_blacklisted_domain($result) {
            $bwdr_settings   =  get_option('bwdr_settings');

            $user_email = $result['user_email'];
            $user_login = $result['user_name'];
            $errors = $result['errors'];
            if (isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                $parts   = explode('@', $user_email);
                $email_domain  = $parts[1];
            
                $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                if (!empty($blacklisted_domains)) {
                    foreach($blacklisted_domains as $blacklisted_domain){
                        if(!empty($blacklisted_domain) && trim($blacklisted_domain) == $email_domain){
                            $errors->add( 'user_email', $bwdr_settings['blacklisted_err'] );
                            $result['errors'] = $errors;
                            do_action( 'bwdr_blacklisted_registration_errors', $errors, $user_login, $user_email, $bwdr_settings );
                            break;
                        }
                    }
                }
            }
            return $result;
        }


        /**
         * Save the new user status
         * @since 1.0
		 * @version 1.0
         */
        public function bwdr_save_profile_status_field( $user_id ) {
            if ( !current_user_can( 'edit_user', $user_id )  ) {
                return false;
            }

            if ( isset($_POST['bwdr_user_status']) && !empty( $_POST['bwdr_user_status'] ) ) {
                $new_status = sanitize_text_field( $_POST['bwdr_user_status'] );

                update_user_meta( $user_id, 'bwdr_user_status', $new_status );
            }
        }

        /**
		 * Update user status field in edit profile page
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_profile_status_field ( $user ) {

            if ( $user->ID == get_current_user_id() ) {
                return;
            }

            $status = get_user_meta ($user->ID, 'bwdr_user_status', true);

            if ( 'bwdr_pending' != $status && 'bwdr_denied' != $status ) {
                $status = 'bwdr_approved';
            }

            ?>
                <table class="form-table">
                    <tr>
                        <th><label for="bwdr_user_status"><?php _e( 'User Status', 'bwdr-registration' ); ?></label>
                        </th>
                        <td>
                            <select id="bwdr_user_status" name="bwdr_user_status">
                                <option value="bwdr_pending" <?php selected( 'bwdr_pending', $status ); ?>><?php _e( 'Pending', 'bwdr-registration' ); ?></option>
                                <option value="bwdr_denied" <?php selected( 'bwdr_denied', $status ); ?>><?php _e( 'Denied', 'bwdr-registration' ); ?></option>
                                <option value="bwdr_approved" <?php selected( 'bwdr_approved', $status ); ?>><?php _e( 'Approved', 'bwdr-registration' ); ?></option>
                            </select>
                            
                        </td>
                    </tr>
                </table>
            <?php
        }

        /**
		 * Populate user status column in user table
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_populate_user_status_column ( $value, $column_name, $user_id ) {

            $status = get_user_meta ($user_id, 'bwdr_user_status', true);

            $value = __('Approved', 'bwdr-registration' );
            switch ( $status ) {
                case 'bwdr_pending':
                    $value = __('Pending', 'bwdr-registration' );
                    break;
                case 'bwdr_denied':
                    $value = __('Denied', 'bwdr-registration' );
                    break;
            }

            return $value;
        }

        /**
		 * Add user status column in user table
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_user_status_column( $column ) {

            $column['bwdr_status'] = __('Status', 'bwdr-registration' );

            return $column;
        }

        /**
		 * Add approved status for whitelisted domains
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_allow_whitelisted_domains( $status, $user_id ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if (isset($bwdr_settings['whitelisted']) && !empty($bwdr_settings['whitelisted'])) {
                $user_info = get_userdata($user_id);
                $user_email = $user_info->user_email;

                $parts   = explode('@', $user_email);
                $email_domain  = $parts[1];
            
                $whitelisted_domains =  explode("\n", $bwdr_settings['whitelisted']);

                if (!empty($whitelisted_domains)) {
                    foreach ($whitelisted_domains as $whitelisted_domain){
                        if(!empty($whitelisted_domain) && trim($whitelisted_domain) == $email_domain){
                            $status = 'bwdr_approved';
                        }
                    }
                }
            }
            return $status;
        }


        /**
		 * Block blacklisted domains from registering through WooCommerce
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_woo_blacklisted_domain( $errors, $user_login, $password, $user_email ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if (isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                
                $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                if ($this->bwdr_check_if_domain_blacklisted( $user_email, $blacklisted_domains )) {
                    $errors->add( 'bwdr_blacklist_error', $bwdr_settings['blacklisted_err'] );
                    do_action( 'bwdr_blacklisted_registration_errors', $errors, $user_login, $user_email, $bwdr_settings );
                }
            }
            return $errors;
        }

        /**
		 * Block blacklisted domains from registering through Ultimate Member
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_um_blacklisted_domain( $fields ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if (isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                
                $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                if ($this->bwdr_check_if_domain_blacklisted( $fields['user_email'], $blacklisted_domains )) {

                    UM()->form()->add_error( 'user_email', $bwdr_settings['blacklisted_err']);
                    do_action( 'bwdr_blacklisted_registration_errors', '', $fields['user_login'], $fields['user_email'], $bwdr_settings );
                }
            }
        }

        /**
		 * Check if email is in blacklist
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_check_if_domain_blacklisted( $email, $blacklisted_domains ) {

            if (!empty($blacklisted_domains) && !empty($email)) {
                $parts   = explode('@', $email);
                $email_domain  = $parts[1];

                foreach($blacklisted_domains as $blacklisted_domain){
                    if(!empty($blacklisted_domain) && trim($blacklisted_domain) == $email_domain){
                        return true;
                    }
                }
            }
            return false;
        }

        /**
		 * Block blacklisted domains from registering
		 * @since 1.0
		 * @version 1.0
		 */
        public function bwdr_blacklisted_domain( $errors, $sanitized_user_login, $user_email ) {

            $bwdr_settings   =  get_option('bwdr_settings');

            if (isset($bwdr_settings['blacklisted']) && !empty($bwdr_settings['blacklisted'])) {
                $parts   = explode('@', $user_email);
                $email_domain  = $parts[1];
            
                $blacklisted_domains =  explode("\n", $bwdr_settings['blacklisted']);

                if (!empty($blacklisted_domains)) {
                    foreach($blacklisted_domains as $blacklisted_domain){
                        if(!empty($blacklisted_domain) && trim($blacklisted_domain) == $email_domain){
                            $errors->add( 'bwdr_blacklist_error', '<strong>' . __('Error', 'bwdr-registration' ).'</strong>: ' . $bwdr_settings['blacklisted_err'] );
                            do_action( 'bwdr_blacklisted_registration_errors', $errors, $sanitized_user_login, $user_email, $bwdr_settings );
                            break;
                        }
                    }
                }
            }
            return $errors;
        }

        /**
         * Add a status for newly registered user
         * @since 1.0
		 * @version 1.0
         */
        public function bwdr_user_status( $user_id ) {

            $status = apply_filters( 'bwdr_default_user_status', 'bwdr_pending' , $user_id );
            
            update_user_meta( $user_id, 'bwdr_user_status', $status );

        }

        /**
         * Check if the user is good to sign in based on their status.
         * @since 1.0
		 * @version 1.0
         */
        public function bwdr_authenticate_user( $userdata ) {

            $status = @get_user_meta($userdata->ID, 'bwdr_user_status', true);
            if ( empty($status) || 'bwdr_approved' == $status ) {
                // approved user
                return $userdata;
            }
            $message = $userdata;
            switch ( $status ) {
                case 'bwdr_pending':
                    $message = new WP_Error( 'bwdr_pending', '<strong>ERROR</strong>: ' .  __('Admin has not approved your account yet.', 'bwdr-registration' ) );
                    break;
                case 'bwdr_denied':
                    $message = new WP_Error( 'bwdr_denied', '<strong>ERROR</strong>: ' . __('Admin has blocked your account.', 'bwdr-registration' ) );
                    break;
            }
            return $message;
        }
      
    }

    new bwdr_approval();
endif;
