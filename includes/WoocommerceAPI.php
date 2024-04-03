<?php
if( !class_exists( 'WoocommerceAPI' ) ){

	class WoocommerceAPI
	{
		private static $instance;

		private $OrderAPI,$ProductAPI;
	
		static function GetInstance(){

			if (!isset(self::$instance)){


	            self::$instance = new self();

	        }

	        return self::$instance;
		}
	
		public function activate(){

		}
		public function InitPlugin(){

			add_action( 'admin_menu', [$this,'RegisterUser'] );
			add_action( 'wp_ajax_create_user', [$this,'CreateUser'] );
			add_action( 'wp_ajax_test_confirguration', [$this,'TestConfirguration'] );

			add_action( 'admin_enqueue_scripts', [$this,'AdminEnqueueScripts']);

			/* Create Custom Endpoint */
			add_action( 'rest_api_init', [$this,'RestAPIInit'] );

			add_role($role = "api-user", $display_name= "API User");

			$this->OrderAPI = new OrderAPI();
			$this->ProductAPI = new ProductAPI();
		}
		public function RestAPIInit(){

			

			register_rest_route(
		        'v1',
		        '/connection',
		        array(
		            'methods' => 'GET',
		            'callback' => [$this,'Connection'],
		        )
		    );

			register_rest_route(
		        'v1',
		        '/products',
		        array(
		            'methods' => 'GET',
		            'callback' => [$this->ProductAPI,'GetProducts'],

		            'args' => array(
		            	'page' => array(
			            	'validate_callback' => function( $param, $request, $key ) {
			                	return is_numeric( $param );
			            	}
			            )
			        ),
		        )
		    );

		    register_rest_route(
		    	'v1',
		        '/products/(?P<id>\d+)',
		        array(
		            'methods' => 'GET',
		            'callback' => [$this->ProductAPI,'GetSingleProduct']
		        )
		    );

		    register_rest_route(
		    	'v1',
		        '/orders',
		        array(
		            'methods' => 'POST',
		            'callback' => [$this->OrderAPI,'CreateOrder']
		        )
		    );
		}
		public static function Authentication(){
			//echo $_SERVER['PHP_AUTH_USER'];
			if (!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || empty($_SERVER['PHP_AUTH_PW'])) {
				$error = new WP_Error( 'authentication_error', __( 'api username or password is empty.'));
			}
			else{
				$user = $_SERVER['PHP_AUTH_USER'];
				$password = $_SERVER['PHP_AUTH_PW'];

				$userdata = get_user_by('login', $user);
				
				$result = wp_check_password($password, $userdata->user_pass, $userdata->ID);

				if(!$result || !in_array('api-user',$userdata->roles)){
					$error = new WP_Error( 'authentication_error', __( 'invalid api username or password.'));
				}
			}
			
			if($error){
				return wp_send_json(["status"=>"failed","errorMessage"=>$error],401);
			}

			return true;
		}
		public function Connection(){
			if(self::Authentication()){
				return wp_send_json(["status"=>"success","errorMessage"=>null],200);
			}
		}

		private function set_order_addresses( $order, $data ) {

			$address_fields = array(
				'first_name',
				'last_name',
				'company',
				'email',
				'phone',
				'address_1',
				'address_2',
				'city',
				'state',
				'postcode',
				'country',
			);

			$billing_address = $shipping_address = array();

			// billing address
			if ( isset( $data['billing_address'] ) && is_array( $data['billing_address'] ) ) {

				foreach ( $address_fields as $field ) {

					if ( isset( $data['billing_address'][ $field ] ) ) {
						$billing_address[ $field ] = wc_clean( $data['billing_address'][ $field ] );
					}
				}

				unset( $address_fields['email'] );
				unset( $address_fields['phone'] );
			}

			// shipping address
			if ( isset( $data['shipping_address'] ) && is_array( $data['shipping_address'] ) ) {

				foreach ( $address_fields as $field ) {

					if ( isset( $data['shipping_address'][ $field ] ) ) {
						$shipping_address[ $field ] = wc_clean( $data['shipping_address'][ $field ] );
					}
				}
			}

			//$this->update_address( $order, $billing_address, 'billing' );
			//$this->update_address( $order, $shipping_address, 'shipping' );

			// update user meta
			if ( $order->get_user_id() ) {
				foreach ( $billing_address as $key => $value ) {
					update_user_meta( $order->get_user_id(), 'billing_' . $key, $value );
				}
				foreach ( $shipping_address as $key => $value ) {
					update_user_meta( $order->get_user_id(), 'shipping_' . $key, $value );
				}
			}
		}
		public static function getUsersList(){
			$user_query = new WP_User_Query( array( 'role' => 'api-user' ) );
			$users = $user_query->get_results();
			return $users; 
		} 
		public function TestConfirguration(){
			global $wpdb;
			$user_login = $wpdb->escape(trim($_POST['user_login']));
			$password = $wpdb->escape(trim($_POST['password']));
			$userdata = get_user_by('login', $user_login);
			$result = wp_check_password($password, $userdata->user_pass, $userdata->ID);
			if($result){
				$sucessMsg = "Confirguration connected successfully";
            	wp_send_json([
	        		"status"=>1,
	        		"message"=>$sucessMsg,
	        	]);
			}
			else{
				$errorMsg[] = 'Not able to connect confirguration.';
                wp_send_json([
	        		"status"=>0,
	        		"error"=>$errorMsg
	        	]);
			}
		}
		public function AdminEnqueueScripts(){
			wp_enqueue_script('admin-script', WOO_API_PLUGIN_URL . 'assets/js/admin-script.js', array(), '1.0',true);
			wp_localize_script('admin-script', 'ajax', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php')
				)
			);
		}
		public function CreateUser(){
			global $wpdb;

			$user_login = $wpdb->escape(trim($_POST['user_login']));
			$first_name = $wpdb->escape(trim($_POST['first_name']));
    		$last_name = $wpdb->escape(trim($_POST['last_name']));
    		$email = $wpdb->escape(trim($_POST['email']));
        	$password = $wpdb->escape(trim($_POST['password']));
        	$confirm_password = $wpdb->escape(trim($_POST['confirm_password']));
			
			$errorMsg = [];
			if(empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name) || empty($user_login)) {
	            $errorMsg[] = "Please don't leave the required fields.";
	        }
	        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	            $errorMsg[] = 'Invalid email address.';
	        }
		    if($password <> $confirm_password ){
	            $errorMsg[] = 'Password do not match.';
	        }
	        if(!empty($user_login) && username_exists($user_login)){
	        	$errorMsg[] = 'Username already exist.';
	        }
	        if(!empty($email) && email_exists($email)) {
	            $errorMsg[] = 'Email already exist.';
	        }
	        if(sizeof($errorMsg)>0){
	        	wp_send_json([
	        		"status"=>0,
	        		"error"=>$errorMsg
	        	]);
	        }
	        $user_id = wp_insert_user(
                array(
                    'first_name' => apply_filters('pre_user_first_name', $first_name), 
                    'last_name' => apply_filters('pre_user_last_name', $last_name),
                    'user_nicename' => apply_filters('pre_user_user_nicename', $first_name),
                    'user_login' => apply_filters('pre_user_user_login',$user_login),
                    'user_pass' => apply_filters('pre_user_user_pass', $password),
                    'user_email' => apply_filters('pre_user_user_email', $email),
                    'role' => 'api-user'
                )
            );
            if( is_wp_error($user_id) ) {
                $errorMsg[] = 'Error on user creation.';
                wp_send_json([
	        		"status"=>0,
	        		"error"=>$errorMsg
	        	]);
            }
            else {
            	$sucessMsg = "User Added successfully";
            	wp_send_json([
	        		"status"=>1,
	        		"message"=>$sucessMsg,
	        		"data"=>[
	        			"first_name" => $first_name,
	        			"last_name" => $last_name,
	        			"user_login" => $user_login,
	        			"password" => $password	,
	        			"email" => $email
	        		]
	        	]);
            }
		}
		public function RegisterUser(){
			add_menu_page(
				__( 'Woocommerce API', 'textdomain' ),
				'Woo API Users',
				'manage_options', 
				'api-user',
				[$this,'RegisteredUserList'], 
				$icon_url = 'dashicons-admin-users', 
				$position = null
			);
			add_submenu_page('api-user', __( 'Add New API', 'textdomain' ), 'Add New', 'manage_options', 'add-api-user',[$this,'RegisterUserCallback'], $position = null );
		}
		public function RegisteredUserList(){

			require_once WOO_API_PLUGIN_PATH.'/templates/user-list.php';

		}
		public function RegisterUserCallback(){
			
			require_once WOO_API_PLUGIN_PATH.'/templates/register-user.php';

		}
		public static function preInitCheckErrors()
		{
			$errors = [];
			if (!in_array( 
				'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
			))
			{
				$errors[] = 'You have not actived woocommerce';
			}

			return $errors;
		}
	}
}