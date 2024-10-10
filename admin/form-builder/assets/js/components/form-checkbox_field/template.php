<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-5">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-start">
            <div class="wpuf-items-center">
                <input
                    type="checkbox"
                    :value="val"
                    :checked="is_selected(val)"
                    :class="class_names('checkbox_btns')"
                    class="wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600 focus:wpuf-ring-indigo-600">
            </div>
            <div class="wpuf-ml-1 wpuf-text-sm">
                <label class="wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
            </div>
        </div>
    </div>

    <div
        v-if="field.inline === 'yes'"
        class="wpuf-flex"
    >
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-start wpuf-mr-4">
            <div class="wpuf-items-center">
                <input
                    type="checkbox"
                    :value="val"
                    :checked="is_selected(val)"
                    :class="class_names('checkbox_btns')"
                    class="wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600 focus:wpuf-ring-indigo-600">
            </div>
            <div class="wpuf-ml-1 wpuf-text-sm">
                <label class="wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
            </div>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
