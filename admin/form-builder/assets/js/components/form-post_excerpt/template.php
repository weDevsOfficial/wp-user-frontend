<div class="wpuf-fields">
    <textarea
        v-if="'no' === field.rich"
        :class="builder_class_names('textareafield')"
        :placeholder="field.placeholder"
        :default_text="field.default"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich" :default_text="field.default"></text-editor>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
