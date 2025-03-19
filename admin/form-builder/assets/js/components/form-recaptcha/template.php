<div class="wpuf-fields">
    <template v-if="!has_recaptcha_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
        <img
            v-if="'invisible_recaptcha' !== field.recaptcha_type"
            class="wpuf-recaptcha-placeholder"
            src="<?php echo esc_url ( WPUF_ASSET_URI . '/images/recaptcha-placeholder.png' ); ?>"
            alt="">
        <div v-else><p><?php esc_html_e( 'Invisible reCaptcha', 'wp-user-frontend' ); ?></p></div>
    </template>
</div>
