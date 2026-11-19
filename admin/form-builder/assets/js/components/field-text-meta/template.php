<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
    <div class="wpuf-flex">
        <label
            :for="option_field.title"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2">
        <input
            type="text"
            v-model="value"
            :readonly="option_field.is_read_only || is_integration_key"
            :class="builder_class_names('text')">
        <p v-if="is_integration_key" class="wpuf-mt-1 wpuf-text-xs wpuf-text-gray-500"><?php esc_html_e( 'This meta key is used by The Events Calendar and can not be changed.', 'wp-user-frontend' ); ?></p>
    </div>
</div>
