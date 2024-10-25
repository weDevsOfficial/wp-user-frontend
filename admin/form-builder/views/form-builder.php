<form id="wpuf-form-builder"
    class="!wpuf-w-[calc(100%+20px)] !wpuf-initial !wpuf-relative !wpuf-top-0 !wpuf-bg-white !wpuf-p-0 wpuf-ml-[-20px] wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>" method="post" action="" @submit.prevent="save_form_builder" v-cloak>
    <div class="wpuf-flex wpuf-bg-white wpuf-px-[20px] wpuf-pt-4 wpuf-justify-between wpuf-items-center wpuf-border-b wpuf-border-slate-200 wpuf-pb-4">
        <div class="wpuf-flex">
            <img :src="logoUrl" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
            <nav class="wpuf-flex wpuf-space-x-8 wpuf-items-center" aria-label="Tabs">
                <div class="wpuf-relative wpuf-flex">
                    <div
                        @click.prevent="post_title_editing = true"
                        v-show="!post_title_editing"
                        class="wpuf-rounded-md wpuf-shadow-sm">
                        <input
                            v-model="post.post_title"
                            type="text"
                            name="post_title"
                            class="wpuf-block wpuf-w-full !wpuf-rounded-none !wpuf-rounded-l-md !wpuf-py-1 !wpuf-px-4 wpuf-text-gray-900  placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 !wpuf-border !wpuf-border-gray-300">
                    </div>
                    <button
                        v-show="!post_title_editing"
                        @mouseover="is_form_switcher = true"
                        @mouseleave="is_form_switcher = false"
                        type="button"
                        class="wpuf-dropdown-container wpuf-relative wpuf--ml-px wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 wpuf-rounded-r-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-border wpuf-border-l-0 wpuf-rounded-r-lg wpuf-border-gray-300 hover:wpuf-bg-gray-50">
                        <i :class="(is_form_switcher ? 'fa fa-angle-up' : 'fa fa-angle-down') + ' form-switcher-arrow'"></i>
                        <div
                            class="wpuf-dropdown-item wpuf-absolute wpuf--left-40 wpuf-z-10 wpuf-w-max wpuf-origin-top-right wpuf-rounded-md wpuf-bg-white wpuf-shadow-lg focus:wpuf-shadow-none focus:wpuf-outline-none wpuf-top-9"
                            role="menu"
                            aria-orientation="vertical"
                            aria-labelledby="menu-button">
                            <div class="wpuf-py-1" role="none">
                                <?php
                                foreach ( $forms as $form ) {
                                    ?>
                                    <a class="wpuf-block wpuf-text-left wpuf-px-4 wpuf-py-2 !wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100 hover:wpuf-text-gray-900 focus:wpuf-shadow-none focus:wpuf-outline-none" href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-' . $form_type . '-forms&action=edit&id=' . $form->ID ) ); ?>"><?php echo esc_html( $form->post_title ); ?></a>
                                    <?php
                                }
                                ?>

                            </div>
                        </div>
                    </button>
                    <div
                        v-show="post_title_editing"
                        class="wpuf-flex wpuf-rounded-md wpuf-shadow-sm">
                        <div class="wpuf-relative wpuf-flex wpuf-flex-grow wpuf-items-stretch focus-within:wpuf-z-10">
                            <input
                                v-model="post.post_title"
                                type="text"
                                name="post_title"
                                class="wpuf-block wpuf-w-full !wpuf-rounded-none !wpuf-rounded-l-md !wpuf-py-1 !wpuf-px-4 wpuf-text-gray-900  placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 !wpuf-border !wpuf-border-gray-300">
                        </div>
                    </div>
                    <button
                        v-show="post_title_editing"
                        @click.prevent="post_title_editing = false"
                        type="button"
                        class="wpuf-dropdown-container wpuf-relative wpuf--ml-px wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 wpuf-rounded-r-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-border wpuf-border-l-0 wpuf-rounded-r-lg wpuf-border-gray-300 hover:wpuf-bg-gray-50">
                        <i class="fa fa-check"></i>
                    </button>
                </div>
                <a href="#wpuf-form-builder-container" class="wpuf-whitespace-nowrap wpuf-border-b-2 wpuf-border-indigo-500 wpuf-px-1 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-indigo-600 focus:wpuf-shadow-none focus:wpuf-outline-none">
                    <?php esc_html_e( 'Form Editor', 'wp-user-frontend' ); ?>
                </a>

                <a href="#wpuf-form-builder-settings" class="wpuf-whitespace-nowrap wpuf-border-b-2 wpuf-border-transparent wpuf-px-1 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 hover:wpuf-border-gray-300 hover:wpuf-text-gray-700 focus:wpuf-shadow-none focus:wpuf-outline-none">
                    <?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?>
                </a>

                <?php do_action( "wpuf-form-builder-tabs-{$form_type}" ); ?>
            </nav>
        </div>
        <div class="wpuf-flex wpuf-space-x-4 wpuf-items-center">
            <?php
            $form_id = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : 0;

            if ( count( $shortcodes ) > 1 && isset( $shortcodes[0]['type'] ) ) {
                foreach ( $shortcodes as $shortcode ) {
                    /* translators: %s: form id */
                    printf( "<span class=\"form-id\" title=\"%s\" data-clipboard-text='%s'><i class=\"fa fa-clipboard\" aria-hidden=\"true\"></i> %s: #{{ post.ID }}</span>", sprintf( esc_html( __( 'Click to copy %s shortcode', 'wp-user-frontend' ) ), esc_attr( $shortcode['type'] ) ), sprintf( '[%s type="%s" id="%s"]', esc_attr( $shortcode['name'] ), esc_attr( $shortcode['type'] ), esc_attr( $form_id ) ), esc_attr( ucwords( $shortcode['type'] ) ), esc_attr( $shortcode['type'] ) );
                }
            } else {
                ?>
                <span class="form-id wpuf-group wpuf-flex wpuf-items-center wpuf-py-2 wpuf-px-4 wpuf-rounded-md wpuf-border wpuf-border-slate-300 hover:wpuf-cursor-pointer" title="<?php esc_html_e( __( 'Click to copy shortcode', 'wp-user-frontend' ) ); ?>" data-clipboard-text="<?php '[' . esc_attr_e( $shortcodes[0]['name'] ) . ' id="' . esc_attr( $form_id ) . '"]'; ?>">#{{ post.ID }}
                    <span id="default-icon" class="wpuf-ml-2">
                        <svg
                            v-if="!shortcodeCopied"
                            class="group-hover:wpuf-rotate-6 group-hover:wpuf-stroke-gray-500 wpuf-stroke-gray-400"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.4438 6.17602C14.2661 5.49738 13.687 5 13 5H11C10.313 5 9.73391 5.49738 9.55618 6.17602M14.4438 6.17602C14.4804 6.31575 14.5 6.46317 14.5 6.61552C14.5 6.91293 14.2761 7.15403 14 7.15403H10C9.72386 7.15403 9.5 6.91293 9.5 6.61552C9.5 6.46317 9.51958 6.31575 9.55618 6.17602M14.4438 6.17602C14.8746 6.21105 15.303 6.25528 15.7289 6.30851C16.4626 6.40022 17 7.08151 17 7.87705V16.897C17 17.7892 16.3284 18.5125 15.5 18.5125H8.5C7.67157 18.5125 7 17.7892 7 16.897V7.87705C7 7.08151 7.53739 6.40022 8.27112 6.30851C8.69698 6.25528 9.12539 6.21105 9.55618 6.17602M9.99997 11.4122H14M9.99997 14.0125H12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        <svg
                            v-if="shortcodeCopied"
                            class="wpuf-rotate-6 !wpuf-stroke-indigo-600 wpuf-mt-[-5px]"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.2392 3V5.15403M7.81196 4.54199L9.22618 6.06512M16.6663 4.54199L15.2521 6.06512M14.4438 8.66349C14.2661 7.98485 13.687 7.48747 13 7.48747H11C10.313 7.48747 9.73391 7.98485 9.55618 8.66349M14.4438 8.66349C14.4804 8.80322 14.5 8.95064 14.5 9.10299C14.5 9.4004 14.2761 9.6415 14 9.6415H10C9.72386 9.6415 9.5 9.4004 9.5 9.10299C9.5 8.95064 9.51958 8.80322 9.55618 8.66349M14.4438 8.66349C14.8746 8.69852 15.303 8.74275 15.7289 8.79598C16.4626 8.88769 17 9.56898 17 10.3645V19.3845C17 20.2767 16.3284 21 15.5 21H8.5C7.67157 21 7 20.2767 7 19.3845V10.3645C7 9.56898 7.53739 8.88769 8.27112 8.79598C8.69698 8.74275 9.12539 8.69852 9.55618 8.66349M9.99997 13.8997H14M9.99997 16.5H12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </span>
                <?php
            }
            ?>
            <a :href="'<?php echo get_wpuf_preview_page(); ?>?wpuf_preview=1&form_id=' + post.ID" target="_blank" class="wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50"><?php esc_html_e( 'Preview', 'wp-user-frontend' ); ?></a>
            <button
                v-if="!is_form_saving"
                @click="save_form_builder"
                type="button"
                :disabled="is_form_saving"
                :class="is_form_saving ? 'wpuf-cursor-wait' : 'wpuf-cursor-pointer'"
                class="wpuf-rounded-full wpuf-bg-indigo-600 wpuf-px-3.5 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"><?php esc_html_e( 'Save Form', 'wp-user-frontend' ); ?></button>

            <button v-else type="button" class="button button-primary button-ajax-working" disabled>
                <span class="loader"></span> <?php esc_html_e( 'Saving Form Data', 'wp-user-frontend' ); ?>
            </button>
        </div>
    </div>
    <div class="wpuf-flex">
        <div class="wpuf-w-2/3 wpuf-bg-white wpuf-min-h-screen wpuf-px-[20px] wpuf-pt-4">
            <builder-stage></builder-stage>
        </div>
        <div class="wpuf-w-1/3 wpuf-bg-gray-50 wpuf-px-[20px] wpuf-pt-4">Field attributes</div>
    </div>
</form>
