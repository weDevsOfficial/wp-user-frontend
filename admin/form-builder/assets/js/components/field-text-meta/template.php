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
            :class="builder_class_names('text') + (option_field.css_class ? ' ' + option_field.css_class : '')"
            :readonly="option_field.readonly === true"
            :style="option_field.custom_attrs && option_field.custom_attrs.style ? option_field.custom_attrs.style : ''"
            :disabled="option_field.disabled === true">
    </div>
</div>
