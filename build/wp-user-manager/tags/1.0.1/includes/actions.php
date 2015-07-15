<?php
/**
 * Plugin Actions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add nonce field to login form needed for ajax validation
 *
 * @since 1.0.0
 * @access public
 * @return string nonce field
 */
function wpum_add_nonce_to_login_form() {
	return wp_nonce_field( "wpum_nonce_login_form", "wpum_nonce_login_security" );
}
add_action( 'login_form_bottom', 'wpum_add_nonce_to_login_form' );

/**
 * Stops users from accessing wp-login.php?action=register
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_restrict_wp_register() {

	if ( wpum_get_option( 'wp_login_signup_redirect' ) ):
		$permalink = wpum_get_option( 'wp_login_signup_redirect' );
		wp_redirect( esc_url( get_permalink( $permalink ) ) );
		exit();
	endif;

}
add_action( 'login_form_register', 'wpum_restrict_wp_register' );

/**
 * Stops users from seeing the admin bar on the frontend.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_remove_admin_bar() {

	$excluded_roles = wpum_get_option( 'adminbar_roles' );
	$user = wp_get_current_user();

	if ( !empty( $excluded_roles ) && array_intersect( $excluded_roles, $user->roles ) && !is_admin() ) {
		if ( current_user_can( $user->roles[0] ) ) {
			show_admin_bar( false );
		}
	}

}
add_action( 'after_setup_theme', 'wpum_remove_admin_bar' );

/**
 * Stops users from seeing the profile.php page in wp-admin.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_remove_profile_wp_admin() {

	if ( !current_user_can( 'administrator' ) && IS_PROFILE_PAGE && wpum_get_option( 'backend_profile_redirect' ) ) {
		wp_redirect( esc_url( get_permalink( wpum_get_option( 'backend_profile_redirect' ) ) ) );
		exit;
	}

}
add_action( 'load-profile.php', 'wpum_remove_profile_wp_admin' );

/**
 * Show content of the User ID column in user list page
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_show_user_id_column_content( $value, $column_name, $user_id ) {
	if ( 'user_id' == $column_name )
		return $user_id;
	return $value;
}
add_action( 'manage_users_custom_column',  'wpum_show_user_id_column_content', 10, 3 );

/**
 * Register widgets
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_register_widgets() {
	register_widget( 'WPUM_Recently_Registered_Users' );
	register_widget( 'WPUM_Password_Recovery' );
	register_widget( 'WPUM_Registration_Form_Widget' );
	register_widget( 'WPUM_Login_Form_Widget' );
}
add_action( 'widgets_init', 'wpum_register_widgets', 1 );

/**
 * Authenticates the login form, if failed
 * returns back to the page where it came from.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_authenticate_login_form( $user ) {

	if ( !defined( 'DOING_AJAX' ) && isset( $_SERVER['HTTP_REFERER'] ) && isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) :

		// check what page the login attempt is coming from
		$referrer = $_SERVER['HTTP_REFERER'];

		$error = false;

		if ( $_POST['log'] == '' || $_POST['pwd'] == '' ) {
			$error = true;
		}

		// check that were not on the default login page
		if ( !empty( $referrer ) && !strstr( $referrer, 'wp-login' ) && !strstr( $referrer, 'wp-admin' ) && $error ) {

			// make sure we don't already have a failed login attempt
			if ( !strstr( $referrer, '?login=failed' ) ) {
				// Redirect to the login page and append a querystring of login failed
				wp_redirect( $referrer . '?login=failed' );
			} else {
				wp_redirect( $referrer );
			}

			exit;

		}

	endif;

}
add_action( 'authenticate', 'wpum_authenticate_login_form' );

/**
 * Redirects wp_login_form when wrong credentials.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_handle_failed_login( $user ) {
	
	if ( isset( $_SERVER['HTTP_REFERER'] ) && !defined( 'DOING_AJAX' ) ) :
		// check what page the login attempt is coming from
		$referrer = $_SERVER['HTTP_REFERER'];

		// check that were not on the default login page
		if ( !empty( $referrer ) && !strstr( $referrer, 'wp-login' ) && !strstr( $referrer, 'wp-admin' ) && $user!=null ) {
			// make sure we don't already have a failed login attempt
			if ( !strstr( $referrer, '?login=failed' ) ) {
				// Redirect to the login page and append a querystring of login failed
				wp_redirect( $referrer . '?login=failed' );
			} else {
				wp_redirect( $referrer );
			}

			exit;
		}
	endif;
	
}
add_action( 'wp_login_failed', 'wpum_handle_failed_login' );

/**
 * Displays a message if php version is lower than required one.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wpum_php_is_old() {
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) { ?>
		<div class="error">
			<p><?php echo sprintf( __( 'This plugin requires a minimum PHP Version 5.3 to be installed on your host. <a href="%s" target="_blank">Click here to read how you can update your PHP version</a>.', 'wpum'), 'http://www.wpupdatephp.com/contact-host/' ); ?></p>
		</div>
	<?php
	}
}
add_action( 'admin_notices', 'wpum_php_is_old' );
