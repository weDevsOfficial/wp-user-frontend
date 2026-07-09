<?php
/**
 * Form Builder v4.1 — React mount point.
 *
 * React renders the entire UI inside #wpuf-form-builder-app.
 * Hidden inputs are preserved for the save handler.
 *
 * Variables available from Admin_Form_Builder::include_form_builder():
 *   $form_id, $form_type, $post_type, $form_settings_key, $shortcodes, $forms
 */
?>
<form id="wpuf-form-builder"
    class="!wpuf-bg-white !wpuf-static !wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] !wpuf-p-0 wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>"
    method="post"
    action="">

    <?php // React mounts here ?>
    <div id="wpuf-form-builder-app"></div>

    <?php if ( ! empty( $form_settings_key ) ) { ?>
        <input type="hidden" name="form_settings_key" value="<?php echo esc_attr( $form_settings_key ); ?>">
    <?php } ?>

    <?php wp_nonce_field( 'wpuf_form_builder_save_form', 'wpuf_form_builder_nonce' ); ?>

    <input type="hidden" name="wpuf_form_id" value="<?php echo esc_attr( $form_id ); ?>">
</form>
