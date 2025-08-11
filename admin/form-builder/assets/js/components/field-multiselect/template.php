<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-select">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <select
        :class="['term-list-selector']"
        class="wpuf-w-full wpuf-mt-2 wpuf-border-primary wpuf-z-30"
        v-model="value"
        multiple
    >
        <option
            class="checked:wpuf-bg-primary"
            v-for="(option, key) in dynamic_options"
            :value="key">{{ option }}</option>
    </select>
</div>
