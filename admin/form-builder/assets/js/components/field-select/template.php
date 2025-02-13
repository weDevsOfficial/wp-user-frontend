<div class="panel-field-opt panel-field-opt-select">
    <label v-if="option_field.title">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <div class="option-fields-section wpuf-relative">
        <p
            @click="showOptions = !showOptions"
            class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-min-w-full wpuf-rounded-md wpuf-py-1 wpuf-px-2 wpuf-text-gray-900 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300 wpuf-max-w-full hover:wpuf-cursor-pointer"
        >
            {{ selectedOption }}
            <i
                :class="showOptions ? 'fa-angle-up' : 'fa-angle-down'"
                class="fa"></i>
        </p>

        <div
            v-if="showOptions"
            class="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-10 wpuf--mt-4">
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
