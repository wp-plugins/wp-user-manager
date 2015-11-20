<?php
/**
 * Registers the password type field.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Field_Type_Password Class
 *
 * @since 1.0.0
 */
class WPUM_Field_Type_Password extends WPUM_Field_Type {

	/**
	 * Constructor for the field type
	 *
	 * @since 1.0.0
 	 */
	public function __construct() {

		// DO NOT DELETE
		parent::__construct();

		// Label of this field type
		$this->name              = _x( 'Password', 'field type name', 'wpum' );
		// Field type name
		$this->type              = 'password';
		// Class of this field
		$this->class             = __CLASS__;
		// Set registration
		$this->set_registration  = false;
		// Set requirement
		$this->set_requirement   = false;
		// Cannot be used multiple times.
		$this->supports_multiple = false;

	}

}

new WPUM_Field_Type_Password;
