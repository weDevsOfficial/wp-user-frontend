<form id="wpuf-form-builder"
    class="wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>" method="post" action="" @submit.prevent="save_form_builder" v-cloak>
    <div class="wpuf-flex wpuf-bg-white wpuf-px-[20px] wpuf-pt-4 wpuf-justify-between wpuf-items-center wpuf-border-b wpuf-border-slate-200 wpuf-pb-4">
        <div class="wpuf-flex">
            <img src="https://wpuf.test/wp-content/plugins/wp-user-frontend/assets/images/wpuf-icon-circle.svg" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
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
                            :class="!shortcodeCopied ? 'wpuf-fill-gray-300' : '!wpuf-fill-blue-500 wpuf-rotate-6'"
                            class="wpuf-w-3.5 wpuf-h-3.5 group-hover:wpuf-rotate-6 group-hover:wpuf-fill-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                            <g class=""><path d="M15.9975 5.99988L15.9975 3.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19.9975 5.99988L20.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.9975 5.99988L10.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></g>
                            <path
                                d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
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
            builder stage here
<!--            <builder-stage></builder-stage>-->
        </div>
        <div class="wpuf-w-1/3 wpuf-bg-gray-50 wpuf-px-[20px] wpuf-pt-4">Field attributes</div>
    </div>
</form>
