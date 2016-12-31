<div class="panel-field-opt panel-field-opt-text">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        <input
            type="text"
            v-model="value"
            @focusout="on_focusout"
        >
    </label>
</div>
