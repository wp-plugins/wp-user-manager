<?php
/**
 * WPUM Template: Directory search form.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */
?>
<div class="wpum-directory-search-form-wrapper">

  <form action="#" method="post" id="wpum-directory-search-form-<?php echo $directory_args['directory_id']; ?>" class="wpum-directory-search-form" name="wpum-directory-search-form">

    <div class="form-fields">

      <?php do_action( 'wpum_directory_search_form_top_fields', $directory_args ); ?>

      <?php
        $search_input = array(
          'name'        => 'search_user',
          'value'       => isset( $_POST['search_user'] ) ? sanitize_text_field( $_POST['search_user'] ) : '',
          'placeholder' => esc_html__( 'Search for users', 'wpum' ),
        );
        echo WPUM()->html->text( $search_input );
      ?>

      <?php do_action( 'wpum_directory_search_form_bottom_fields', $directory_args ); ?>

    </div>

    <div class="form-submit">
      <input type="submit" name="wpum_submit_user_search" id="wpum-submit-user-search" class="button wpum-button" value="<?php esc_html_e( 'Search', 'wpum' ); ?>">
    </div>

    <div class="wpum-clearfix"></div>

  </form>

</div>
