<?php
/**
 * Main Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wpum_get_login_methods' ) ) :
/**
 * Define login methods for options panel
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_get_login_methods() {
	return apply_filters( 'wpum_get_login_methods', array(
			'username'       => __( 'Username only', 'wpum' ),
			'email'          => __( 'Email only', 'wpum' ),
			'username_email' => __( 'Username or Email', 'wpum' ),
		) );
}
endif;

if ( ! function_exists( 'wpum_get_psw_lengths' ) ) :
/**
 * Define login methods for options panel
 *
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_get_psw_lengths() {
	return apply_filters( 'wpum_get_psw_lengths', array(
			''       => __( 'Disabled', 'wpum' ),
			'weak'   => __( 'Weak', 'wpum' ),
			'medium' => __( 'Medium', 'wpum' ),
			'strong' => __( 'Strong', 'wpum' ),
		) );
}
endif;

if ( ! function_exists( 'wpum_logout_url' ) ) :
/**
 * A simple wrapper function for the wp_logout_url function
 *
 * The function checks whether a custom url has been passed,
 * if not, looks for the settings panel option,
 * defaults to wp_logout_url
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_logout_url( $custom_redirect = null ) {

	$redirect = null;

	if ( !empty( $custom_redirect ) ) {
		$redirect = esc_url( $custom_redirect );
	} else if ( wpum_get_option( 'logout_redirect' ) ) {
			$redirect = esc_url( get_permalink( wpum_get_option( 'logout_redirect' ) ) );
	}

	return wp_logout_url( apply_filters( 'wpum_logout_url', $redirect, $custom_redirect ) );

}
endif;

if ( ! function_exists( 'wpum_get_username_label' ) ) :
/**
 * Returns the correct username label on the login form
 * based on the selected login method.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_get_username_label() {

	$label = __( 'Username', 'wpum' );

	if ( wpum_get_option( 'login_method' ) == 'email' ) {
		$label = __( 'Email', 'wpum' );
	} else if ( wpum_get_option( 'login_method' ) == 'username_email' ) {
			$label = __( 'Username or email', 'wpum' );
	}

	return $label;

}
endif;

if ( ! function_exists( 'wpum_login_form' ) ) :
/**
 * Display login form.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function wpum_login_form( $args = array() ) {

	$defaults = array(
		'echo'           => true,
		'redirect'       => wpum_get_login_redirect_url(),
		'form_id'        => null,
		'label_username' => wpum_get_username_label(),
		'label_password' => __( 'Password', 'wpum' ),
		'label_remember' => __( 'Remember Me', 'wpum' ),
		'label_log_in'   => __( 'Login', 'wpum' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'login_link'     => 'yes',
		'psw_link'       => 'yes',
		'register_link'  => 'yes'
	);

	// Parse incoming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Show already logged in message
	if ( is_user_logged_in() ) :

		get_wpum_template( 'already-logged-in.php', array( 'args' => $args ) );

	// Show login form if not logged in
	else :

		get_wpum_template( 'forms/login-form.php', array( 'args' => $args ) );

		// Display helper links
		do_action( 'wpum_do_helper_links', $args['login_link'], $args['register_link'], $args['psw_link'] );

	endif;

}
endif;
