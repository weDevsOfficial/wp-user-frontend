<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-radio">
    <div class="wpuf-flex">
        <label
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div
        v-if="option_field.inline"
        class="wpuf-flex">
        <div
            v-for="(option, key, index) in option_field.options"
            class="wpuf-items-center">
            <label
                :class="index !== 0 ? 'wpuf-ml-8' : ''"
                class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                <input
                    type="radio"
                    :name="'radio_' + editing_form_field.id + '_' + option_field.name"
                    :value="key"
                    v-model="value"
                    :class="builder_class_names('radio')">
                {{ option }}
            </label>
        </div>
    </div>
    <div
        v-else
        class="wpuf-flex wpuf-items-center"
        :class="index < Object.keys(option_field.options).length - 1 ? 'wpuf-mb-3' : ''"
        v-for="(option, key, index) in option_field.options">
        <label class="!wpuf-mb-0">
            <input
                type="radio"
                :name="'radio_' + editing_form_field.id + '_' + option_field.name"
                :value="key"
                v-model="value"
                :class="builder_class_names('radio')">
            {{ option }}
        </label>

    </div>
</div>
