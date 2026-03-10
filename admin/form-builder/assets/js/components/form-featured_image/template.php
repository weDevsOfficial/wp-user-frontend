<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
               :class="builder_class_names('upload_btn')" href="#">
                <template v-if="field.button_label === ''">
                    <?php esc_html_e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpuf-size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
