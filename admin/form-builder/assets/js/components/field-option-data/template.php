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
            <div class="wpuf-flex wpuf-items-center wpuf-gap-2">
                <span class="wpuf-text-[14px] wpuf-text-gray-700 wpuf-font-medium"><?php esc_attr_e( 'Label & Values', 'wp-user-frontend' ); ?></span>
                <button
                    type="button"
                    @click="open_ai_modal"
                    class="wpuf-w-8 wpuf-h-8 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-shadow-sm hover:wpuf-shadow-md wpuf-border-0"
                    style="background: linear-gradient(135deg, #FFEE00 0%, #D500FF 28%, #0082FF 100%);"
                    title="<?php esc_attr_e( 'AI Generate Options', 'wp-user-frontend' ); ?>">
                    <svg class="wpuf-w-5 wpuf-h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
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

    <!-- AI Generate Options Modal -->
    <div v-if="show_ai_modal" class="wpuf-ai-modal-overlay" @click="close_ai_modal">
        <div class="wpuf-ai-modal" @click.stop>
            <div class="wpuf-ai-modal-header">
                <h3><?php esc_attr_e( 'AI Generate Options', 'wp-user-frontend' ); ?></h3>
                <button type="button" @click="close_ai_modal" class="wpuf-ai-modal-close">&times;</button>
            </div>
            <div class="wpuf-ai-modal-body">
                <label class="wpuf-block wpuf-mb-2 wpuf-text-sm wpuf-font-medium">
                    <?php esc_attr_e( 'Describe the options you need', 'wp-user-frontend' ); ?>
                </label>
                <textarea
                    v-model="ai_prompt"
                    rows="3"
                    class="wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-border wpuf-rounded"
                    placeholder="<?php esc_attr_e( 'e.g., List of US states, Business categories, Job titles', 'wp-user-frontend' ); ?>"
                ></textarea>
                <div v-if="ai_error" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600">{{ ai_error }}</div>
                <div v-if="ai_generated_options.length" class="wpuf-mt-4">
                    <div class="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mb-2">
                        <span class="wpuf-text-sm wpuf-font-medium"><?php esc_attr_e( 'Generated Options', 'wp-user-frontend' ); ?></span>
                        <button type="button" @click="select_all_ai_options" class="wpuf-text-sm wpuf-text-primary hover:wpuf-underline">
                            {{ all_ai_selected ? '<?php esc_attr_e( 'Deselect All', 'wp-user-frontend' ); ?>' : '<?php esc_attr_e( 'Select All', 'wp-user-frontend' ); ?>' }}
                        </button>
                    </div>
                    <div class="wpuf-ai-options-list">
                        <label v-for="(opt, idx) in ai_generated_options" :key="idx" class="wpuf-flex wpuf-items-center wpuf-py-1 wpuf-cursor-pointer hover:wpuf-bg-gray-50 wpuf-rounded wpuf-px-2">
                            <input
                                type="checkbox"
                                v-model="opt.selected"
                                :class="builder_class_names('checkbox')">
                            <span class="wpuf-text-sm wpuf-text-gray-700">{{ opt.label }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="wpuf-ai-modal-footer">
                <button type="button" @click="close_ai_modal" class="wpuf-btn wpuf-btn-secondary">
                    <?php esc_attr_e( 'Cancel', 'wp-user-frontend' ); ?>
                </button>
                <button v-if="!ai_generated_options.length" type="button" @click="generate_ai_options" :disabled="ai_loading || !ai_prompt" class="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed wpuf-border-0">
                    <svg v-if="!ai_loading" class="wpuf-w-5 wpuf-h-5 wpuf-pr-1" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <i v-if="ai_loading" class="fa fa-spinner fa-spin wpuf-mr-1"></i>
                    {{ ai_loading ? '<?php esc_attr_e( 'Generating...', 'wp-user-frontend' ); ?>' : '<?php esc_attr_e( 'Generate', 'wp-user-frontend' ); ?>' }}
                </button>
                <button v-else type="button" @click="import_ai_options" class="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center wpuf-border-0">
                    <?php esc_attr_e( 'Import Selected', 'wp-user-frontend' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- AI Provider Configuration Modal -->
    <div v-if="show_ai_config_modal" class="wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-black wpuf-bg-opacity-50 wpuf-z-[1000000] wpuf-flex wpuf-items-center wpuf-justify-center">
        <div class="wpuf-bg-white wpuf-rounded-md wpuf-p-8 wpuf-max-w-xl wpuf-w-full wpuf-mx-5 wpuf-relative">
            <!-- Key Icon -->
            <div class="wpuf-flex wpuf-justify-center wpuf-mb-8">
                <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="110" height="110" rx="55" fill="#D1FAE5"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M60 41C55.0294 41 51 45.0294 51 50C51 50.525 51.0451 51.0402 51.1317 51.5419C51.2213 52.0604 51.089 52.4967 50.8369 52.7489L42.1716 61.4142C41.4214 62.1644 41 63.1818 41 64.2426V68C41 68.5523 41.4477 69 42 69H47C47.5523 69 48 68.5523 48 68V66H50C50.5523 66 51 65.5523 51 65V63H53C53.2652 63 53.5196 62.8946 53.7071 62.7071L57.2511 59.1631C57.5033 58.911 57.9396 58.7787 58.4581 58.8683C58.9598 58.9549 59.475 59 60 59C64.9706 59 69 54.9706 69 50C69 45.0294 64.9706 41 60 41ZM60 45C59.4477 45 59 45.4477 59 46C59 46.5523 59.4477 47 60 47C61.6569 47 63 48.3431 63 50C63 50.5523 63.4477 51 64 51C64.5523 51 65 50.5523 65 50C65 47.2386 62.7614 45 60 45Z" fill="#065F46"/>
                </svg>
            </div>

            <!-- Title -->
            <h2 class="wpuf-text-2xl wpuf-font-medium wpuf-text-center wpuf-text-gray-900 wpuf-mb-4">
                <?php esc_attr_e( 'AI Provider Not Configured', 'wp-user-frontend' ); ?>
            </h2>

            <!-- Description -->
            <p class="wpuf-text-lg wpuf-text-center wpuf-text-gray-400 wpuf-mb-16">
                <?php esc_attr_e( 'To use AI Form Generation, please connect an AI provider by adding your API key in the settings', 'wp-user-frontend' ); ?>
            </p>

            <!-- Buttons -->
            <div class="wpuf-flex wpuf-justify-center wpuf-gap-3">
                <button
                    type="button"
                    @click="close_ai_config_modal"
                    class="wpuf-px-6 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-text-lg wpuf-transition-colors wpuf-min-w-[101px]">
                    <?php esc_attr_e( 'Cancel', 'wp-user-frontend' ); ?>
                </button>
                <button
                    type="button"
                    @click="go_to_ai_settings"
                    class="wpuf-px-6 wpuf-py-3 wpuf-bg-emerald-700 hover:wpuf-bg-emerald-800 wpuf-text-white wpuf-rounded-md wpuf-text-lg wpuf-transition-colors wpuf-min-w-[158px]">
                    <?php esc_attr_e( 'Go to Settings', 'wp-user-frontend' ); ?>
                </button>
            </div>
        </div>
    </div>
    <?php do_action( 'wpuf_field_option_data_after' ); ?>
</div>
