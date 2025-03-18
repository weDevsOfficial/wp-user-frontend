<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a
                class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
                :class="builder_class_names('upload_btn')" href="#">
                <template v-if="field.button_label === ''">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
                        <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                    </svg>
                    <?php _e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
                        <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                    </svg>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
