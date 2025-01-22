<?php
global $post;
$form_settings = wpuf_get_form_settings( $post->ID );
$post_type_selected = ! empty( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';
?>
<div class="wpuf-settings-container wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-m-4 wpuf-flex wpuf-transition-transform wpuf-duration-200 wpuf-ease-in-out">
    <div class="wpuf-w-1/4 wpuf-min-h-screen wpuf-border-r wpuf-p-8">
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
    <div class="wpuf-w-3/4 wpuf-min-h-screen wpuf-p-8">
        <div class="wpuf-pb-6 wpuf-border-b wpuf-border-gray-200">
            <h2 class="wpuf-text-2xl wpuf-mb-2 wpuf-mt-0">{{ active_settings_title }}</h2>
        </div>
        <?php
        foreach ( $settings_items as $settings_key => $settings_item ) {
            if ( $settings_item['section'] ) {
                foreach ( $settings_item['section'] as $section_key => $section ) {
                    ?>
                    <div class="wpuf-py-4 wpuf-border-b wpuf-border-gray-300">
                        <p class="wpuf-text-lg wpuf-font-medium wpuf-mb-2">
                            <?php echo $section['label']; ?>
                        </p>
                        <p class="wpuf-text-gray-500 wpuf-text-xs"><?php echo $section['desc']; ?></p>
                    <?php
                    foreach ( $section['fields'] as $field_key => $field ) {
                        $value = ! empty( $field['default'] ) ? $field['default'] : '';
                        $value = isset( $field['value'] ) ? $field['value'] : $value; // checking with isset because saved value can be empty string

                        if ( 'inline_fields' !== $field_key ) {
                            ?>
                        <div class="wpuf-my-4 wpuf-input-container">
                            <div class="wpuf-flex wpuf-items-center wpuf-w-2/5<?php echo 'color-picker' === $field['type'] ? ' wpuf-justify-between' : '' ?>">
                                <?php if ( 'checkbox' === $field['type'] ) { ?>
                                    <input
                                        :class="[setting_class_names('checkbox'), '!wpuf-mr-2']"
                                        type="checkbox"
                                        name="wpuf_settings[<?php echo $field_key; ?>]"
                                        id="<?php echo $field_key; ?>"/>
                                <?php } ?>
                                <?php
                                if ( $field['type'] === 'color-picker' ) {
                                    echo '<div class="wpuf-flex wpuf-items-center">';
                                }
                                ?>
                                <label for="<?php echo $field_key; ?>" class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                                    <?php echo $field['label']; ?>
                                </label>
                                <?php if ( ! empty( $field['help_text'] ) ) { ?>
                                    <help-text text="<?php echo $field['help_text']; ?>"></help-text>
                                <?php } ?>
                                <?php
                                if ( $field['type'] === 'color-picker' ) {
                                    echo '</div>';
                                }
                                ?>
                                <?php if ( ! empty( $field['link'] ) ) { ?>
                                    <a href="<?php echo $field['link']; ?>" target="_blank" title="<?php esc_attr_e( 'Learn More', 'wp-user-frontend' ); ?>" class="focus:wpuf-shadow-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="wpuf-size-5 wpuf-ml-1 wpuf-stroke-gray-50 hover:wpuf-stroke-gray-200">
                                            <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                                            <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                                        </svg>
                                    </a>
                                <?php } ?>
                                <?php if ( $field['type'] === 'toggle' ) { ?>
                                    <label
                                        for="<?php echo $field_key; ?>"
                                        class="wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-cursor-pointer wpuf-ml-2">
                                        <input
                                            type="checkbox"
                                            id="<?php echo $field_key; ?>"
                                            name="wpuf_settings[<?php echo $field_key; ?>]"
                                            class="wpuf-sr-only wpuf-peer">
                                        <div class="wpuf-flex wpuf-items-center wpuf-w-12 wpuf-h-6 wpuf-bg-gray-300 wpuf-rounded-full wpuf-peer peer-checked:after:wpuf-translate-x-full rtl:wpuf-peer-checked:after:wpuf--translate-x-full peer-checked:after:wpuf-border-white after:content-[''] after:wpuf-absolute after:top-[4px] after:wpuf-bg-white after:wpuf-border-gray-300 after:wpuf-border after:wpuf-rounded-full after:wpuf-h-5 after:wpuf-w-5 after:wpuf-left-[3px] after:wpuf-transition-all peer-checked:wpuf-bg-primary"></div>
                                    </label>
                                <?php } ?>
                                <?php if ( $field['type'] === 'color-picker' ) { ?>
                                    <div class="wpuf-relative wpuf-ml-2 wpuf-flex wpuf-gap-2.5">
                                    <div
                                        @click="$event.target.querySelector('input').click()"
                                        class="wpuf-flex wpuf-justify-center wpuf-items-center wpuf-space-x-1 wpuf-px-2 wpuf-py-1.5 wpuf-rounded-md wpuf-bg-white wpuf-border wpuf-cursor-pointer wpuf-relative">
                                        <div class="wpuf-w-6 wpuf-h-6 wpuf-overflow-hidden wpuf-border wpuf-border-gray-200 wpuf-rounded-full wpuf-flex wpuf-justify-center wpuf-items-center">
                                            <input
                                                type="color"
                                                class="wpuf-w-8 wpuf-h-12 !wpuf-border-gray-50 !wpuf--m-4 hover:!wpuf-cursor-pointer"
                                                style="background: <?php echo $field['default']; ?>"
                                                value="<?php echo ! empty( $field['default'] ) ? $field['default'] : ''; ?>">
                                        </div>
                                        <i class="fa fa-angle-down !wpuf-font-bold !wpuf-text-xl !wpuf-leading-none wpuf-text-gray-600 wpuf-ml-2"></i>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <?php if ( 'select' === $field['type'] ) { ?>
                                <select
                                    id="<?php echo $field_key; ?>"
                                    :class="setting_class_names('dropdown')">
                                    <?php foreach ( $field['options'] as $index => $option ) { ?>
                                        <option value="<?php echo $index; ?>"><?php echo $option; ?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                            <?php if ( 'multi-select' === $field['type'] ) { ?>
                                <select
                                    id="<?php echo $field_key; ?>"
                                    :class="['tax-list-selector', setting_class_names('dropdown')]"
                                    multiple
                                >
                                    <?php foreach ( $field['options'] as $index => $option ) { ?>
                                        <option value="<?php echo $index; ?>"><?php echo $option; ?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                            <?php
                            if ( 'text' === $field['type'] ) {
                                ?>
                                <input
                                    :class="setting_class_names('text')"
                                    type="<?php echo $field['type']; ?>"
                                    name="wpuf_settings[<?php echo $field_key; ?>]"
                                    id="<?php echo $field_key; ?>"
                                    value="<?php echo $value; ?>"/>
                            <?php } ?>
                            <?php
                            if ( $field['type'] === 'textarea' ) {

                                ?>
                                <textarea
                                    :class="setting_class_names('textarea')"
                                    name="wpuf_settings[<?php echo $field_key; ?>]"
                                    id="<?php echo $field_key; ?>"><?php echo $value; ?></textarea>
                            <?php } ?>

                            <?php
                            if ( 'pic-radio' === $field['type'] ) {
                                ?>
                            <div class="wpuf-flex">
                                <?php
                                foreach ( $field['options'] as $key => $option ) {
                                    ?>
                                    <div class="wpuf-relative wpuf-text-center">
                                        <label>
                                            <input
                                                type="radio"
                                                name="wpuf_settings[<?php echo $field_key; ?>]"
                                                value="<?php echo $key; ?>"
                                                class="wpuf-absolute wpuf-opacity-0 wpuf-peer">
                                            <img
                                                class="wpuf-absolute wpuf-opacity-0 peer-checked:wpuf-opacity-100 wpuf-top-[5%] wpuf-right-[10%] wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out"
                                                src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/checked-green.svg' ); ?>" alt="">
                                            <img
                                                src="<?php echo $option['image']; ?>"
                                                alt="<?php echo $key; ?>"
                                                class="hover:wpuf-cursor-pointer wpuf-border-transparent wpuf-border-2 wpuf-border-solid wpuf-rounded-lg hover:wpuf-border-primary peer-checked:wpuf-border-primary wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-mb-2">
                                        </label>
                                        <label
                                            for="<?php echo $field_key; ?>"
                                            class="wpuf-mr-2 wpuf-text-sm wpuf-text-gray-700">
                                            <?php echo $option['label']; ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                                <?php
                            }
                            ?>

                            <?php
                            if ( 'trailing-text' === $field['type'] ) {
                                ?>
                                <div class="wpuf-mt-2 wpuf-relative">
                                    <input
                                        :class="setting_class_names('<?php echo $field['trailing_type']; ?>')"
                                        type="<?php echo $field['trailing_type']; ?>"
                                        name="<?php echo $field_key; ?>"
                                        id="<?php echo $field_key; ?>"
                                        value="<?php echo $value; ?>"/>
                                    <span
                                        :class="<?php echo $field['trailing_type'] === 'number'; ?> ? 'wpuf-p-1' : 'wpuf-p-2'"
                                        class="wpuf-absolute wpuf-top-0 wpuf--right-px wpuf-h-full wpuf-bg-gray-50 wpuf-rounded-r-sm wpuf-text-gray-700 wpuf-border wpuf-border-gray-300 wpuf-p-1">
                                        <?php echo $field['trailing_text']; ?>
                                    </span>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                            <?php
                        } else {
                            ?>
                            <div class="wpuf-flex wpuf-input-container">
                                <?php
                                $index_counter = 0;
                                foreach ( $field['fields'] as $inner_field_key => $inner_field ) {
                                    $classes = 'wpuf-w-1/2';
                                    $classes .= $index_counter === 0 ? ' wpuf-mr-2' : '';

                                    ++$index_counter;
                                    ?>
                                <div
                                    class="<?php echo $classes; ?>">
                                    <label
                                        for="<?php echo $field_key; ?>"
                                        class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                                        <?php echo $inner_field['label']; ?>
                                    </label>
                                    <?php
                                    if ( 'text' === $inner_field['type'] ) {
                                        ?>
                                        <input
                                            :class="setting_class_names('text')"
                                            type="<?php echo $inner_field['type']; ?>"
                                            name="wpuf_settings[<?php echo $inner_field_key; ?>]"
                                            id="<?php echo $inner_field_key; ?>"
                                            value="<?php echo $value; ?>"/>
                                        <?php
                                    }
                                    ?>
                                </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    </div>
                    <?php
                }
            }
            ?>
            <?php
        }
        ?>
        <div class="wpuf-flex wpuf-space-x-4 wpuf-items-center wpuf-mt-8">
            <button
                @click.prevent=""
                class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300"><?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>
            </button>
            <button
                v-if="!is_form_saving"
                @click="save_form_builder"
                type="button"
                :disabled="is_form_saving"
                :class="is_form_saving ? 'wpuf-cursor-wait' : 'wpuf-cursor-pointer'"
                class="wpuf-btn-primary wpuf-w-full"><?php esc_html_e( 'Save Form', 'wp-user-frontend' ); ?></button>
            <button v-else type="button" class="button button-primary button-ajax-working" disabled>
                <span class="loader"></span> <?php esc_html_e( 'Saving Form Data', 'wp-user-frontend' ); ?>
            </button>
        </div>
    </div>
</div>
