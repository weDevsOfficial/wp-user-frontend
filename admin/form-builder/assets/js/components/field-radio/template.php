<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-radio">
    <div class="wpuf-flex">
        <label
            class="wpuf-font-sm wpuf-text-gray-900">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div
        v-if="!option_field.inline"
        class="wpuf-flex wpuf-items-center wpuf-gap-x-2 wpuf-m-2"
        v-for="(option, key) in option_field.options">
        <label
            class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
        </label>
        <input
            type="radio"
            :value="key"
            v-model="value"
            :class="builder_class_names('radio')">
            {{ option }}
    </div>

    <div
        v-if="option_field.inline"
        class="wpuf-mt-2 wpuf-flex">
        <div
            v-for="(option, key, index) in option_field.options"
            class="wpuf-items-center">
            <label
                :class="index !== 0 ? 'wpuf-ml-2' : ''"
                class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
                <input
                    type="radio"
                    :value="key"
                    v-model="value"
                    :class="builder_class_names('radio')">
                {{ option }}
            </label>
        </div>
    </div>
</div>
