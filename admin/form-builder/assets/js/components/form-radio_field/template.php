<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-2">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input
                type="radio"
                :value="val"
                :checked="is_selected(val)"
                :id="'radio-' + field.name + '-' + val"
                :class="builder_class_names('radio')">
            <label :for="'radio-' + field.name + '-' + val">{{ label }}</label>
        </div>
    </div>

    <div
        v-else
        class="wpuf-space-y-6 sm:wpuf-flex sm:wpuf-items-center sm:wpuf-space-x-10 sm:wpuf-space-y-0">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input
                type="radio"
                :value="val"
                :checked="is_selected(val)"
                :id="'radio-' + field.name + '-' + val"
                :class="builder_class_names('radio')">
            <label :for="'radio-' + field.name + '-' + val">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
