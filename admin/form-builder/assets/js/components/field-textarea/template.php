<div class="panel-field-opt panel-field-opt-textarea">
    <div class="wpuf-flex">
        <label class="wpuf-mb-2">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <textarea :class="builder_class_names('textareafield')" :rows="option_field.rows || 5" v-model="value"></textarea>
</div>
