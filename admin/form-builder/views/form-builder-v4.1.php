<form id="wpuf-form-builder"
    class="!wpuf-bg-white !wpuf-static !wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] !wpuf-p-0 wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>"
    method="post"
    action="" @submit.prevent="save_form_builder" v-cloak>
    <div class="wpuf-bg-white wpuf-p-8 wpuf-justify-between wpuf-items-center wpuf-pb-7">
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
                                :class="post_title_editing ? '' : '!wpuf-border-transparent'"
                                class="wpuf-text-gray-900 wpuf-text-base wpuf-field-sizing-content focus:!wpuf-ring-primary focus:!wpuf-border-transparent focus:!wpuf-shadow-none">
                            <i
                                v-if="post_title_editing"
                                @click="post_title_editing = !post_title_editing"
                                class="fa fa-check !wpuf-leading-none hover:wpuf-cursor-pointer wpuf-ml-1 wpuf-text-base"></i>
                            <div
                                v-if="!post_title_editing"
                                class="wpuf-dropdown wpuf-dropdown-bottom wpuf-dropdown-end wpuf-relative">
                                <div
                                    tabindex="0"
                                    role="button"
                                    class="wpuf-btn wpuf-m-1 wpuf-h-min wpuf-min-h-min wpuf-border-0 wpuf-ring-0 wpuf-shadow-none wpuf-p-0">
                                    <i
                                        :class="is_form_switcher ? 'fa fa-angle-up' : 'fa fa-angle-down'"
                                        class="form-switcher-arrow !wpuf-font-bold !wpuf-text-xl !wpuf-leading-none"
                                    ></i>
                                </div>
                                <ul tabindex="0" class="wpuf-absolute wpuf-dropdown-content wpuf-menu !wpuf-p-0 wpuf-rounded-md wpuf-z-[1] wpuf-w-52 wpuf-shadow wpuf-bg-white !wpuf-right-[-4rem] !wpuf-top-8">
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
                        ?>
                        <span
                            class="form-id wpuf-group wpuf-flex wpuf-items-center wpuf-px-[18px] wpuf-py-[10px] wpuf-rounded-md wpuf-border wpuf-border-gray-300 hover:wpuf-cursor-pointer wpuf-ml-6 wpuf-text-gray-700 wpuf-text-base wpuf-leading-none wpuf-shadow-sm"
                            title="<?php printf( esc_attr( __( 'Click to copy %s shortcode', 'wp-user-frontend' ) ), $shortcode['type'] ); ?>"
                            data-clipboard-text="<?php printf( esc_attr( '[' . $shortcode['name'] . ' type="' . esc_attr( $shortcode['type'] ) . '" id="' . esc_attr( $form_id ) . '"]' ) ); ?>"><?php echo esc_attr( ucwords( $shortcode['type'] ) ); ?>: #{{ post.ID }}
                            <span id="default-icon" class="wpuf-ml-2">
                                <svg
                                    class="group-hover:wpuf-rotate-6 group-hover:wpuf-stroke-gray-500 wpuf-stroke-gray-400"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </span>
                    </span>
                            <?php
                    }
                } else {
                    ?>
                    <span
                        class="form-id wpuf-group wpuf-flex wpuf-items-center wpuf-px-[18px] wpuf-py-[10px] wpuf-rounded-md wpuf-border wpuf-border-gray-300 hover:wpuf-cursor-pointer wpuf-ml-6 wpuf-text-gray-700 wpuf-text-base wpuf-leading-none wpuf-shadow-sm"
                        title="<?php printf( esc_html( __( 'Click to copy shortcode', 'wp-user-frontend' ) ) ); ?>"
                        data-clipboard-text="<?php printf( esc_attr( '[' . $shortcodes[0]['name'] . ' id="' . esc_attr( $form_id ) . '"]' ) ); ?>">#{{ post.ID }}
                        <span id="default-icon" class="wpuf-ml-2">
                            <svg
                                v-if="!shortcodeCopied"
                                class="group-hover:wpuf-rotate-6 group-hover:wpuf-stroke-gray-500 wpuf-stroke-gray-400"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
            <div class="wpuf-flex wpuf-space-x-4">
                <a
                    :href="'<?php echo get_wpuf_preview_page(); ?>?wpuf_preview=1&form_id=' + post.ID"
                    target="_blank"
                    class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-3 wpuf-rounded-md wpuf-px-[18px] wpuf-py-[10px] wpuf-text-base wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 focus:wpuf-shadow-none focus:wpuf-border-none wpuf-leading-none wpuf-shadow-sm"><?php esc_html_e( 'Preview', 'wp-user-frontend' ); ?>
                    <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.69947 7.26867C1.6419 7.09594 1.64184 6.90895 1.69931 6.73619C2.85628 3.2581 6.13716 0.75 10.0038 0.75C13.8687 0.75 17.1484 3.25577 18.3068 6.73134C18.3643 6.90406 18.3644 7.09106 18.3069 7.26381C17.15 10.7419 13.8691 13.25 10.0024 13.25C6.1375 13.25 2.85787 10.7442 1.69947 7.26867Z" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5032 7C12.5032 8.38071 11.3839 9.5 10.0032 9.5C8.62246 9.5 7.50317 8.38071 7.50317 7C7.50317 5.61929 8.62246 4.5 10.0032 4.5C11.3839 4.5 12.5032 5.61929 12.5032 7Z" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <button
                    v-if="!is_form_saving"
                    @click="save_form_builder"
                    type="button"
                    :disabled="is_form_saving"
                    :class="is_form_saving ? 'wpuf-cursor-wait' : 'wpuf-cursor-pointer'"
                    class="wpuf-btn-primary wpuf-leading-none"><?php esc_html_e( 'Save', 'wp-user-frontend' ); ?></button>
                <button v-else type="button" class="button button-primary button-ajax-working" disabled>
                    <span class="loader"></span> <?php esc_html_e( 'Saving Form Data', 'wp-user-frontend' ); ?>
                </button>
            </div>
        </div>
        <div class="wpuf-flex wpuf-items-center wpuf-mt-8">
            <div class="wpuf-flex wpuf-bg-gray-100 wpuf-w-max wpuf-rounded-lg wpuf-p-2">
                <a
                    @click="active_tab = 'form-editor'"
                    :class="active_tab === 'form-editor' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-rounded-md wpuf-drop-shadow-sm' : 'wpuf-text-gray-500'"
                    class="wpuf-nav-tab wpuf-nav-tab-active  wpuf-py-2 wpuf-px-4 wpuf-text-base hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-rounded-md hover:wpuf-drop-shadow-sm focus:wpuf-shadow-none wpuf-mr-2 hover:wpuf-cursor-pointer">
                    <?php esc_html_e( 'Form Editor', 'wp-user-frontend' ); ?>
                </a>
                <a
                    @click="active_tab = 'form-settings'"
                    :class="active_tab === 'form-settings' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-rounded-md wpuf-drop-shadow-sm' : 'wpuf-text-gray-500'"
                    class="wpuf-nav-tab wpuf-nav-tab-active  wpuf-py-2 wpuf-px-4 wpuf-text-base hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-rounded-md hover:wpuf-drop-shadow-sm focus:wpuf-shadow-none wpuf-mr-2 hover:wpuf-cursor-pointer">
                    <?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?>
                </a>
                <?php do_action( "wpuf-form-builder-tabs-{$form_type}" ); ?>
            </div>
        </div>
    </div>
    <div
        v-show="active_tab === 'form-editor'"
        class="wpuf-flex wpuf-bg-white wpuf-mr-8 ">
        <div class="wpuf-w-2/3 wpuf-min-h-screen wpuf-max-h-screen wpuf-px-[52px] wpuf-py-4 wpuf-border-t wpuf-border-l wpuf-border-gray-200 wpuf-overflow-auto">
            <builder-stage-v4-1></builder-stage-v4-1>
        </div>
        <div class="wpuf-w-1/3 wpuf-max-h-screen wpuf-overflow-auto wpuf-rounded-tr-lg wpuf-border wpuf-border-b-0 wpuf-border-gray-200">
            <div class="wpuf-p-6 wpuf-pb-0 wpuf-mb-8">
                <div role="tablist" class="wpuf-tabs wpuf-tabs-boxed wpuf-text-gray-500 wpuf-rounded-xl wpuf-px-3 wpuf-py-2 wpuf-text-base wpuf-font-medium wpuf-bg-gray-100">
                    <a
                        role="tab"
                        :class="current_panel === 'form-fields-v4-1' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : ''"
                        class="wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-transition-all"
                        href="#add-fields"
                        @click.prevent="set_current_panel('form-fields-v4-1')">
                        <?php esc_html_e( 'Add Fields', 'wp-user-frontend' ); ?>
                    </a>
                    <a
                        role="tab"
                        :class="current_panel === 'field-options' ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : 'wpuf-text-gray-500'"
                        class="wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-ml-1 wpuf-transition-all"
                        href="#field-options"
                        @click.prevent="set_current_panel('field-options')">
                        <?php esc_html_e( 'Field Options', 'wp-user-frontend' ); ?>
                    </a>
                </div>
                <section>
                    <div class="wpuf-form-builder-panel wpuf-mt-6 wpuf-mb-32">
                        <component :is="current_panel"></component>
                    </div>
                </section>
            </div>
    </div>
    </div>
    <div
        v-show="active_tab === 'form-settings'"
        id="wpuf-form-builder-settings">
        <?php
            do_action( "wpuf_form_builder_settings_tabs_{$form_type}" );
        ?>
    </div><!-- #wpuf-form-builder-settings -->
    <?php do_action( "wpuf-form-builder-tab-contents-{$form_type}" ); ?>

    <?php if ( ! empty( $form_settings_key ) ) { ?>
        <input type="hidden" name="form_settings_key" value="<?php echo esc_attr( $form_settings_key ); ?>">
    <?php } ?>

    <?php wp_nonce_field( 'wpuf_form_builder_save_form', 'wpuf_form_builder_nonce' ); ?>

    <input type="hidden" name="wpuf_form_id" value="<?php echo esc_attr( $form_id ); ?>">
</form>
