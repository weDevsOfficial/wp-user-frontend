<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-2">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-center">
            <div class="wpuf-flex wpuf-items-center">
                <input
                    type="checkbox"
                    :value="val"
                    :checked="is_selected(val)"
                    :class="class_names('checkbox_btns')"
                    class="wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600 focus:wpuf-ring-indigo-600 !wpuf-mt-0.5">
                <label class="wpuf-ml-3 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
            </div>
        </div>
    </div>

    <div
        v-else
        class="wpuf-flex"
    >
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-mr-4">
            <input
                type="checkbox"
                :value="val"
                :checked="is_selected(val)"
                :class="class_names('checkbox_btns')"
                class="!wpuf-mt-[.5px] wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600">
            <label class="wpuf-ml-1 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
