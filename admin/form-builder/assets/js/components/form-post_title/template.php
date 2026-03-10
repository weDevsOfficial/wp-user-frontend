<div class="wpuf-fields">
    <input
        type="text"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        :class="builder_class_names('text')"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
