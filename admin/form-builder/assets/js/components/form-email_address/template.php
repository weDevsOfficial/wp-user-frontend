<div class="wpuf-fields">
    <input
        type="email"
        :class="class_names('email') + builder_class_names('text')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
