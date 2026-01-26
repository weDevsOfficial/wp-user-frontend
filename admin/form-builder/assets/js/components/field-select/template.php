<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-select">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <div class="option-fields-section wpuf-relative">
        <div
            @click="showOptions = !showOptions"
            class="wpuf-my-4 wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base"
        >
            {{ selectedOption }}
            <i
                :class="showOptions ? 'fa-angle-up' : 'fa-angle-down'"
                class="fa wpuf-text-base"></i>
        </div>

        <div
            v-if="showOptions"
            class="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-40 wpuf--mt-4">
            <ul>
                <li
                    v-for="(option, key) in option_field.options"
                    @click="[value = key, showOptions = false, selectedOption = option]"
                    :value="key"
                    class="wpuf-text-sm wpuf-color-gray-900 wpuf-py-2 wpuf-px-4 hover:wpuf-cursor-pointer hover:wpuf-bg-gray-100">{{ option }}</li>
            </ul>
        </div>
    </div>
</div>
