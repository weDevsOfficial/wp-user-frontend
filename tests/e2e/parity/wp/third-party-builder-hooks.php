<?php
/**
 * Plugin Name: WPUF parity third-party builder hooks
 * Description: Test fixture (task 2.6, PAR0022). Acts as another plugin printing
 * markup on the PHP form builder hooks the React builder bridges. Copied into
 * mu-plugins by the spec and removed afterwards.
 */

add_action(
    'wpuf_after_post_form_settings_field_limit_message',
    function ( $field, $value, $form_settings ) {
        $enabled = isset( $form_settings['tp_enabled'] ) && 'yes' === $form_settings['tp_enabled'];
        $label   = isset( $form_settings['tp_label'] ) ? $form_settings['tp_label'] : '';
        ?>
        <p class="tp-row">
            <label><input type="checkbox" class="tp-enabled" name="wpuf_settings[tp_enabled]" value="yes" <?php checked( $enabled ); ?>> TP enabled</label>
            <input type="text" class="tp-label" name="wpuf_settings[tp_label]" value="<?php echo esc_attr( $label ); ?>" onclick="alert(1)">
            <script>window.tpScriptRan = true;</script>
        </p>
        <?php
    },
    10,
    3
);

add_action(
    'wpuf-form-builder-tabs-post',
    function () {
        echo '<a class="tp-tab" href="#tp-tab">TP tab</a>';
    }
);

add_action(
    'wpuf_form_builder_settings_tabs_post',
    function () {
        $form_settings = wpuf_get_form_settings( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $extra         = isset( $form_settings['tp_extra'] ) ? $form_settings['tp_extra'] : '';
        echo '<div class="tp-settings">TP settings <input type="text" class="tp-extra" name="wpuf_settings[tp_extra]" value="' . esc_attr( $extra ) . '"></div>';
    }
);
