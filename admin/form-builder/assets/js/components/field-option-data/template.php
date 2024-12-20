<div class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            class="wpuf-font-sm wpuf-text-gray-900">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2 wpuf-flex">
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
            <input
                type="checkbox"
                v-model="show_value"
                class="wpuf-input-checkbox">
            <?php esc_attr_e( 'Show values', 'wp-user-frontend' ); ?>
        </label>
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 wpuf-ml-2">
            <input
                type="checkbox"
                v-model="sync_value"
                class="wpuf-input-checkbox"
            /><?php esc_attr_e( 'Sync values', 'wp-user-frontend' ); ?>
        </label>
    </div>
    <div class="wpuf-grid wpuf-grid-cols-4 wpuf-auto-cols-min wpuf-gap-4 wpuf-mt-4">
        <div></div>
        <div class="wpuf-flex">
            <label class="wpuf-font-sm wpuf-text-gray-900">
                <?php esc_attr_e( 'Label', 'wp-user-frontend' ); ?>
            </label>
            <help-text text="<?php esc_attr_e( 'Do not use & or other special character for option label', 'wp-user-frontend' ); ?>"></help-text>
        </div>
        <div v-if="show_value" class="value">
            <?php esc_attr_e( 'Value', 'wp-user-frontend' ); ?>
        </div>
        <div></div>
    </div>
    <div class="option-field-option-chooser">
        <div
            v-for="(option, index) in options"
            :key="option.id"
            :data-index="index"
            class="wpuf-grid wpuf-grid-cols-[auto_1fr_1fr_auto] wpuf-items-center wpuf-gap-4 wpuf-mb-2 option-field-option hover:wpuf-cursor-pointer">
                <div class="wpuf-flex wpuf-items-center">
                    <div class="selector">
                        <input
                            v-if="option_field.is_multiple"
                            type="checkbox"
                            :value="option.value"
                            v-model="selected"
                            class="wpuf-input-checkbox"
                        >
                        <input
                            v-else
                            type="radio"
                            :value="option.value"
                            v-model="selected"
                            :class="builder_class_names('radio')"
                            class="option-chooser-radio wpuf-ml-3 wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900"
                        >
                    </div>
                    <div class="sort-handler">
                        <i class="fa fa-bars"></i>
                    </div>
                </div>
                <div class="label wpuf-flex">
                    <input
                        class="wpuf-text-sm wpuf-w-full"
                        type="text"
                        v-model="option.label"
                        @input="set_option_label(index, option.label)">
                </div>
                <div
                    v-if="show_value"
                    class="value wpuf-flex">
                    <input
                        class="wpuf-text-sm wpuf-w-full"
                        type="text"
                        v-model="option.value">
                </div>
                <div class="wpuf-flex wpuf-items-center wpuf-gap-1">
                    <div
                        class="action-buttons hover:wpuf-cursor-pointer"
                        @click="delete_option(index)">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 hover:wpuf-border-primary wpuf-p-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>

                    </div>
                    <div
                        v-if="index === options.length - 1"
                        class="plus-buttons hover:wpuf-cursor-pointer"
                        @click="add_option">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="wpuf-ml-1 wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 wpuf-p-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                </div>
        </div>
    </div>
    <a v-if="!option_field.is_multiple && selected" href="#clear" @click.prevent="clear_selection"><?php esc_attr_e( 'Clear Selection', 'wp-user-frontend' ); ?></a>
</div>
