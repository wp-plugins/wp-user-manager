<?php
/**
 * Plugin Filters
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Settings Link To WP-Plugin Page
 * 
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_add_settings_link( $links ) {
	$settings_link = '<a href="'.admin_url( 'users.php?page=wpum-settings' ).'">'.__('Settings','wpum').'</a>';
	array_push( $links, $settings_link );
	return $links;
}
add_filter( "plugin_action_links_".WPUM_SLUG , 'wpum_add_settings_link');

/**
 * Add links to plugin row
 * 
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_plugin_row_meta( $input, $file ) {
	
	if ( $file != 'wp-user-manager/wp-user-manager.php' )
		return $input;

	$links = array(
		'<a href="http://docs.wpusermanager.com" target="_blank">' . esc_html__( 'Documentation', 'wpum' ) . '</a>',
		'<a href="http://wpusermanager.com/addons/" target="_blank">' . esc_html__( 'Extensions', 'wpum' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'wpum_plugin_row_meta', 10, 2 );

/**
 * Add User ID Column to users list
 * 
 * @since 1.0.0
 * @access public
 * @return array
 */
function wpum_add_user_id_column( $columns ) {
    $columns['user_id'] = __( 'User ID', 'wpum' );
    return $columns;
}
add_filter( 'manage_users_columns', 'wpum_add_user_id_column' );

/**
 * Filters the upload dir when $wpum_upload is true
 *
 * @copyright mikejolley
 * @since 1.0.0
 * @param  array $pathdata
 * @return array
 */
function wpum_upload_dir( $pathdata ) {
	global $wpum_upload, $wpum_uploading_file;

	if ( ! empty( $wpum_upload ) ) {
		$dir = apply_filters( 'wpum_upload_dir', 'wp-user-manager-uploads/' . sanitize_key( $wpum_uploading_file ), sanitize_key( $wpum_uploading_file ) );

		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/' . $dir;
			$pathdata['url']    = $pathdata['url'] . '/' . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir         = '/' . $dir . $pathdata['subdir'];
			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['subdir'] );
		}
	}

	return $pathdata;
}
add_filter( 'upload_dir', 'wpum_upload_dir' );

/**
 * Add rating links to the admin panel
 *
 * @since	    1.0.0
 * @global		string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function wpum_admin_rate_us( $footer_text ) {
	
	$screen = get_current_screen();

	if ( $screen->base !== 'users_page_wpum-settings' )
		return;

	$rate_text = sprintf( __( 'Please support the future of <a href="%1$s" target="_blank">WP User Manager</a> by <a href="%2$s" target="_blank">rating us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'wprm', 'wpum' ),
		'https://wpusermanager.com',
		'http://wordpress.org/support/view/plugin-reviews/wp-user-manager?filter=5#postform'
	);

	return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . ' <span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span></span>';
	
}
add_filter( 'admin_footer_text', 'wpum_admin_rate_us' );

/**
 * Add custom classes to body tag
 * 
 * @since	    1.0.0
 * @param       array $classes
 * @return      array
 */
function wpum_body_classes($classes) {

	if( is_page( wpum_get_core_page_id('login') ) ) {
		// add class if we're on a login page
		$classes[] = 'wpum-login-page';
	} else if( is_page( wpum_get_core_page_id('register') ) ) {
		// add class if we're on a register page
		$classes[] = 'wpum-register-page';
	} else if( is_page( wpum_get_core_page_id('account') ) ) {
		// add class if we're on a account page
		$classes[] = 'wpum-account-page';
	} else if( is_page( wpum_get_core_page_id('profile') ) ) {
		
		// add class if we're on a profile page
		$classes[] = 'wpum-profile-page';

		// add user to body class if set
		if( wpum_is_single_profile() )
			$classes[] = 'wpum-user-' . wpum_is_single_profile();

	} else if( is_page( wpum_get_core_page_id('password') ) ) {
		// add class if we're on a password page
		$classes[] = 'wpum-password-page';
	}
		
	return $classes;
}
add_filter( 'body_class', 'wpum_body_classes' );

/**
 * Modify the WP_User_Query on the directory page.
 * Check whether the directory should be displaying
 * specific user roles only.
 * 
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_roles( $args, $directory_id ) {

	// Get roles
	$roles = wpum_directory_get_roles( $directory_id );

	// Execute only if there are roles.
	if( $roles ) {

		global $wpdb;
		$blog_id = get_current_blog_id();

		$meta_query = array(
		    'key' => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
		    'value' => '"(' . implode( '|', array_map( 'preg_quote', $roles ) ) . ')"',
		    'compare' => 'REGEXP'
		);

		$args['meta_query'] = array( $meta_query );

	}

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_roles', 10, 2 );

/**
 * Modify the WP_User_Query on the directory page.
 * Check whether the directory should be excluding
 * specific users by id.
 * 
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_exclude_users( $args, $directory_id ) {

	$users = wpum_directory_get_excluded_users( $directory_id );

	if( is_array( $users ) )
		$args['exclude'] = $users;

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_exclude_users', 11, 2 );

/**
 * Modify the WP_User_Query on the directory page.
 * Specify a custom sorting order.
 * 
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_order( $args, $directory_id ) {

	// Get selected sorting method
	$sorting_method = get_post_meta( $directory_id, 'default_sorting_method', true );

	// Check whether a sorting method is set from frontend
	if( isset( $_GET['sort'] ) && array_key_exists( $_GET['sort'] , wpum_get_directory_sorting_methods() ) )
		$sorting_method = sanitize_key( $_GET['sort'] );

	switch ( $sorting_method ) {
		case 'user_nicename':
			$args['orderby'] = 'user_nicename';
			break;
		case 'newest':
			$args['orderby'] = 'registered';
			$args['order'] = 'DESC';
			break;
		case 'oldest':
			$args['orderby'] = 'registered';
			break;
		case 'name':
			$args['meta_key'] = 'first_name';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';
			break;
		case 'last_name':
			$args['meta_key'] = 'last_name';
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';
			break;
	}

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_order', 12, 2 );

/**
 * Modify the WP_User_Query on the directory page.
 * Check whether the directory should be setting a specific amount of users.
 * 
 * @since 1.0.0
 * @param array $args WP_User_Query args.
 * @param string $directory_id id number of the directory.
 * @return array
 */
function wpum_directory_pre_set_amount( $args, $directory_id ) {

	$can_sort = wpum_directory_display_amount_sorter( $directory_id );

	if( $can_sort && isset( $_GET['amount'] ) && is_numeric( $_GET['amount'] ) )
		$args['number'] = sanitize_key( $_GET['amount'] );

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_amount', 11, 2 );

/**
 * Retrieve custom avatar if any
 * 
 * @since 1.0.0
 *
 * @param int|string|object $id_or_email A user ID,  email address, or comment object
 * @param int     $size        Size of the avatar image
 * @param string  $default     URL to a default image to use if no avatar is available
 * @param string  $alt         Alternative text to use in image tag. Defaults to blank
 * @return false|string `<img>` tag for the user's avatar.
 */
function wpum_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

	$safe_alt = esc_attr( $alt );
	$custom_avatar = false;

	if( is_object( $id_or_email ) ) {

		$comment_email = $id_or_email->comment_author_email;

		$user = get_user_by( 'email', $comment_email );
		
		if( $user ) {
			$custom_avatar = get_user_meta( $user->ID , 'current_user_avatar', true );
		}

	} elseif ( is_email( $id_or_email ) && email_exists( $id_or_email ) || is_numeric( $id_or_email ) ) {
		$custom_avatar = get_user_meta( $id_or_email, 'current_user_avatar', true );
	}
	
	if ( !empty( $custom_avatar ) ) {
		$avatar = "<img alt='{$safe_alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
	}

	return $avatar;

}
add_filter('get_avatar', 'wpum_get_avatar', 1, 5);

/**
 * Highlight which pages are selected as plugin's core page
 * within the Pages management screen.
 *
 * @since 1.0.0
 * @param array $post_states An array of post display states.
 * @param int   $post        The post ID.
 * @return array
 */
function wpum_highlight_pages( $post_states, $post ) {

	$icon = '<i class="wpum-shortcodes-icon" title="'.__( 'WPUM Page', 'wpum' ).'"></i>';

	if( wpum_get_core_page_id( 'login' ) == $post->ID ) {
		$post_states['page_for_login'] = $icon;
	} else if( wpum_get_core_page_id( 'account' ) == $post->ID ) {
		$post_states['page_for_account'] = $icon;
	} else if( wpum_get_core_page_id( 'password' ) == $post->ID ) {
		$post_states['page_for_password'] = $icon;
	} else if( wpum_get_core_page_id( 'register' ) == $post->ID ) {
		$post_states['page_for_registration'] = $icon;
	} else if( wpum_get_core_page_id( 'profile' ) == $post->ID ) {
		$post_states['page_for_profiles'] = $icon;
	}

	return $post_states;

}
add_filter( 'display_post_states', 'wpum_highlight_pages', 10, 2 );

/**
 * Adjust body class on admin panel
 *
 * @since 1.0.0
 * @return array
 */
function wpum_admin_body_classes( $classes ) {

	$screen = get_current_screen();

	if( $screen->base == 'plugin-install' && isset( $_GET['tab'] ) && $_GET['tab'] == 'wpum_addons' ) {
		$classes .= 'wpum_addons_page';
	}

	return $classes;

}
add_filter( 'admin_body_class', 'wpum_admin_body_classes' );