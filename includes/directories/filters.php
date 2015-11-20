<?php
/**
 * Directories Filters
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
 * Modify the user query to search for users through the search form.
 *
 * @param array $args         query arguments.
 * @param string $directory_id directory id number.
 * @since 1.2.0
 */
function wpum_directory_pre_set_search( $args, $directory_id ) {

	if( wpum_directory_has_search_form( $directory_id ) && isset( $_POST['search_user'] ) ) {

		$args['search'] = sanitize_text_field( $_POST['search_user'] );

	}

	return $args;

}
add_filter( 'wpum_user_directory_query', 'wpum_directory_pre_set_search', 15, 2 );
