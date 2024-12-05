<form id="wpuf-form-builder"
    class="wpuf-bg-white wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>"
    method="post"
    action="" @submit.prevent="save_form_builder" v-cloak>
    <div class="wpuf-bg-white wpuf-px-[20px] wpuf-pt-8 wpuf-justify-between wpuf-items-center wpuf-pb-4">
        <div class="wpuf-flex wpuf-justify-between">
            <div class="wpuf-flex">
                <img src="<?php echo WPUF_ASSET_URI . '/images/wpuf-icon-circle.svg'; ?>" alt="WPUF Icon" class="wpuf-mr-2">
                <nav class="wpuf-flex wpuf-items-center" aria-label="Tabs">
                    <div class="wpuf-relative wpuf-flex">
                        <div class="wpuf-flex wpuf-items-center">
                            <input
                                @click="post_title_editing = !post_title_editing"
                                v-model="post.post_title"
                                type="text"
                                name="post_title"
                                :class="post_title_editing ? '' : '!wpuf-border-0'"
                                class="wpuf-text-gray-900 placeholder:wpuf-text-gray-400 wpuf-font-medium wpuf-min-w-16 wpuf-max-w-32">
                            <i
                                v-if="post_title_editing"
                                @click="post_title_editing = !post_title_editing"
                                class="fa fa-check !wpuf-leading-none hover:wpuf-cursor-pointer wpuf-ml-1 wpuf-text-base"></i>
                            <div
                                v-if="!post_title_editing"
                                class="wpuf-dropdown wpuf-dropdown-bottom wpuf-dropdown-end">
                                <div
                                    tabindex="0"
                                    role="button"
                                    class="wpuf-btn wpuf-m-1 wpuf-h-min wpuf-min-h-min wpuf-border-0 wpuf-ring-0 wpuf-shadow-none wpuf-p-0">
                                    <i
                                        :class="is_form_switcher ? 'fa fa-angle-up' : 'fa fa-angle-down'"
                                        class="form-switcher-arrow !wpuf-font-bold !wpuf-text-xl !wpuf-leading-none"
                                    ></i>
                                </div>
                                <ul tabindex="0" class="wpuf-dropdown-content wpuf-menu !wpuf-p-0 wpuf-rounded-md wpuf-z-[1] wpuf-w-52 wpuf-shadow">
                                    <?php
                                    foreach ( $forms as $form ) {
                                        ?>
                                        <li>
                                            <a class="wpuf-block wpuf-rounded-none wpuf-font-medium wpuf-text-left wpuf-px-4 wpuf-py-2 !wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100 hover:wpuf-text-gray-900 focus:wpuf-shadow-none focus:wpuf-outline-none" href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-' . $form_type . '-forms&action=edit&id=' . $form->ID ) ); ?>">
                                                <?php echo esc_html( $form->post_title ); ?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <?php
                $form_id = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : 0;

                if ( count( $shortcodes ) > 1 && isset( $shortcodes[0]['type'] ) ) {
                    foreach ( $shortcodes as $shortcode ) {
                        /* translators: %s: form id */
                        printf( "<span class=\"form-id\" title=\"%s\" data-clipboard-text='%s'><i class=\"fa fa-clipboard\" aria-hidden=\"true\"></i> %s: #{{ post.ID }}</span>", sprintf( esc_html( __( 'Click to copy %s shortcode', 'wp-user-frontend' ) ), esc_attr( $shortcode['type'] ) ), sprintf( '[%s type="%s" id="%s"]', esc_attr( $shortcode['name'] ), esc_attr( $shortcode['type'] ), esc_attr( $form_id ) ), esc_attr( ucwords( $shortcode['type'] ) ), esc_attr( $shortcode['type'] ) );
                    }
                } else {
                    ?>
                    <span class="form-id wpuf-group wpuf-flex wpuf-items-center wpuf-px-2 wpuf-rounded-md wpuf-border wpuf-border-gray-300 hover:wpuf-cursor-pointer wpuf-ml-6" title="<?php esc_html_e( __( 'Click to copy shortcode', 'wp-user-frontend' ) ); ?>" data-clipboard-text="<?php '[' . esc_attr_e( $shortcodes[0]['name'] ) . ' id="' . esc_attr( $form_id ) . '"]'; ?>">#{{ post.ID }}
                    <span id="default-icon" class="wpuf-ml-2">
                        <svg
                            v-if="!shortcodeCopied"
                            class="group-hover:wpuf-rotate-6 group-hover:wpuf-stroke-gray-500 wpuf-stroke-gray-400"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.4438 6.17602C14.2661 5.49738 13.687 5 13 5H11C10.313 5 9.73391 5.49738 9.55618 6.17602M14.4438 6.17602C14.4804 6.31575 14.5 6.46317 14.5 6.61552C14.5 6.91293 14.2761 7.15403 14 7.15403H10C9.72386 7.15403 9.5 6.91293 9.5 6.61552C9.5 6.46317 9.51958 6.31575 9.55618 6.17602M14.4438 6.17602C14.8746 6.21105 15.303 6.25528 15.7289 6.30851C16.4626 6.40022 17 7.08151 17 7.87705V16.897C17 17.7892 16.3284 18.5125 15.5 18.5125H8.5C7.67157 18.5125 7 17.7892 7 16.897V7.87705C7 7.08151 7.53739 6.40022 8.27112 6.30851C8.69698 6.25528 9.12539 6.21105 9.55618 6.17602M9.99997 11.4122H14M9.99997 14.0125H12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        <svg
                            v-if="shortcodeCopied"
                            class="wpuf-rotate-6 !wpuf-stroke-primary wpuf-mt-[-5px]"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.2392 3V5.15403M7.81196 4.54199L9.22618 6.06512M16.6663 4.54199L15.2521 6.06512M14.4438 8.66349C14.2661 7.98485 13.687 7.48747 13 7.48747H11C10.313 7.48747 9.73391 7.98485 9.55618 8.66349M14.4438 8.66349C14.4804 8.80322 14.5 8.95064 14.5 9.10299C14.5 9.4004 14.2761 9.6415 14 9.6415H10C9.72386 9.6415 9.5 9.4004 9.5 9.10299C9.5 8.95064 9.51958 8.80322 9.55618 8.66349M14.4438 8.66349C14.8746 8.69852 15.303 8.74275 15.7289 8.79598C16.4626 8.88769 17 9.56898 17 10.3645V19.3845C17 20.2767 16.3284 21 15.5 21H8.5C7.67157 21 7 20.2767 7 19.3845V10.3645C7 9.56898 7.53739 8.88769 8.27112 8.79598C8.69698 8.74275 9.12539 8.69852 9.55618 8.66349M9.99997 13.8997H14M9.99997 16.5H12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </span>
                    <?php
                }
                ?>
            </div>
            <div class="right-items">
                <div class="wpuf-flex wpuf-space-x-4 wpuf-items-center">
                    <a
                        :href="'<?php echo get_wpuf_preview_page(); ?>?wpuf_preview=1&form_id=' + post.ID"
                        target="_blank"
                        class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300"><?php esc_html_e( 'Preview', 'wp-user-frontend' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpuf-size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </a>
                    <button
                        v-if="!is_form_saving"
                        @click="save_form_builder"
                        type="button"
                        :disabled="is_form_saving"
                        :class="is_form_saving ? 'wpuf-cursor-wait' : 'wpuf-cursor-pointer'"
                        class="wpuf-btn-primary"><?php esc_html_e( 'Save Form', 'wp-user-frontend' ); ?></button>
                    <button v-else type="button" class="button button-primary button-ajax-working" disabled>
                        <span class="loader"></span> <?php esc_html_e( 'Saving Form Data', 'wp-user-frontend' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="wpuf-flex wpuf-items-center wpuf-mt-8">
            <div class="wpuf-flex wpuf-bg-gray-100 wpuf-w-max wpuf-rounded-xl wpuf-p-4 wpuf-mr-6">
                <div class="wpuf-tab-contents">
                    <a
                        href="#wpuf-form-builder-container"
                        @click="active_tab = 'form-editor'"
                        :class="active_tab === 'form-editor' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-rounded-md wpuf-drop-shadow-sm' : ''"
                        class="wpuf-nav-tab wpuf-nav-tab-active wpuf-text-gray-800 wpuf-py-2 wpuf-px-4 wpuf-text-sm hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-rounded-md hover:wpuf-drop-shadow-sm focus:wpuf-shadow-none wpuf-mr-2">
                        <?php esc_html_e( 'Form Editor', 'wp-user-frontend' ); ?>
                    </a>
                    <a
                        href="#wpuf-form-builder-settings"
                        @click="active_tab = 'form-settings'"
                        :class="active_tab === 'form-settings' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-rounded-md wpuf-drop-shadow-sm' : ''"
                        class="wpuf-nav-tab wpuf-nav-tab-active wpuf-text-gray-800 wpuf-py-2 wpuf-px-4 wpuf-text-sm hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-rounded-md hover:wpuf-drop-shadow-sm focus:wpuf-shadow-none wpuf-mr-2">
                        <?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?>
                    </a>
                    <?php do_action( "wpuf-form-builder-tabs-{$form_type}" ); ?>
                </div>
            </div>
            <button
                @click="enableMultistep = !enableMultistep"
                type="button"
                class="wpuf-mx-4 wpuf-group wpuf-relative wpuf-inline-flex wpuf-h-5 wpuf-w-10 shrink-0 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-full"
                role="switch"
                aria-checked="false">
                <span class="wpuf-sr-only">Enable Multistep</span>
                <span aria-hidden="true" class="wpuf-pointer-events-none wpuf-absolute wpuf-h-full wpuf-w-full wpuf-rounded-md wpuf-bg-white"></span>
                <span
                    aria-hidden="true"
                    :class="enableMultistep ? 'wpuf-bg-primary' : 'wpuf-bg-gray-200'"
                    class="wpuf-pointer-events-none wpuf-absolute wpuf-mx-auto wpuf-h-4 wpuf-w-9 wpuf-rounded-full wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out"></span>
                <span
                    aria-hidden="true"
                    :class="enableMultistep ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                    class="wpuf-pointer-events-none wpuf-absolute wpuf-left-0 wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition-transform wpuf-duration-200 wpuf-ease-in-out"></span>
            </button>
            <?php esc_html_e( 'Enable Multistep', 'wp-user-frontend' ); ?>
        </div>
    </div>
    <div
        v-show="active_tab === 'form-editor'"
        class="wpuf-flex wpuf-bg-white wpuf-pb-16 wpuf-w-[calc(100%-30px)]">
        <div class="wpuf-w-2/3 wpuf-min-h-screen wpuf-max-h-screen wpuf-px-[20px] wpuf-pt-4 wpuf-border-t wpuf-border-gray-200 wpuf-overflow-auto">
            <builder-stage></builder-stage>
        </div>
        <div class="wpuf-w-1/3 wpuf-max-h-screen wpuf-overflow-auto wpuf-rounded-r-lg wpuf-border wpuf-border-gray-200">
            <div class="wpuf-p-6 wpuf-pb-0">
                <div role="tablist" class="wpuf-tabs wpuf-tabs-boxed wpuf-text-gray-500 wpuf-rounded-xl wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-bg-gray-100">
                    <a
                        role="tab"
                        :class="current_panel === 'form-fields' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : ''"
                        class="wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-transition-all"
                        href="#add-fields"
                        @click.prevent="set_current_panel('form-fields')">
                        <?php esc_html_e( 'Add Fields', 'wp-user-frontend' ); ?>
                    </a>
                    <a
                        role="tab"
                        :class="current_panel === 'field-options' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : ''"
                        class="wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-ml-1 wpuf-transition-all"
                        href="#field-options"
                        @click.prevent="set_current_panel('field-options')">
                        <?php esc_html_e( 'Field Options', 'wp-user-frontend' ); ?>
                    </a>
                </div>
                </div>
                <section>
                    <div class="wpuf-form-builder-panel wpuf-mt-6">
                        <component :is="current_panel"></component>
                    </div>
                </section>
            </div>
        </div>
    <div
        v-show="active_tab === 'form-settings'"
        id="wpuf-form-builder-settings"
        class="group clearfix wpuf-flex">
        <div class="wpuf-w-1/3 wpuf-bg-gray-50 wpuf-px-[20px] wpuf-pt-4">
            <div id="wpuf-form-builder-settings-tabs" class="nav-tab-wrapper wpuf-flex wpuf-flex-col">
                <?php do_action( "wpuf-form-builder-settings-tabs-{$form_type}" ); ?>
            </div><!-- #wpuf-form-builder-settings-tabs -->
        </div>
        <div class="wpuf-w-2/3 wpuf-bg-gray-50">
            <div
                id="wpuf-form-builder-settings-contents"
                class="tab-contents">
                <?php do_action( "wpuf-form-builder-settings-tab-contents-{$form_type}" ); ?>
            </div><!-- #wpuf-form-builder-settings-contents -->
        </div>
    </div><!-- #wpuf-form-builder-settings -->
    <?php do_action( "wpuf-form-builder-tab-contents-{$form_type}" ); ?>

    <?php if ( ! empty( $form_settings_key ) ) { ?>
        <input type="hidden" name="form_settings_key" value="<?php echo esc_attr( $form_settings_key ); ?>">
    <?php } ?>

    <?php wp_nonce_field( 'wpuf_form_builder_save_form', 'wpuf_form_builder_nonce' ); ?>

    <input type="hidden" name="wpuf_form_id" value="<?php echo esc_attr( $form_id ); ?>">
</form>
