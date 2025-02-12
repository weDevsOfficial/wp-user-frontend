<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            :for="option_field.name"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-900">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2">
        <input
            v-if="option_field.variation && 'number' === option_field.variation"
            type="number"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
            :class="builder_class_names('text')">

        <input
            v-if="!option_field.variation"
            type="text"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
            :class="builder_class_names('text')">
    </div>
</div>
