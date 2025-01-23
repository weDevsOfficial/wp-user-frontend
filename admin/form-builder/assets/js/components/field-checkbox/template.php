<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-checkbox wpuf-mb-6">
    <label v-if="option_field.title" :class="option_field.title_class">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>
    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options" class="wpuf-mt-2">
            <label>
                <input type="checkbox" :class="builder_class_names('checkbox')" :value="key" v-model="value"> {{ option }}
            </label>
        </li>
    </ul>
</div>
