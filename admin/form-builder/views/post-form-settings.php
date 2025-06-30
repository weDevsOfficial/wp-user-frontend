<div class="wpuf-settings-container wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-m-4 wpuf-flex wpuf-transition-transform wpuf-duration-200 wpuf-ease-in-out">
    <div class="wpuf-w-1/4 wpuf-min-h-screen wpuf-border-r wpuf-p-8">
        <?php

        use WeDevs\Wpuf\Free\Pro_Prompt;

        $settings_titles = wpuf_get_post_form_builder_setting_menu_titles();
        $settings_items  = wpuf_get_post_form_builder_setting_menu_contents();
        $badge_menus     = [
            'post_expiration',
        ];

        foreach ( $settings_titles as $key => $top_settings ) {
            $icon  = ! empty( $top_settings['icon'] ) ? $top_settings['icon'] : '';
            $label = ! empty( $top_settings['label'] ) ? $top_settings['label'] : '';
            $class = '';
            ?>

            <?php
            if ( ( 'modules' === $key ) && empty( $settings_items['modules'] ) ) {
                ?>
            <div class="wpuf-mb-4 wpuf-flex wpuf-justify-between wpuf-items-center">
                <h2
                    id="modules-menu"
                    @click="switch_settings_menu('modules', 'modules')"
                    :class="active_settings_tab === 'modules'? 'wpuf-bg-primary active_settings_tab wpuf-m-0 wpuf-text-white' : ''"
                    class="wpuf-group/sidebar-item hover:wpuf-bg-primary hover:wpuf-cursor-pointer hover:wpuf-text-white wpuf-rounded-lg wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-items-center wpuf-w-full wpuf-m-0 wpuf-py-2 wpuf-px-3 wpuf--ml-3 wpuf-flex wpuf-text-gray-600">
                    <?php
                    echo $icon;
                    ?>
                    <span class="wpuf-ml-2">
                        <?php
                        echo $label;
                        ?>
                    </span>
                </h2>
            </div>
            <?php } else { ?>
                <div class="wpuf-mb-4 wpuf-flex wpuf-justify-between wpuf-items-center">
                    <h2 class="wpuf-text-base wpuf-text-gray-600 wpuf-m-0 wpuf-flex wpuf-items-center">
                        <?php
                        echo $icon;
                        ?>
                        <span class="wpuf-ml-2">
                <?php
                echo $label;
                ?>
                </span>
                    </h2>
                </div>
                <?php
            }
            ?>

            <div class="wpuf-mb-4">
                <ul class="wpuf-sidebar-menu wpuf-list-none wpuf-space-y-2">
                        <?php
                        $sub_menus = ! empty( $top_settings['sub_items'] ) ? $top_settings['sub_items'] : [];
                        foreach ( $sub_menus as $sub_key => $sub_menu ) {
                            $sub_icon  = ! empty( $sub_menu['icon'] ) ? $sub_menu['icon'] : '';
                            $sub_label = ! empty( $sub_menu['label'] ) ? $sub_menu['label'] : '';
                            ?>
                            <li
                                @click="switch_settings_menu('<?php echo $key; ?>', '<?php echo $sub_key; ?>')"
                                :class="active_settings_tab === '<?php echo $sub_key; ?>' ? 'wpuf-bg-primary active_settings_tab' : ''"
                                class="wpuf-group/sidebar-item wpuf-mx-2 wpuf-py-2 wpuf-px-3 hover:wpuf-bg-primary hover:wpuf-cursor-pointer wpuf-rounded-lg wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-items-center wpuf-flex wpuf-justify-between"
                                data-settings="<?php echo $sub_key; ?>">
                                <a
                                    :class="active_settings_tab === '<?php echo $sub_key; ?>' ? 'wpuf-text-white' : 'wpuf-text-gray-600'"
                                    class="wpuf-ml-2 wpuf-text-sm group-hover/sidebar-item:wpuf-text-white wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out focus:wpuf-shadow-none focus:wpuf-outline-none wpuf-flex wpuf-items-center">
                                    <?php
                                    echo $sub_icon;
                                    ?>
                                    <span class="wpuf-ml-2">
                                        <?php
                                        echo $sub_label;
                                        ?>
                                    </span>
                                </a>
                                <?php
                                if ( in_array( $sub_key, $badge_menus, true ) && ! wpuf_is_pro_active() ) {
                                    ?>
                                    <span><img src="<?php echo wpuf_get_pro_icon() ?>" alt="pro icon"></span>
                                    <?php
                                }
                                ?>
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
        <div class="wpuf-pb-8">
            <h2 class="wpuf-text-2xl wpuf-m-0 wpuf-leading-none">{{ active_settings_title }}</h2>
        </div>
        <?php
        global $post;

        $form_settings  = wpuf_get_form_settings( $post->ID );
        $form_post_type = ! empty( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';
?>
        <div class="wpuf-border-y wpuf-border-gray-200 wpuf-py-8">
        <?php
        foreach ( $settings_items as $section_key => $section ) {
            foreach ( $section as $settings_key => $settings_item ) {
                if ( ! empty( $settings_item['section'] ) ) {
                    $section_counter = 1;

                    foreach ( $settings_item['section'] as $section_key => $section ) {
                        $class_list = 'wpuf-settings-body wpuf-pb-8';
                        if ( 1 < $section_counter ) {
                            $class_list .= ' wpuf-pt-6 wpuf-border-t wpuf-border-gray-200';
                        }

                        // if it is the last item of the section
                        if ( ( $section_counter === count( $settings_item['section'] ) ) && 1 < count(
                                $settings_item['section']
                            ) ) {
                            $class_list = 'wpuf-settings-body wpuf-pt-6 wpuf-border-t wpuf-border-gray-200';
                        }
                        ?>
                    <div
                        class="<?php echo $class_list; ?>"
                        data-settings-body="<?php echo $settings_key; ?>"
                    >
                        <p class="wpuf-text-lg wpuf-font-medium wpuf-mb-3 wpuf-mt-0 wpuf-leading-none"><?php echo $section['label']; ?></p>
                        <p class="wpuf-text-gray-500 wpuf-text-[13px] wpuf-leading-5 !wpuf-mb-4 !wpuf-mt-0"><?php echo $section['desc']; ?></p>
                        <?php
                        if ( ! empty( $section['fields'] ) ) {
                            foreach ( $section['fields'] as $field_key => $field ) {
                                wpuf_render_settings_field( $field_key, $field, $form_settings, $form_post_type );
                            }
                        }

                        if ( ! empty( $section['pro_preview'] ) ) {
                            if ( ! empty( $section['pro_preview']['fields'] ) ) {
                                ?>
                                <div class="wpuf-p-4 wpuf-relative wpuf-rounded wpuf-border wpuf-border-transparent hover:wpuf-border-sky-500 wpuf-border-dashed wpuf-group/pro-item wpuf-transition-all wpuf-opacity-50 hover:wpuf-opacity-100">
                                    <a
                                        class="wpuf-btn-primary wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-30 wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-100 wpuf-transition-all"
                                        target="_blank"
                                        href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>">
                                        <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                                    </a>
                                    <div class="wpuf-z-20 wpuf-absolute wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full wpuf-shadow-sm wpuf-bg-emerald-50 group-hover/pro-item:wpuf-opacity-50 wpuf-opacity-0"></div>
                                    <?php
                                    foreach ( $section['pro_preview']['fields'] as $field_key => $field ) {
                                        wpuf_render_settings_field( $field_key, $field, $form_settings, $form_post_type );
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                        <?php
                        $section_counter++;
                    }
                } else {
                    ?>
                <div
                    class="wpuf-settings-body wpuf--mt-6"
                    data-settings-body="<?php echo $settings_key; ?>"
                >
                    <?php
                    foreach ( $settings_item as $field_key => $field ) {
                        if ( 'pro_preview' === $field_key ) {
                            continue;
                        }

                        wpuf_render_settings_field( $field_key, $field, $form_settings, $form_post_type );
                    }

                    if ( ! empty( $settings_item['pro_preview'] ) ) {
                        ?>
                        <div class="wpuf-p-4 wpuf-relative wpuf-rounded wpuf-border wpuf-border-transparent hover:wpuf-border-sky-500 wpuf-border-dashed wpuf-group/pro-item wpuf-transition-all wpuf-opacity-50 hover:wpuf-opacity-100">
                            <a
                                class="wpuf-btn-primary wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-30 wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-100 wpuf-transition-all"
                                target="_blank"
                                href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>">
                                <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                            </a>
                            <div class="wpuf-z-20 wpuf-absolute wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full wpuf-shadow-sm wpuf-bg-emerald-50 group-hover/pro-item:wpuf-opacity-50 wpuf-opacity-0"></div>
                            <?php
                            foreach ( $settings_item['pro_preview']['fields'] as $field_key => $field ) {
                                wpuf_render_settings_field( $field_key, $field, $form_settings, $form_post_type );
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
        }
        ?>
        </div>
            <?php

        if ( ! wpuf_is_pro_active() || empty( $settings_items['modules'] ) ) {
            ?>
        <div
            v-if="active_settings_tab === 'modules'"
            class="wpuf-py-4 wpuf-border-b wpuf-border-gray-300 wpuf-flex wpuf-items-center wpuf-justify-evenly wpuf-flex-col wpuf-h-[70vh] wpuf-p-4 wpuf-relative wpuf-rounded wpuf-border wpuf-border-transparent hover:wpuf-border-sky-500 wpuf-border-dashed wpuf-group/pro-item wpuf-transition-all wpuf-opacity-50 hover:wpuf-opacity-100">
            <?php
            if ( ! wpuf_is_pro_active() ) {
                ?>
            <a
                class="wpuf-btn-primary wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-30 wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-100 wpuf-transition-all"
                target="_blank"
                href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>">
                <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
            </a>
            <div class="wpuf-z-20 wpuf-absolute wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full wpuf-shadow-sm wpuf-bg-emerald-50 group-hover/pro-item:wpuf-opacity-50 wpuf-opacity-0"></div>
                <?php
            }
            ?>
            <div class="wpuf-flex wpuf-flex-col wpuf-items-center">
                <svg width="161" height="161" viewBox="0 0 161 161" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="80.5" cy="80.5" r="80.5" fill="#F3F3F4"/>
                    <path d="M148.624 58.0946V148.936C148.624 155.477 143.323 160.777 136.783 160.777H26.075C19.5347 160.777 14.2344 155.477 14.2344 148.936V58.0946C14.2344 51.5543 19.5347 46.2539 26.075 46.2539H136.783C143.323 46.2403 148.624 51.5407 148.624 58.0946Z" fill="#DBEEE6"/>
                    <path d="M27.082 156.987C22.0721 156.987 18 152.932 18 147.944V59.0423C18 54.0543 22.0721 50 27.082 50H135.918C140.928 50 145 54.0543 145 59.0423V147.958C145 152.946 140.928 157 135.918 157H27.082V156.987Z" fill="white"/>
                    <path d="M74.8736 114H31V116H74.8736V114Z" fill="#059669"/>
                    <rect x="31" y="77" width="26" height="26" rx="5" fill="#DBEEE6"/>
                    <path d="M98 121.188H31V123.188H98V121.188Z" fill="#DBEEE6"/>
                    <path d="M87.9881 128.336H31V130.336H87.9881V128.336Z" fill="#DBEEE6"/>
                    <rect x="116" y="118.945" width="17" height="7.55556" rx="3.77778" fill="#059669"/>
                    <g filter="url(#filter0_dd_2801_83558)">
                        <circle cx="128.277" cy="122.722" r="4.72222" fill="white"/>
                        <circle cx="128.277" cy="122.722" r="4.48611" stroke="#E2E8F0" stroke-width="0.472222"/>
                    </g>
                    <path d="M136.78 160.5H26.0859C19.6991 160.5 14.5 155.301 14.5 148.914V58.0859C14.5 51.6991 19.6991 46.5 26.0859 46.5H136.78C143.167 46.5 148.366 51.6991 148.366 58.0859V148.928C148.366 155.301 143.167 160.5 136.78 160.5Z" stroke="#059669"/>
                    <path d="M30.7616 57.6777C30.7616 59.2719 29.4672 60.5663 27.873 60.5663C26.2788 60.5663 24.9844 59.2719 24.9844 57.6777C24.9844 56.0835 26.2788 54.7891 27.873 54.7891C29.4672 54.7891 30.7616 56.0835 30.7616 57.6777Z" fill="#DBEEE6"/>
                    <path d="M38.1054 57.6777C38.1054 59.2719 36.8109 60.5663 35.2168 60.5663C33.6226 60.5663 32.3281 59.2719 32.3281 57.6777C32.3281 56.0835 33.6226 54.7891 35.2168 54.7891C36.8109 54.7891 38.1054 56.0835 38.1054 57.6777Z" fill="#DBEEE6"/>
                    <path d="M45.4491 57.6777C45.4491 59.2719 44.1547 60.5663 42.5605 60.5663C40.9663 60.5663 39.6719 59.2719 39.6719 57.6777C39.6719 56.0835 40.9663 54.7891 42.5605 54.7891C44.1547 54.7891 45.4491 56.0835 45.4491 57.6777Z" fill="#DBEEE6"/>
                    <defs>
                        <filter id="filter0_dd_2801_83558" x="120.555" y="116" width="15.4453" height="15.4453" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                            <feOffset dy="1"/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.06 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2801_83558"/>
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                            <feOffset dy="1"/>
                            <feGaussianBlur stdDeviation="1.5"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/>
                            <feBlend mode="normal" in2="effect1_dropShadow_2801_83558" result="effect2_dropShadow_2801_83558"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_2801_83558" result="shape"/>
                        </filter>
                    </defs>
                </svg>
                <p class="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-9 wpuf-mb-0">No modules have been activated yet.</p>
                <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-mt-2">No modules have been activated yet.</p>

                <?php
                if ( wpuf_is_pro_active() ) {
                    ?>
                    <a
                        class="wpuf-btn-primary wpuf-mt-4"
                        target="_blank"
                        href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-modules' ) ); ?>">
                        <?php esc_html_e( 'Go To Module Page', 'wp-user-frontend' ); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
            <?php
        }
        ?>
        <div class="wpuf-flex wpuf-space-x-4 wpuf-items-center wpuf-mt-8">
            <a
                href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-post-forms' ) ); ?>"
                class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-8 wpuf-py-3 wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-cursor-pointer"><?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>
            </a>
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

<?php
function wpuf_render_settings_field( $field_key, $field, $form_settings, $post_type = 'post' ) {
    $pro_badge    = WPUF_ASSET_URI . '/images/pro-badge.svg';
    $badge_fields = [
        'enable_multistep',
        'notification_edit',
    ];
    $name         = ! empty( $field['name'] ) ? $field['name'] : 'wpuf_settings[' . $field_key . ']';

    if ( ( 'default_category' === $field_key ) && ( 'post' !== $post_type ) ) {
        $field_key = 'default_' . $post_type . '_cat';
    }

    $value = ! empty( $field['default'] ) ? $field['default'] : '';
    $value = ! empty( $field['value'] ) ? $field['value'] : $value;                           // default value

    // replace default value if already saved in DB
    if ( ! empty( $field['name'] ) ) {
        preg_match('/wpuf_settings\[(.*?)\]\[(.*?)\]/', $field['name'], $matches);

        if (isset($matches[1]) && isset($matches[2])) {
            $dynamic_key = $matches[1];
            $temp_key    = $matches[2];
            $value       = isset( $form_settings[ $dynamic_key ][ $temp_key ] ) ? $form_settings[ $dynamic_key ][ $temp_key ] : $value;
        }
    } else {
        $value = isset( $form_settings[ $field_key ] ) ? $form_settings[ $field_key ] : $value;   // checking with isset because saved value can be empty string
    }

    // if the field is a pro fields preview, no need to load fields from db
    if ( empty( $field['pro_preview'] ) ) {
        $value = isset( $form_settings[ $field_key ] ) ? $form_settings[ $field_key ] : $value;   // checking with isset because saved value can be empty string
    }

    do_action( 'wpuf_before_post_form_settings_field', $field, $value );
    do_action( 'wpuf_before_post_form_settings_field_' . $field_key, $field, $value );

    if ( 'inline_fields' !== $field_key ) {
        ?>
        <div class="wpuf-mt-6 wpuf-input-container">
            <div class="wpuf-flex wpuf-items-center <?php echo ( 'color-picker' === $field['type'] || 'toggle' === $field['type'] ) ? 'wpuf-justify-between wpuf-w-2/5' : ''; ?>">
                <?php if ( 'checkbox' === $field['type'] ) { ?>
                    <input
                        :class="[setting_class_names('checkbox'), '!wpuf-mr-2']"
                        type="checkbox"
                        name="<?php echo $name; ?>"
                        <?php echo esc_attr( checked( $value, 'on', false ) ); ?>
                        id="<?php echo $field_key; ?>"/>
                <?php } ?>
                <?php
                if ( 'color-picker' === $field['type'] || 'toggle' === $field['type'] ) {
                    echo '<div class="wpuf-flex wpuf-items-center">';
                }
                ?>
                <label for="<?php echo $field_key; ?>" class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                    <?php echo $field['label']; ?>
                </label>
                <?php if ( ! empty( $field['help_text'] ) ) { ?>
                    <help-text text="<?php echo $field['help_text']; ?>"></help-text>
                <?php } ?>
                <?php if ( ! empty( $field['link'] ) ) { ?>
                    <a href="<?php echo $field['link']; ?>" target="_blank" title="<?php esc_attr_e( 'Learn More', 'wp-user-frontend' ); ?>" class="focus:wpuf-shadow-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="wpuf-size-5 wpuf-ml-1 wpuf-stroke-gray-50 hover:wpuf-stroke-gray-200">
                            <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                            <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                        </svg>
                    </a>
                <?php } ?>
                <?php if ( in_array( $field_key, $badge_fields, true ) && ! wpuf_is_pro_active() ) { ?>
                    <img class="wpuf-ml-2" src="<?php echo esc_attr( $pro_badge ); ?>" alt="">
                <?php } ?>
                <?php
                if ( 'color-picker' === $field['type'] || 'toggle' === $field['type'] ) {
                    echo '</div>';
                }
                ?>
                <?php if ( 'toggle' === $field['type'] ) { ?>
                    <label
                        for="<?php echo $field_key; ?>"
                        class="wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-cursor-pointer wpuf-ml-2">
                        <input
                            type="checkbox"
                            id="<?php echo $field_key; ?>"
                            name="<?php echo $name; ?>"
                            <?php echo esc_attr( checked( $value, 'on', false ) ); ?>
                            class="wpuf-sr-only wpuf-peer">
                        <span class="wpuf-flex wpuf-items-center wpuf-w-10 wpuf-h-4 wpuf-bg-gray-300 wpuf-rounded-full wpuf-peer peer-checked:wpuf-bg-primary after:wpuf-w-6 after:wpuf-h-6 after:wpuf-bg-white after:wpuf-rounded-full after:wpuf-shadow-md after:wpuf-duration-300 peer-checked:after:wpuf-translate-x-4 after:wpuf-border after:wpuf-border-solid after:wpuf-border-gray-50"></span>
                    </label>
                <?php } ?>
                <?php if ( 'color-picker' === $field['type'] ) { ?>
                    <div class="wpuf-relative wpuf-ml-2 wpuf-flex wpuf-gap-2.5">
                        <div
                            @click="$event.target.querySelector('input').click()"
                            class="wpuf-flex wpuf-justify-center wpuf-items-center wpuf-space-x-1 wpuf-px-2 wpuf-py-1.5 wpuf-rounded-md wpuf-bg-white wpuf-border wpuf-cursor-pointer wpuf-relative">
                            <div class="wpuf-w-6 wpuf-h-6 wpuf-overflow-hidden wpuf-border wpuf-border-gray-200 wpuf-rounded-full wpuf-flex wpuf-justify-center wpuf-items-center">
                                <input
                                    type="color"
                                    class="wpuf-w-8 wpuf-h-12 !wpuf-border-gray-50 !wpuf--m-4 hover:!wpuf-cursor-pointer"
                                    name="<?php echo $name; ?>"
                                    id="<?php echo $field_key; ?>"
                                    style="background: <?php echo $field['default']; ?>"
                                    value="<?php echo $value; ?>">
                            </div>
                            <i
                                @click="$event.target.closest('div').querySelector('input').click()"
                                class="fa fa-angle-down !wpuf-font-bold !wpuf-text-xl !wpuf-leading-none wpuf-text-gray-600 wpuf-ml-2"></i>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php
            if ( 'select' === $field['type'] ) {
                $value_str = is_array( $value ) ? implode( ',', $value ) : $value;
                ?>
                <select
                    id="<?php echo $field_key; ?>"
                    name="<?php echo $name; ?>"
                    data-value="<?php echo $value_str; ?>"
                    :class="setting_class_names('dropdown')">
                    <?php
                    foreach ( $field['options'] as $index => $option ) {
                        printf( '<option data-select-value="%s" data-select-index="%s" value="%s"%s>%s</option>', $value, $index, esc_attr( $index ), esc_attr( selected( $value, $index, false ) ), esc_html( $option ) );
                    }
                    ?>
                </select>
            <?php } ?>
            <?php
            if ( 'multi-select' === $field['type'] ) {
                $value_str = is_array( $value ) ? implode( ',', $value ) : $value;
                ?>
                <select
                    id="<?php echo $field_key; ?>"
                    name="<?php echo $name; ?>[]"
                    data-value="<?php echo $value_str; ?>"
                    :class="setting_class_names('dropdown')"
                    multiple
                >
                    <?php
                    foreach ( $field['options'] as $index => $option ) {
                        if ( is_array( $value ) ) {
                            // phpcs:ignore WordPress.PHP
                            $selected = in_array( $index, $value ) ? 'selected' : '';

                            printf(
                                '<option value="%s" %s>%s</option>', esc_attr( $index ), $selected, esc_html( $option )
                            );
                        } else {
                            printf(
                                '<option value="%s">%s</option>', esc_attr( $index ), esc_html( $option )
                            );
                        }
                    }
                    ?>
                </select>
            <?php } ?>
            <?php
            if ( 'text' === $field['type'] || 'number' === $field['type'] ) {
                ?>
                <input
                    :class="setting_class_names('text')"
                    type="<?php echo $field['type']; ?>"
                    name="<?php echo $name; ?>"
                    <?php echo ! empty( $field['placeholder'] ) ? 'placeholder=' . $field['placeholder'] : ''; ?>
                    id="<?php echo $field_key; ?>"
                    value="<?php echo $value; ?>"/>
            <?php } ?>
            <?php
            if ( 'textarea' === $field['type'] ) {

                ?>
                <textarea
                    :class="setting_class_names('textarea')"
                    rows="6"
                    name="<?php echo $name; ?>"
                    id="<?php echo $field_key; ?>"><?php echo $value; ?></textarea>
            <?php } ?>

            <?php
            if ( 'pic-radio' === $field['type'] ) {
                ?>
                <div class="wpuf-grid wpuf-grid-cols-4 wpuf-pic-radio" id="<?php echo $field_key; ?>">
                    <?php
                    foreach ( $field['options'] as $key => $option ) {
                        ?>
                        <div class="wpuf-relative wpuf-text-center wpuf-p-3 wpuf-pl-0 wpuf-pt-0">
                            <label>
                                <input
                                    type="radio"
                                    name="<?php echo $name; ?>"
                                    value="<?php echo $key; ?>"
                                    <?php echo esc_attr( checked( $value, $key, false ) ); ?>
                                    class="wpuf-absolute wpuf-opacity-0 wpuf-peer">
                                <img
                                    class="wpuf-absolute wpuf-opacity-0 peer-checked:wpuf-opacity-100 wpuf-top-[7%] wpuf-right-[12%] wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out"
                                    src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/checked-green.svg' ); ?>" alt="">
                                <img
                                    src="<?php echo $option['image']; ?>"
                                    alt="<?php echo $key; ?>"
                                    class="hover:wpuf-cursor-pointer wpuf-border-transparent wpuf-border-2 wpuf-border-solid wpuf-rounded-lg hover:wpuf-border-primary peer-checked:wpuf-border-primary wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-mb-2 wpuf-w-full">
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
                <div class="wpuf-relative">
                    <input
                        :class="setting_class_names('<?php echo $field['trailing_type']; ?>')"
                        type="<?php echo $field['trailing_type']; ?>"
                        name="<?php echo $name; ?>"
                        id="<?php echo $field_key; ?>"
                        value="<?php echo $value; ?>"/>
                    <span
                        class="wpuf-absolute wpuf-top-0 wpuf--right-px wpuf-h-full wpuf-bg-gray-50 wpuf-rounded-r-[6px] wpuf-text-gray-700 wpuf-border wpuf-border-gray-300 wpuf-text-base wpuf-py-[7px] wpuf-px-[15px]">
                        <?php echo $field['trailing_text']; ?>
                    </span>
                </div>
                <?php
            }

            if ( 'date' === $field['type'] ) {
                ?>
                <input
                    :class="setting_class_names('text')"
                    class="datepicker"
                    type="text"
                    name="<?php echo $name; ?>"
                    id="<?php echo $field_key; ?>"
                    value="<?php echo $value; ?>"/>
                <?php
            }

            if ( ! empty( $field['notice'] ) ) {
                ?>
                <div class="wpuf-bg-yellow-50 wpuf-border-l-4 wpuf-border-yellow-500 wpuf-text-yellow-700 wpuf-p-4">
                    <p class="wpuf-m-0"><?php echo $field['notice']['text']; ?></p>
                </div>

                <?php
            }

            if ( ! empty( $field['long_help'] ) ) {
                ?>
                <div class="wpuf-text-sm wpuf-mt-4 wpuf-long-help">
                    <?php echo wp_kses_post( $field['long_help'] ); ?>
                </div>
                <?php
            }
            ?>

        </div>
        <?php
    } else {
        ?>
        <div class="wpuf-mt-6 wpuf-flex wpuf-input-container">
            <?php
            $index_counter = 0;
            foreach ( $field['fields'] as $inner_field_key => $inner_field ) {
                $classes = 'wpuf-w-1/2';
                $classes .= $index_counter === 0 ? ' wpuf-mr-6' : '';

                $value = ! empty( $inner_field['default'] ) ? $inner_field['default'] : '';
                $value = ! empty( $inner_field['value'] ) ? $inner_field['value'] : $value;                 // default value

                // if the field is a pro fields preview, no need to load fields from db
                if ( empty( $inner_field['pro_preview'] ) ) {
                    $value = isset( $form_settings[ $inner_field_key ] ) ? $form_settings[ $inner_field_key ] : $value;   // checking with isset because saved value can be empty string
                }

                ++$index_counter;
                ?>
                <div
                    class="<?php echo $classes; ?>">
                    <label
                        for="<?php echo $inner_field_key; ?>"
                        class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        <?php echo $inner_field['label']; ?>
                    </label>
                    <?php
                    if ( 'text' === $inner_field['type'] || 'number' === $inner_field['type'] ) {
                        ?>
                        <input
                            :class="setting_class_names('text')"
                            class="!wpuf-mt-2"
                            type="<?php echo $inner_field['type']; ?>"
                            name="wpuf_settings[<?php echo $inner_field_key; ?>]"
                            <?php echo ! empty( $inner_field['placeholder'] ) ? 'placeholder=' . $inner_field['placeholder'] : ''; ?>
                            id="<?php echo $inner_field_key; ?>"
                            value="<?php echo $value; ?>"/>
                        <?php
                    }

                    if ( 'date' === $inner_field['type'] ) {
                        ?>
                        <input
                            :class="setting_class_names('text')"
                            class="datepicker !wpuf-mt-2"
                            type="text"
                            name="wpuf_settings[<?php echo $inner_field_key; ?>]"
                            id="<?php echo $inner_field_key; ?>"
                            value="<?php echo $value; ?>"/>
                        <?php
                    }

                    if ( 'select' === $inner_field['type'] ) {
                        $value_str = is_array( $value ) ? implode( ',', $value ) : $value;
                        ?>
                        <select
                            id="<?php echo $inner_field_key; ?>"
                            name="<?php echo $inner_field_key; ?>"
                            data-value="<?php echo $value_str; ?>"
                            class="!wpuf-mt-2"
                            :class="setting_class_names('dropdown')">
                            <?php
                            foreach ( $inner_field['options'] as $index => $option ) {
                                printf( '<option data-select-value="%s" data-select-index="%s" value="%s"%s>%s</option>', $value, $index, esc_attr( $index ), esc_attr( selected( $value, $index, false ) ), esc_html( $option ) );
                            }
                            ?>
                        </select>
                    <?php } ?>
                </div>
                <?php
            }

            if ( ! empty( $field['long_help'] ) ) {
                ?>
            <div class="wpuf-text-sm wpuf-mt-4 wpuf-long-help">
                <?php echo wp_kses_post( $field['long_help'] ); ?>
            </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    do_action( 'wpuf_after_post_form_settings_field_' . $field_key, $field, $value );
    do_action( 'wpuf_after_post_form_settings_field', $field, $value );
}
