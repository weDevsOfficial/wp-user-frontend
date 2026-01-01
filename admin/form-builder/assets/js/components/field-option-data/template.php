<div class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            class="wpuf-font-sm wpuf-text-gray-700">{{ option_field.title }}
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <div class="wpuf-mt-2 wpuf-flex">
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700">
            <input
                type="checkbox"
                v-model="show_value"
                :class="builder_class_names('checkbox')"
                class="!wpuf-mr-2" />
            <?php esc_attr_e( 'Show values', 'wp-user-frontend' ); ?>
        </label>
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700 wpuf-ml-8">
            <input
                type="checkbox"
                v-model="sync_value"
                :class="builder_class_names('checkbox')"
                class="!wpuf-mr-2" />
            <?php esc_attr_e( 'Sync values', 'wp-user-frontend' ); ?>
        </label>
    </div>

    <div class="wpuf-mt-4">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-2">
            <span class="wpuf-text-[14px] wpuf-text-gray-700 wpuf-font-medium"><?php esc_attr_e( 'Label & Values', 'wp-user-frontend' ); ?></span>
            <?php do_action( 'wpuf_field_option_data_actions' ); ?>
        </div>
        <table class="option-field-option-chooser">
            <tbody>
                <tr
                v-for="(option, index) in options"
                :key="option.id"
                :data-index="index"
                class="option-field-option wpuf-flex wpuf-justify-start wpuf-items-center">
                    <td class="wpuf-flex wpuf-items-center">
                        <input
                            v-if="option_field.is_multiple"
                            type="checkbox"
                            :value="option.value"
                            v-model="selected"
                            :class="builder_class_names('checkbox')"
                        >
                        <input
                            v-else
                            type="radio"
                            :value="option.value"
                            v-model="selected"
                            class="!wpuf-mt-0"
                            :class="builder_class_names('radio')"
                        >
                        <i class="fa fa-bars sort-handler hover:!wpuf-cursor-move wpuf-text-gray-400 wpuf-ml-1"></i>
                    </td>
                    <td>
                        <input
                            :class="[builder_class_names('text'), '!wpuf-w-full']"
                            type="text"
                            v-model="option.label"
                            @input="set_option_label(index, option.label)">
                    </td>
                    <td v-if="show_value">
                        <input
                            :class="[builder_class_names('text'), '!wpuf-w-full']"
                            type="text"
                            v-model="option.value">
                    </td>
                    <td>
                        <div class="wpuf-flex wpuf-ml-2">
                            <div
                                @click="delete_option(index)"
                                class="action-buttons hover:wpuf-cursor-pointer">
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
                                @click="add_option"
                                class="plus-buttons hover:wpuf-cursor-pointer !wpuf-border-0">
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
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <a
        v-if="!option_field.is_multiple && selected"
        class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 wpuf-mt-4"
        href="#clear"
        @click.prevent="clear_selection">
        <?php esc_attr_e( 'Clear Selection', 'wp-user-frontend' ); ?>
    </a>
    <?php do_action( 'wpuf_field_option_data_after' ); ?>
</div>
