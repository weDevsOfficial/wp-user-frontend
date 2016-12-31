<div class="panel-field-opt panel-field-opt-radio">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>
    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label>
                <input type="radio" :value="key" v-model="value"> {{ option }}
            </label>
        </li>
    </ul>
</div>
