<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-2">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input
                type="radio"
                class="wpuf-border-gray-300 wpuf-text-indigo-600 !wpuf-m-0">
            <label
                :value="val"
                :checked="is_selected(val)"
                :class="builder_class_names('radio_btns')">{{ label }}</label>
        </div>
    </div>

    <div
        v-else
        class="wpuf-space-y-6 sm:wpuf-flex sm:wpuf-items-center sm:wpuf-space-x-10 sm:wpuf-space-y-0">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input type="radio" class="wpuf-h-4 wpuf-w-4 wpuf-border-gray-300 wpuf-text-indigo-600 !wpuf-m-0">
            <label
                :value="val"
                :checked="is_selected(val)"
                :class="class_names('radio_btns')"
                class="wpuf-ml-3 wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
