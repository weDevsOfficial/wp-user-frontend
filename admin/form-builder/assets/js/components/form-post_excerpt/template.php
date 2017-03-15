<div class="wpuf-fields">
    <textarea
        :class="class_names('textareafield')"
        :placeholder="field.placeholder"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>
    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
