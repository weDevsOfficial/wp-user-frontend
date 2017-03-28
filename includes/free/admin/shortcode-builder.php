<div id="wpuf-select-form" style="display: none;">

    <div class="wpuf-popup-container">

        <h3><?php _e( 'Select a form to insert', 'wpuf' ); ?></h3>

        <div class="wpuf-div">
            <label for="wpuf-form-type" class="label"><?php _e( 'Form Type', 'wpuf' ); ?></label>

            <select id="wpuf-form-type">
                <option value="post"><?php _e( 'Post Form', 'wpuf' ); ?></option>
                <option value="registration"><?php _e( 'Registration Form', 'wpuf' ); ?></option>
            </select>
        </div>

        <div class="wpuf-div show-if-post">
            <label for="wpuf-form-post" class="label"><?php _e( 'Post Form', 'wpuf' ); ?></label>
            <select id="wpuf-form-post">
                <?php 
                $args = array(
                    'post_type'        => 'wpuf_forms',
                    'post_status'      => 'publish',
                    );
                $posts_array = get_posts( $args );

                foreach ($posts_array as $post) {
                    ?>
                    <option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>

        <div class="wpuf-div show-if-registration">
            <label for="wpuf-form-registration" class="label"><?php _e( 'Registration Form', 'wpuf' ); ?></label>

            <select id="wpuf-form-registration">
                <?php 
                $args = array(
                    'post_type'        => 'wpuf_profile',
                    'post_status'      => 'publish',
                    );
                $posts_array = get_posts( $args );

                foreach ($posts_array as $post) {
                    ?>
                    <option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>

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