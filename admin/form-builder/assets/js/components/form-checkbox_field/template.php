<div class="wpuf-fields">
    <ul :class="['wpuf-fields-list', ('yes' === field.inline) ? 'wpuf-list-inline' : '']">
        <li v-if="has_options" v-for="(label, val) in field.options">
            <label>
                <input
                    type="checkbox"
                    :value="label"
                    :checked="is_selected(label)"
                    :class="class_names('checkbox_btns')"
                > {{ label }}
                <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
            </label>
        </li>
    </ul>
</div>
