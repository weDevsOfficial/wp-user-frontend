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
        <div
            v-for="(section, index) in settings_items[active_settings_tab].section"
            class="wpuf-py-4">
            <p class="wpuf-text-lg wpuf-font-medium wpuf-mb-2">
                {{ section.label }}
            </p>
            <p>{{ section.desc }}</p>
            <div
                v-for="(field, index) in section.fields"
                class="wpuf-mb-4">
                <label :for="index" class="wpuf-block wpuf-text-sm wpuf-font-semibold wpuf-mb-2">
                    {{ field.label }}
                </label>
                <select
                    v-if="field.type === 'select'"
                    :class="setting_class_names('dropdown')">
                    <option
                        v-for="(option, index) in field.options"
                        :value="index">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</div>
