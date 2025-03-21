<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            :for="option_field.name"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
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
