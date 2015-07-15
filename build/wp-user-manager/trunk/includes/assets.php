<?php
/**
 * Handles loading of css and js files.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Loads the plugin admin assets files
 *
 * @since 1.0.0
 * @return void
 */
function wpum_admin_cssjs() {

	$js_dir  = WPUM_PLUGIN_URL . 'assets/js/';
	$css_dir = WPUM_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Styles & scripts
	wp_register_style( 'wpum-admin', $css_dir . 'wp_user_manager' . $suffix . '.css', WPUM_VERSION );
	wp_register_style( 'wpum-shortcode-manager', WPUM_PLUGIN_URL . 'includes/admin/tinymce/css/wpum_shortcodes_tinymce_style.css', WPUM_VERSION );
	wp_register_style( 'wpum-select2', WPUM_PLUGIN_URL . 'assets/select2/css/select2.css', WPUM_VERSION );
	wp_register_script( 'wpum-select2', WPUM_PLUGIN_URL . 'assets/select2/js/select2.min.js', 'jQuery', WPUM_VERSION, true );
	wp_register_script( 'wpum-serializeJSON', WPUM_PLUGIN_URL . 'assets/js/vendor/jquery.serializeJSON.js', 'jQuery', WPUM_VERSION, true );
	wp_register_script( 'wpum-admin-js', $js_dir . 'wp_user_manager_admin' . $suffix . '.js', 'jQuery', WPUM_VERSION, true );

	// Enquery styles and scripts anywhere needed
	wp_enqueue_style( 'wpum-shortcode-manager' );

	// Enqueue styles & scripts on admin page only
	$screen = get_current_screen();

	// Load styles only on required pages.
	if ( $screen->base == 'users_page_wpum-settings' || $screen->id == 'wpum_directory' || $screen->base == 'users_page_wpum-edit-field' || $screen->base == 'users_page_wpum-profile-fields' ):

		wp_enqueue_script( 'wpum-select2' );
		wp_enqueue_script( 'wpum-admin-js' );
		wp_enqueue_style( 'wpum-admin' );
		wp_enqueue_style( 'wpum-select2' );
		wp_enqueue_script( 'accordion' );

		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'default_fields' && $screen->base == 'users_page_wpum-settings' )
			wp_enqueue_script( 'jquery-ui-sortable' );

		if ( $screen->base == 'users_page_wpum-custom-fields-editor' )
			wp_enqueue_script( 'wpum-serializeJSON' );

		// Backend JS Settings
		wp_localize_script( 'wpum-admin-js', 'wpum_admin_js', array(
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'confirm' => __( 'Are you sure you want to do this? This action cannot be reversed.', 'wpum' ),
		) );

	endif;

}
add_action( 'admin_enqueue_scripts', 'wpum_admin_cssjs' );


/**
 * Loads the plugin frontend assets files
 *
 * @since 1.0.0
 * @return void
 */
function wpum_frontend_cssjs() {

	$js_dir  = WPUM_PLUGIN_URL . 'assets/js/';
	$css_dir = WPUM_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Styles & scripts registration
	wp_register_script( 'wpum-frontend-js', $js_dir . 'wp_user_manager' . $suffix . '.js', array( 'jquery' ), WPUM_VERSION, true );
	wp_register_style( 'wpum-frontend-css', $css_dir . 'wp_user_manager_frontend' . $suffix . '.css' , WPUM_VERSION );

	// Enqueue everything
	wp_enqueue_script( 'jQuery' );
	wp_enqueue_script( 'wpum-frontend-js' );

	// Allows developers to disable the frontend css in case own file is needed.
	if ( !defined( 'WPUM_DISABLE_CSS' ) )
		wp_enqueue_style( 'wpum-frontend-css' );

	// Display password meter only if enabled
	if ( wpum_get_option( 'display_password_meter_registration' ) ) :
			
		wp_enqueue_script( 'password-strength-meter' );
			
		wp_localize_script( 'password-strength-meter', 'pwsL10n', array(
			'empty'  => __( 'Strength indicator', 'wpum' ),
			'short'  => __( 'Very weak', 'wpum' ),
			'bad'    => __( 'Weak', 'wpum' ),
			'good'   => _x( 'Medium', 'password strength', 'wpum' ),
			'strong' => __( 'Strong', 'wpum' )
		) );

	endif;

	// Frontend jS Settings
	wp_localize_script( 'wpum-frontend-js', 'wpum_frontend_js', array(
		'ajax'                 => admin_url( 'admin-ajax.php' ),
		'checking_credentials' => __( 'Checking credentials...', 'wpum' ),
		'pwd_meter'            => wpum_get_option( 'display_password_meter_registration' ),
		'disable_ajax'         => wpum_get_option( 'disable_ajax' )
	) );

}
add_action( 'wp_enqueue_scripts', 'wpum_frontend_cssjs' );