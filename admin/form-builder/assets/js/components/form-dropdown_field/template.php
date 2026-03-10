<div class="wpuf-fields">
    <select
        :class="builder_class_names('dropdown')">
        <option v-if="field.first" value="">{{ field.first }}</option>
        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
