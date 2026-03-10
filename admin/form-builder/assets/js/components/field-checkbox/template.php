<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-checkbox wpuf-mb-6">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                <input type="checkbox" :class="builder_class_names('checkbox')" class="!wpuf-mr-2" :value="key" v-model="value">
                {{ option }}
            </label>
        </li>
    </ul>
</div>
