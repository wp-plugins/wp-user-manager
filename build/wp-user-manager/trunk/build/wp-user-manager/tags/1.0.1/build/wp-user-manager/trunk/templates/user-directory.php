<?php
/**
 * WPUM Template: User Directory.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Return different template if set.
if( $directory_args['template'] ) {
	get_wpum_template( "user-directory-{$directory_args['template']}.php", array( 
			'directory_args' => $directory_args
		) 
	);
	return;
}

?>

<!-- start directory -->
<div id="wpum-user-directory-<?php echo $directory_args['directory_id']; ?>" class="wpum-user-directory directory-<?php echo $directory_args['directory_id']; ?>">

	<!-- Start Users list -->
	<?php if ( ! empty( $directory_args['user_data'] ) ) {

		do_action( 'wpum_before_user_directory', $directory_args );

		echo '<ul class="wpum-user-listings">';

		foreach ( $directory_args['user_data'] as $user ) {

			// Load single-user.php template to display each user individually
			get_wpum_template( "directory/single-user.php", array( 'user' => $user ) );

		}

		echo "</ul>";

		do_action( 'wpum_after_user_directory', $directory_args );

	} else {
	
		$args = array( 
			'id'   => 'wpum-no-user-found', 
			'type' => 'notice', 
			'text' => __( 'No users have been found', 'wpum' )
		);
		$warning = wpum_message( $args, true );

	} ?>

	<!-- end users list -->

</div>
<!-- end directory -->