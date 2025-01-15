<div class="wpuf-settings-container wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-m-4 wpuf-flex wpuf-transition-transform wpuf-duration-200 wpuf-ease-in-out">
    <div class="wpuf-w-1/4 wpuf-min-h-screen wpuf-max-h-screen wpuf-border-r wpuf-p-8">
        <?php
        $settings_titles = wpuf_get_post_form_builder_setting_menu_titles();
        $settings_items = wpuf_get_post_form_builder_setting_menu_contents();
        foreach ( $settings_titles as $key => $top_settings ) {
            $icon  = ! empty( $top_settings['icon'] ) ? $top_settings['icon'] : '';
            $label = ! empty( $top_settings['label'] ) ? $top_settings['label'] : '';
            ?>
            <div class="wpuf-mb-4 wpuf-flex wpuf-justify-between wpuf-items-center">
                <h2 class="wpuf-text-base wpuf-text-gray-600 wpuf-mb-2 wpuf-flex wpuf-items-center">
                    <?php
                    echo $icon;
                    ?>
                    <span class="wpuf-ml-2">
                    <?php
                    echo $label;
                    ?>
                    </span>
                </h2>
                <i
                    class="fa fa-angle-down !wpuf-font-bold !wpuf-text-xl !wpuf-leading-none wpuf-text-gray-600"
                ></i>
            </div>
            <div class="wpuf-mb-4">
                <ul class="wpuf-sidebar-menu wpuf-list-none wpuf-space-y-2">
                    <?php
                    $sub_menus = ! empty( $top_settings['sub_items'] ) ? $top_settings['sub_items'] : [];
                    foreach ( $sub_menus as $sub_key => $sub_menu ) {
                        $sub_icon  = ! empty( $sub_menu['icon'] ) ? $sub_menu['icon'] : '';
                        $sub_label = ! empty( $sub_menu['label'] ) ? $sub_menu['label'] : '';
                        ?>
                    <li
                        @click="switch_settings_menu('<?php echo $sub_key; ?>')"
                        :class="active_settings_tab === '<?php echo $sub_key; ?>' ? 'wpuf-bg-primary active_settings_tab' : ''"
                        class="wpuf-group/sidebar-item wpuf-mx-2 wpuf-py-2 wpuf-px-3 wpuf-flex hover:wpuf-bg-primary hover:wpuf-cursor-pointer wpuf-rounded-lg wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-flex wpuf-items-center wpuf-focus-visible:">
                        <a
                            :class="active_settings_tab === '<?php echo $sub_key; ?>' ? 'wpuf-text-white' : 'wpuf-text-gray-600'"
                            class="wpuf-ml-2 wpuf-text-sm group-hover/sidebar-item:wpuf-text-white wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out focus:wpuf-shadow-none focus:wpuf-outline-none wpuf-flex">
                            <?php
                            echo $sub_icon;
                            ?>
                            <span class="wpuf-ml-2">
                            <?php
                            echo $sub_label;
                            ?>
                            </span>
                        </a>
                    </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="wpuf-w-3/4 wpuf-min-h-screen wpuf-max-h-screen wpuf-p-8">
        <div class="wpuf-pb-6 wpuf-border-b">
            <h2 class="wpuf-text-2xl wpuf-mb-2 wpuf-mt-0">
                {{ active_settings_title }}
            </h2>
        </div>
        <template v-if="section_exists">
        <div
            v-for="(section, index) in settings_items[active_settings_tab].section"
            class="wpuf-py-4">
            <p class="wpuf-text-lg wpuf-font-medium wpuf-mb-2">
                {{ section.label }}
            </p>
            <p class="wpuf-text-gray-500 wpuf-text-xs">{{ section.desc }}</p>
            <div
                v-for="(field, index) in section.fields"
                class="wpuf-my-4">
                <div class="wpuf-flex">
                    <label :for="index" class="wpuf-text-sm wpuf-text-gray-700 wpuf-mb-2">
                        {{ field.label }}
                    </label>
                    <help-text v-if="field.help_text" :text="field.help_text"></help-text>
                    <a v-if="field.link" :href="field.link" target="_blank" title="<?php esc_attr_e( 'Learn More', 'wp-user-frontend' ); ?>" class="focus:wpuf-shadow-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="wpuf-size-5 wpuf-ml-1 wpuf-stroke-gray-50 hover:wpuf-stroke-gray-200">
                            <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                            <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                        </svg>
                    </a>
                </div>
                <select
                    v-if="field.type === 'select'"
                    :class="setting_class_names('dropdown')">
                    <option
                        v-for="(option, index) in field.options"
                        :value="index">
                        {{ option }}
                    </option>
                </select>
                <select
                    v-if="field.type === 'multi-select'"
                    :class="['tax-list-selector']"
                    multiple
                >
                    <option
                        v-for="(option, index) in field.options"
                        :value="index">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
        </template>
    </div>
</div>
