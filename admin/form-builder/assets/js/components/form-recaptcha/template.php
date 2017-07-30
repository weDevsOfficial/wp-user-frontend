<div class="wpuf-fields">
    <template v-if="!has_recaptcha_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
        <img class="wpuf-recaptcha-placeholder" src="<?php echo WPUF_ASSET_URI . '/images/recaptcha-placeholder.png' ?>" alt="">
    </template>
</div>
