<?php
/**
 * Handles the function to work with the fields.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets list of registered field types.
 *
 * @since 1.0.0
 * @return array $field_types - list of field types.
 */
function wpum_get_field_types() {

	return apply_filters( 'wpum/field/types', array() );

}

/**
 * Gets list of registered field classes.
 *
 * @since 1.0.0
 * @return array $field_classes - list of field types and class names.
 */
function wpum_get_field_classes() {

	return apply_filters( 'wpum/field/types/classes', array() );

}

/**
 * Verify if a field type exists
 *
 * @since 1.0.0
 * @return bool - true | false.
 */
function wpum_field_type_exists( $type = '' ) {

	$exists = false;

	$all_types = wpum_get_field_classes();

	if( array_key_exists( $type , $all_types ) )
		$exists = true;

	return $exists;

}

/**
 * Get the class of a field type and returns the object.
 *
 * @since 1.0.0
 * @param  $type type of field
 * @return object - class.
 */
function wpum_get_field_type_object( $type = '' ) {

	$object = null;

	$field_types = wpum_get_field_classes();

	if( !empty( $type ) && wpum_field_type_exists( $type ) ) {
		$class = $field_types[ $type ];
		$object = new $class;
	}

	return $object;
}

/**
 * Get the options of a field type
 *
 * @since 1.0.0
 * @param  $type type of field
 * @return array - list of options.
 */
function wpum_get_field_options( $type = '' ) {

	$options = array();
	$field_types = wpum_get_field_classes();

	if( !empty( $type ) && wpum_field_type_exists( $type ) ) {
		$class = $field_types[ $type ];
		$options = call_user_func( "$class::options" );
	}

	return $options;

}

/**
 * Get the list of registration fields formatted into an array.
 * The format of the array is used by the forms.
 *
 * @since 1.0.0
 * @return array - list of fields.
 */
function wpum_get_registration_fields() {

	// Get fields from the database
	$primary_group = WPUM()->field_groups->get_group_by('primary');

	$args = array(
		'id'           => $primary_group->id,
		'array'        => true,
		'registration' => true,
		'number'       => -1,
		'orderby'      => 'field_order',
		'order'        => 'ASC'
	);

	$data = WPUM()->fields->get_by_group( $args );

	// Manipulate fields list into a list formatted for the forms API.
	$fields = array();
	
	// Loop through the found fields
	foreach ( $data as $key => $field ) {
		
		// Adjust field type parameter if no field type template is defined.
		switch ( $field['type'] ) {
			case 'username':
				$field['type'] = 'text';
				break;
			case 'avatar':
				$field['type'] = 'file';
				break;
		}

		$fields[ $field['meta'] ] = array(
			'priority'    => $field['field_order'],
			'label'       => $field['name'],
			'type'        => $field['type'],
			'meta'        => $field['meta'],
			'required'    => $field['is_required'],
			'description' => $field['description'],
		);

	}

	// Remove password field if not enabled
    if( ! wpum_get_option('custom_passwords') )
    	unset( $fields['password'] );

    // Remove the user avatar field if not enabled
	if( ! wpum_get_option('custom_avatars') )
		unset( $fields['user_avatar'] );

	return apply_filters( 'wpum_get_registration_fields', $fields );

}

/**
 * Get the list of account fields formatted into an array.
 * The format of the array is used by the forms.
 *
 * @since 1.0.0
 * @return array - list of fields.
 */
function wpum_get_account_fields() {

	// Get fields from the database
	$primary_group = WPUM()->field_groups->get_group_by('primary');

	$args = array(
		'id'           => $primary_group->id,
		'array'        => true,
		'number'       => -1,
		'orderby'      => 'field_order',
		'order'        => 'ASC'
	);

	$data = WPUM()->fields->get_by_group( $args );

	// Manipulate fields list into a list formatted for the forms API.
	$fields = array();
	
	// Loop through the found fields
	foreach ( $data as $key => $field ) {
		
		// Adjust field type parameter if no field type template is defined.
		switch ( $field['type'] ) {
			case 'username':
				$field['type'] = 'text';
				break;
			case 'nickname':
				$field['type'] = 'text';
				break;
			case 'display_name':
				$field['type'] = 'select';
				break;
			case 'avatar':
				$field['type'] = 'file';
				break;
		}

		$fields[ $field['meta'] ] = array(
			'priority'    => $field['field_order'],
			'label'       => $field['name'],
			'type'        => $field['type'],
			'meta'        => $field['meta'],
			'required'    => $field['is_required'],
			'description' => $field['description'],
			'placeholder' => apply_filters( 'wpum_profile_field_placeholder', null, $field ),
			'options'     => apply_filters( 'wpum_profile_field_options', null, $field ),
			'value'       => apply_filters( 'wpum_profile_field_value', null, $field )
		);

	}

	// Remove password field from here
	unset( $fields['password'] );

	// The username cannot be changed, let's remove that field since it's useless
	unset( $fields['username'] );
	
	// Remove the user avatar field if not enabled
	if( ! wpum_get_option( 'custom_avatars' ) )
		unset( $fields['user_avatar'] );

	return apply_filters( 'wpum_get_account_fields', $fields );

}

/**
 * Displays the html of a field within a form.
 *
 * @since 1.0.0
 * @return mixed
 */
function wpum_get_field_input_html( $key, $field ) {

	if( wpum_field_type_exists( $field['type'] ) ) {

		$object = wpum_get_field_type_object( $field['type'] );

		if ( method_exists( $object->class, "input_html" ) ) {
			echo call_user_func( $object->class . "::input_html", $key, $field );
		} else {
			get_wpum_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) );
		}

	} else {

		echo __( 'This field type has no output', 'wpum' );

	}

}

/**
 * Wrapper function to install groups database table and install primary group.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_install_groups() {

	if( ! get_option( 'wpum_version_upgraded_from' ) ) {

		// Create database table for field groups
		@WPUM()->field_groups->create_table();
		
		// Add primary group
		$field_groups_args = array(
			'id'         => 1,
			'name'       => 'Primary',
			'can_delete' => false,
			'is_primary' => true
		);
		WPUM()->field_groups->add( $field_groups_args );

	}

}

/**
 * Wrapper function to install fields database table and install primary fields.
 *
 * @since 1.0.0
 * @return void
 */
function wpum_install_fields() {

	if( ! get_option( 'wpum_version_upgraded_from' ) ) {

		// Create database table for field groups
		@WPUM()->fields->create_table();

		// Get primary group id
		$primary_group = WPUM()->field_groups->get_group_by( 'primary' );

		// Install fields
		$fields = array(
			array(
				'id'                   => 1,
				'group_id'             => $primary_group->id,
				'type'                 => 'username',
				'name'                 => 'Username',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'username',
			),
			array(
				'id'                   => 2,
				'group_id'             => $primary_group->id,
				'type'                 => 'email',
				'name'                 => 'Email',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'user_email',
			),
			array(
				'id'                   => 3,
				'group_id'             => $primary_group->id,
				'type'                 => 'password',
				'name'                 => 'Password',
				'is_required'          => true,
				'show_on_registration' => true,
				'can_delete'           => false,
				'meta'                 => 'password',
			),
			array(
				'id'                   => 4,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'First Name',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'first_name',
			),
			array(
				'id'                   => 5,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'Last Name',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'last_name',
			),
			array(
				'id'                   => 6,
				'group_id'             => $primary_group->id,
				'type'                 => 'nickname',
				'name'                 => 'Nickname',
				'is_required'          => true,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'nickname',
			),
			array(
				'id'                   => 7,
				'group_id'             => $primary_group->id,
				'type'                 => 'display_name',
				'name'                 => 'Display Name',
				'is_required'          => true,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'display_name',
			),
			array(
				'id'                   => 8,
				'group_id'             => $primary_group->id,
				'type'                 => 'text',
				'name'                 => 'Website',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'user_url',
			),
			array(
				'id'                   => 9,
				'group_id'             => $primary_group->id,
				'type'                 => 'textarea',
				'name'                 => 'Description',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'description',
			),
			array(
				'id'                   => 10,
				'group_id'             => $primary_group->id,
				'type'                 => 'avatar',
				'name'                 => 'Profile Picture',
				'is_required'          => false,
				'show_on_registration' => false,
				'can_delete'           => false,
				'meta'                 => 'user_avatar',
			)
		);
		
		foreach ( $fields as $field ) {
			WPUM()->fields->add( $field );
		}

	}

}
