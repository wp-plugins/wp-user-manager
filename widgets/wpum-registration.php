<?php
/**
 * Registration Form Widget.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Registration_Form_Widget Class
 *
 * @since 1.0.0
 */
class WPUM_Registration_Form_Widget extends WPH_Widget {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Configure widget array
		$args = array(
			'label'       => __( '[WPUM] Registration Form', 'wpum' ),
			'description' => __( 'Display the registration form.', 'wpum' ),
		);

		$args['fields'] = array(
			array(
				'name'   => __( 'Title', 'wpum' ),
				'id'     => 'title',
				'type'   => 'text',
				'class'  => 'widefat',
				'std'    => __( 'Register', 'wpum' ),
				'filter' => 'strip_tags|esc_attr'
			),
			array(
				'name'     => __( 'Display login link', 'wpum' ),
				'id'       => 'login_link',
				'type'     =>'checkbox',
				'std'      => 1,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'     => __( 'Display password recovery link', 'wpum' ),
				'id'       => 'psw_link',
				'type'     =>'checkbox',
				'std'      => 1,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'     => __( 'Display registration link', 'wpum' ),
				'id'       => 'register_link',
				'type'     =>'checkbox',
				'std'      => 0,
				'filter'   => 'strip_tags|esc_attr',
			),
			array(
				'name'   => __( 'Custom form ID (optional)', 'wpum' ),
				'id'     => 'form_id',
				'type'   => 'text',
				'class'  => 'widefat',
				'filter' => 'strip_tags|esc_attr'
			),
		);

		// create widget
		$this->create_widget( $args );

	}

	/**
	 * Display widget content.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$output = $args['before_widget'];
		$output .= $args['before_title'];
		$output .= $instance['title'];
		$output .= $args['after_title'];

		$atts = array(
			'form_id'       => $instance['form_id'],
			'login_link'    => $instance['login_link'],
			'psw_link'      => $instance['psw_link'],
			'register_link' => $instance['register_link']
		);

		// Set default values
		if( !array_key_exists('form_id', $atts) || empty($atts['form_id']) )
			$atts['form_id'] = 'default_registration_form';

		$output .= WPUM()->forms->get_form( 'register', $atts );

		$output .= $args['after_widget'];

		echo $output;

	}

}
