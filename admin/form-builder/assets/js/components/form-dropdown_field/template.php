<div class="wpuf-fields">
    <select
        disabled
        :class="class_names('select_lbl')"
        class="wpuf-block wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-border-0 wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">
        <option v-if="field.first" value="">{{ field.first }}</option>
        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
