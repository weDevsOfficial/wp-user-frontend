<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
    <div class="wpuf-flex">
        <label
            :for="option_field.title"
            class="wpuf-font-sm wpuf-text-gray-900">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2">
        <input
            type="text"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
            class="wpuf-block wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300 wpuf-max-w-full">
    </div>
</div>
