<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label>
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
            {{ option_field.min_column }}
        </label>
    </div>
    <input
        type="range"
        v-model="value"
        v-bind:min="minColumn"
        v-bind:max="maxColumn"
    >
</div>
