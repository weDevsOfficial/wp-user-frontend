<div class="wpuf-fields">
    <select
        :class="builder_class_names('multi_label')"
        class="wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300"
        multiple
    >
        <option v-if="field.first" value="">{{ field.first }}</option>

        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
