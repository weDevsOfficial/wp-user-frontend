<div id="wpuf-media-dialog" style="display: none;">

    <div class="wpuf-popup-container">

        <h3><?php _e( 'Select a form to insert', 'wpuf' ); ?></h3>

        <?php $form_types = apply_filters( 'wpuf_shortcode_dialog_form_type', array(
            'post'         => __( 'Post Form', 'wpuf' ),
            'registration' => __( 'Registration Form', 'wpuf' ),
        ) ); ?>

        <div class="wpuf-div">
            <label for="wpuf-form-type" class="label"><?php _e( 'Form Type', 'wpuf' ); ?></label>
            <select id="wpuf-form-type">

                <?php foreach ( $form_types as $key => $form_type ) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $form_type; ?></option>
                <?php } ?>

            </select>
        </div>

        <?php foreach ( $form_types as $key => $form_type ) {

            switch ( $key ) {
                case 'post':
                    $form_post_type = 'wpuf_forms';
                    break;

                case 'registration':
                    $form_post_type = 'wpuf_profile';
                    break;

                default:
                    $form_post_type = apply_filters( 'wpuf_shortcode_dialog_form_type_post', $key, $form_types );
                    break;

            } ?>

            <div class="wpuf-div show-if-<?php echo $key; ?>">

                <label for="wpuf-form-<?php echo $key; ?>" class="label"><?php echo $form_type; ?></label>

                <select id="wpuf-form-<?php echo $key; ?>">

                    <?php
                    $args = array(
                        'post_type'   => $form_post_type,
                        'post_status' => 'publish',
                    );
                    $form_posts = get_posts( $args );

                    foreach ($form_posts as $form) { ?>

                        <option value="<?php echo $form->ID; ?>"><?php echo $form->post_title; ?></option>

                    <?php } ?>

                </select>

            </div>

        <?php }

        do_action( 'wpuf_shortcode_dialog_content', $form_types ); ?>

        <div class="submit-button wpuf-div">
            <button id="wpuf-form-insert" class="button-primary"><?php _e( 'Insert Form', 'wpuf' ); ?></button>
            <button id="wpuf-form-close" class="button-secondary" style="margin-left: 5px;" onClick="tb_remove();"><?php _e( 'Close', 'wpuf' ); ?></a>
        </div>

    </div>
</div>

<style type="text/css">
    .wpuf-popup-container {
        padding: 15px 0 0 20px;
    }
    .wpuf-div {
        padding: 10px;
        clear: left;
    }
    .wpuf-div label.label {
        float: left;
        width: 25%;
    }
</style>