<div class="wpuf-fields">
    <ul :class="['wpuf-fields-list', ('yes' === field.inline) ? 'wpuf-list-inline' : '']">
        <li v-if="has_options" v-for="(label, val) in field.options">
            <label>
                <input
                    type="radio"
                    :value="label"
                    :checked="is_selected(label)"
                    :class="class_names('radio_btns')"
                > {{ label }}
            </label>
        </li>
    </ul>
</div>
