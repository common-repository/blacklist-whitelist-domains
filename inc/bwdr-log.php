<?php

if ( ! class_exists( 'bwdr_log' ) ) :
    class bwdr_log {

        /**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
        public function __construct() {

            add_action( 'admin_menu', array( $this, 'admin_menu_page') );

            add_action( 'bwdr_blacklisted_registration_errors', array( $this, 'save_log_on_blacklist'), 10, 4 );
        }


        /**
		 * Log page menu
		 * @since 1.0
		 * @version 1.0
		 */
        public function admin_menu_page() {
        
            add_submenu_page( 'user-registration-options', __('Blacklist Log', 'bwdr-registration' ), __('Blacklist Log', 'bwdr-registration' ), 'manage_options', 'blacklist-log', array($this, 'blacklist_log_callback'));
        }

        /**
		 * Settings Page Content
		 * @since 1.0
		 * @version 1.0
		 */
        public function blacklist_log_callback() {
            $logListTable = new bwdr_log_wp_list_table();
			$logListTable->prepare_items();
			?>
				<div class="wrap">
					<div id="icon-users" class="icon32"></div>
					<h2>Blacklist Log</h2>
					<form id="bwdr-blacklist-log-filter" method="post">
                    	<input type="hidden" name="page" value="bwdr-blacklist-log">
						<?php wp_nonce_field( 'bwdr-blacklist-log', 'bwdr_nonce', false ); ?>
						<?php $logListTable->display(); ?>
					</form>
				</div>
			<?php
        }

        public function save_log_on_blacklist($errors, $sanitized_user_login, $user_email, $bwdr_settings) {
            global $wpdb;

			$table_name = $wpdb->prefix."bwdr_log";
			$registration_time = time();
			$sql = $wpdb->prepare( "INSERT INTO ".$table_name." (registration_time, user_name, user_email) VALUES ( %s, %s, %s)", $registration_time, $sanitized_user_login, $user_email );
			$wpdb->query($sql);
        }

    }
    
    new bwdr_log();
   
endif;


if ( ! class_exists( 'bwdr_log_wp_list_table' ) ) :

	if( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

	class bwdr_log_wp_list_table extends WP_List_Table{

        /**
		 * Define Sortable Columns
		 * @since 1.0
		 * @version 1.0
		 */
		public function get_sortable_columns() {
			return array(
				'id' => array('id', false),
				'registration_time' => array('registration_time', false),
				'user_name' => array('user_name', false),
				'user_email' => array('user_email', false),
		    );
		}

        /**
		 * Define All Columns
		 * @since 1.0
		 * @version 1.0
		 */
		public function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'id':
				case 'registration_time':
				case 'user_name':
				case 'user_email':
					return $item[ $column_name ];

				default:
					return print_r( $item, true ) ;
			}
		}

		/**
		 * Define Column Headings
		 * @since 1.0
		 * @version 1.0
		 */
		public function get_columns() {
			$columns = array(
				'id'					=> __('Log DB ID', 'bwdr-registration' ),
				'registration_time'		=> __('Registration Time', 'bwdr-registration' ),
				'user_name'				=> __('User Name', 'bwdr-registration' ),
				'user_email'			=> __('User Email', 'bwdr-registration' ),
			);
			return $columns;
		}

        /**
		 * Setup table data
		 * @since 1.0
		 * @version 1.0
		 */
		private function table_data() {

			global $wpdb;
			$logtable = $wpdb->prefix.'bwdr_log';


			$DBdata = $wpdb->get_results ( "SELECT * FROM $logtable ");
			$result = array();
			if (!empty($DBdata)) {
				
				$count = 0;
				foreach ($DBdata as $data){
					$result[$count]['id'] = $data->id;
					$result[$count]['registration_time'] = date("d-m-Y h:ia", $data->registration_time);
					$result[$count]['user_name'] = $data->user_name;
					$result[$count]['user_email'] = $data->user_email;
					$count++;
				}
			}
			return $result;
		}

		/**
		 * Define hidden columns
		 * @since 1.0
		 * @version 1.0
		 */
		public function get_hidden_columns()
		{
			return array();
		}

        /**
		 * Display table data
		 * @since 1.0
		 * @version 1.0
		 */
		public function prepare_items() {
			$columns = $this->get_columns();
			$hidden = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();
	
			$data = $this->table_data();
			usort( $data, array( &$this, 'sort_data' ) );
	
			$perPage = 5;
			$currentPage = $this->get_pagenum();
			$totalItems = count($data);
	
			$this->set_pagination_args( array(
				'total_items' => $totalItems,
				'per_page'    => $perPage
			) );
	
			$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
	
			$this->_column_headers = array($columns, $hidden, $sortable);
			$this->items = $data;
		}

        /**
		 * Sort table data
		 * @since 1.0
		 * @version 1.0
		 */
		private function sort_data( $a, $b ) {
			// Set defaults
			$orderby = 'registration_time';
			$order = 'asc';

			// If orderby is set, use this as the sort column
			if(!empty($_GET['orderby']))
			{
				$orderby = sanitize_text_field($_GET['orderby']);
			}

			// If order is set use this as the order
			if(!empty($_GET['order']))
			{
				$order = sanitize_text_field($_GET['order']);
			}


			$result = strcmp( $a[$orderby], $b[$orderby] );

			if($order === 'asc')
			{
				return $result;
			}

			return -$result;
		}

	}
	
endif;