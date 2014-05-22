<?php
/**
 * Plugin Name: Logged In Users
 * Plugin URI: https://github.com/PlethoraThemes/wp-logged-in-users
 * Description: Display logged in users on the admin bar
 * Version: 0.1.0
 * Author: kostasx
 * Author URI: https://github.com/kostasx
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

class PlethoraLoggedInUsers {

	protected static $instance = null;

	function __construct(){

		add_action( 'admin_bar_menu', array( $this, 'plethora_admin_bar_add' ), 10    );
		add_action( 'wp_login', 	  array( $this, 'plethora_wp_login' ), 	    10, 2 );
		add_action( 'wp_logout', 	  array( $this, 'plethora_wp_logout' ) 			  );

	}

	public static function get_instance(){

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	function plethora_helpers_getusers( $type = 'user_login' ){

		$user_list = get_users();
		$result    = array();

		foreach( $user_list as $user ) $result[] = $user->$type; 
		return $result;

	}

	public function plethora_admin_bar_add(){

		global $wp_admin_bar; 

		$presence  = get_option( 'plethora_users_online' );
		$user_list = $this->plethora_helpers_getusers();
		$title	   = '';	

		foreach ( $user_list as $user  ) {

			if ( $presence[$user] === 'online' ){

				$title .= "<span style='color:green;'>&hearts;</span> " . $user . " | ";

			} else {

				$title .= "<span style='color:red;'>&hearts;</span> " . $user . " | ";

			}

		}

		$wp_admin_bar->add_menu( array( 
			'id' => 'plethora_liu_admin_bar', 
	        'parent'=> 'top-secondary',
			'title' => $title
			)
		);

	}

	function plethora_wp_login( $user_login, $user ){

		global $current_user;
	    get_currentuserinfo();

		$users_online = get_option( 'plethora_users_online' );

		if ( is_array( $users_online ) && 1 !== empty( $users_online) ){

			$users_online[$user_login] = 'online';

		} else {

			$users_online = array( $user_login => 'online' );

		}

		update_option( 'plethora_users_online', $users_online );

	}

	function plethora_wp_logout(){

		$user_login = wp_get_current_user()->user_login;

		$users_online = get_option( 'plethora_users_online' );

		if ( is_array( $users_online ) && 1 !== empty( $users_online) ){

			$users_online[$user_login] = 'offline';

		} else {

			$users_online = array( $user_login => 'offline' );

		}

		update_option( 'plethora_users_online', $users_online );

	}

}

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	add_action( 'plugins_loaded', array( 'PlethoraLoggedInUsers', 'get_instance' ) );

}