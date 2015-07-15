<?php
/**
 * Handles the declaration of supported field types.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Abstract WPUM_Field_Type class.
 *
 * @abstract
 * @author      Alessandro Tesoro
 */
abstract class WPUM_Field_Type {

	/**
	 * @since 1.0.0
	 * @var string The name of this field type
	 */
	public $name = '';

	/**
	 * @since 1.0.0
	 * @var string The type of this field
	 */
	public $type = '';

	/**
	 * The name of the category that this field type should be grouped with.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $category = '';

	/**
	 * If this is set, the editor will allow creation of options (e.g checkbox, selectbox).
	 *
	 * @since 1.0.0
	 * @var bool Does this field support options? e.g. selectbox, radio buttons, etc.
	 */
	public $supports_options = false;

	/**
	 * If this is set, the editor will allow creation of multiple types of this field.
	 *
	 * @since 1.0.0
	 * @var bool Can this field be added multiple times to a group?.
	 */
	public $supports_multiple = true;

	/**
	 * If this is set, the editor will allow to decide whether this field can be displayed on registration form.
	 *
	 * @since 1.0.0
	 * @var bool.
	 */
	public $set_registration = false;

	/**
	 * If this is set, the editor will allow to decide whether this field can change it's requirement setting.
	 *
	 * @since 1.0.0
	 * @var bool.
	 */
	public $set_requirement = true;

	/**
	 * Class name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $class = '';

	/**
	 * Get things started
	 *
	 * @since   1.0.0
	 */
	function __construct() {

		add_filter( "wpum/field/types", array( $this, 'get_field_types' ), 10, 1 );
		add_filter( "wpum/field/types/classes", array( $this, 'get_field_classes' ), 11, 1 );

	}

	/**
	 * Get supported field types
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_field_types( $fields ) {
		
		$l10n = array(
			'basic' => __('Basic', 'wpum'),
		);
		
		// If no category is selected - add it to the basic category
		if( ! $this->category ) {
			$this->category = 'basic';
		}
		
		// Set the category
		if( isset( $l10n[ $this->category ] ) ) {
			$cat = $l10n[ $this->category ];
		} else {
			$cat = $this->category;
		}
		
		// add to array
		$fields[ $cat ][ $this->type ] = $this->name;
		
		// return array
		return $fields;

	}

	/**
	 * Get registered field php class names.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_field_classes( $classes ) {
		
		// add to array
		$classes[ $this->type ] = $this->class;
		
		// return array
		return $classes;

	}

	/**
	 * Use this method to register options for the field.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public static function options() {
		return array();
	}

}