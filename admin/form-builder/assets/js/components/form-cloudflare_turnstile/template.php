<div class="wpuf-fields">
    <template v-if="!has_turnstile_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
        <img
            class="wpuf-turnstile-placeholder"
            :src="turnstile_image"
            alt="">
    </template>
</div>
