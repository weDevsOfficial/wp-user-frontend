<?php

function wpuf_plugin_options() {
    global $wpdb, $wpuf_options;
    ?>

    <div class="wrap wpuf-admin">

        <div id="icon-options-general" class="icon32"><br></div>
        <h2>WP User Frontend: Management Options</h2>

        <div id="option-saved"><?php _e( 'Options saved', 'wpuf' ); ?></div>

        <?php
        if ( isset( $_POST['options_submit'] ) ) {
            //var_dump($_POST);
            foreach ($_POST as $key => $value) {

                if ( wpuf_starts_with( $key, 'wpuf_' ) ) {
                    update_option( $key, wpuf_clean_tags( $value ) );
                    //echo "$key => $value <br>";
                }
            }
        }
        //echo get_option('wpuf_allow_tags');
        ?>

        <form method="post" action="" class="wpuf_admin">
            <?php wp_nonce_field( 'update-options' ); ?>

            <p class="submit">
                <input type="submit" name="options_submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
            </p>

            <table class="widefat options" style="width: 650px">

                <?php wpuf_build_form( $wpuf_options ); ?>

            </table>

            <p class="submit">
                <input type="hidden" name="action" value="wpuf_admin_ajax_action">
                <input type="submit" name="options_submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
            </p>

        </form>

    </div>
    <?php
}

if ( is_admin() ) {
    add_action( 'wp_ajax_wpuf_admin_ajax_action', 'wpuf_admin_ajax' );
}

/**
 * Handles admin options settings submission with ajax
 *
 */
function wpuf_admin_ajax() {

    foreach ($_POST as $key => $val) {
        $_POST[$key] = esc_attr( $val );
    }

    foreach ($_POST as $key => $value) {
        //update the input fields, whose names starts with symple_
        if ( wpuf_starts_with( $key, 'wpuf_' ) ) {
            //echo "$key => $value <br>";
            update_option( $key, wpuf_clean_tags( $value ) );
            //echo "$key => $value \n";
        } //starts with
    } //foreach

    echo __( 'Settings Saved', 'wpuf' );
    //print_r($_POST);

    exit;
}
