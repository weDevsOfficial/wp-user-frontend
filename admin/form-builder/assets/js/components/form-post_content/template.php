<div class="wpuf-fields">
    <div
        v-if="field.insert_image === 'yes'"
        class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
        <a
            class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
            :class="builder_class_names('upload_btn')" href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
            <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
            </svg>
            <?php _e( 'Insert Photo', 'wp-user-frontend' ); ?>
        </a>
    </div>
    <br v-if="field.insert_image === 'yes'" />

    <textarea
        v-if="'no' === field.rich"
        :class="builder_class_names('textareafield')"
        :placeholder="field.placeholder"
        :default_text="field.default"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich" :default_text="field.default"></text-editor>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
