<form id="wpuf-form-builder"
      class="wpuf-bg-white wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] wpuf-px-[20px] wpuf-flex wpuf-pt-4 wpuf-justify-between wpuf-items-center wpuf-border-b wpuf-border-slate-200 wpuf-pb-4 wpuf-form-builder-<?php echo esc_attr( $form_type ); ?>" method="post" action="" @submit.prevent="save_form_builder" v-cloak>
        <div class="wpuf-flex">
            <img src="https://wpuf.test/wp-content/plugins/wp-user-frontend/assets/images/wpuf-icon-circle.svg" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
            <nav class="wpuf-flex wpuf-space-x-8 wpuf-items-center" aria-label="Tabs">
                <!-- Current: "wpuf-border-indigo-500 wpuf-text-indigo-600", Default: "wpuf-border-transparent wpuf-text-gray-500 hover:wpuf-border-gray-300 hover:wpuf-text-gray-700" -->
                <div class="wpuf-relative wpuf-flex">
                    <div
                        @click.prevent="post_title_editing = true"
                        v-show="!post_title_editing"
                        class="wpuf-rounded-md wpuf-shadow-sm">
                        <input
                            v-model="post.post_title"
                            type="text"
                            name="post_title"
                            class="wpuf-block wpuf-w-full !wpuf-rounded-none !wpuf-rounded-l-md !wpuf-border-0  !wpuf-py-1 !wpuf-px-4 wpuf-text-gray-900 !wpuf-ring-1 !wpuf-ring-inset !wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">
                    </div>
                    <button
                        v-show="!post_title_editing"
                        @click.prevent="switch_form"
                        type="button"
                        class="wpuf-relative wpuf--ml-px wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 wpuf-rounded-r-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                        <i :class="(is_form_switcher ? 'fa fa-angle-up' : 'fa fa-angle-down') + ' form-switcher-arrow'"></i>
                    </button>
                    <div
                        v-show="post_title_editing"
                        class="wpuf-flex wpuf-rounded-md wpuf-shadow-sm">
                        <div class="wpuf-relative wpuf-flex wpuf-flex-grow wpuf-items-stretch focus-within:wpuf-z-10">
                            <input
                                v-model="post.post_title"
                                type="text"
                                name="post_title"
                                class="wpuf-block wpuf-w-full !wpuf-rounded-none !wpuf-rounded-l-md !wpuf-border-0  !wpuf-py-1 !wpuf-px-4 wpuf-text-gray-900 !wpuf-ring-1 !wpuf-ring-inset !wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">
                        </div>
                    </div>
                    <button
                        v-show="post_title_editing"
                        @click.prevent="post_title_editing = false"
                        type="button"
                        class="wpuf-relative wpuf--ml-px wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 wpuf-rounded-r-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                        <i class="fa fa-check"></i>
                    </button>
                    <!--
                      Dropdown menu, show/hide based on menu state.

                      Entering: "wpuf-transition wpuf-ease-out wpuf-duration-100"
                        From: "wpuf-transform wpuf-opacity-0 wpuf-scale-95"
                        To: "wpuf-transform wpuf-opacity-100 wpuf-scale-100"
                      Leaving: "wpuf-transition wpuf-ease-in wpuf-duration-75"
                        From: "wpuf-transform wpuf-opacity-100 wpuf-scale-100"
                        To: "wpuf-transform wpuf-opacity-0 wpuf-scale-95"
                    -->
                    <div
                        v-show="is_form_switcher"
                        class="wpuf-absolute wpuf-left-0 wpuf-z-10 wpuf-mt-2 wpuf-w-max wpuf-origin-top-right wpuf-rounded-md wpuf-bg-white wpuf-shadow-lg focus:wpuf-shadow-none focus:wpuf-outline-none wpuf-top-[40px]"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="menu-button">
                        <div class="wpuf-py-1" role="none">
                            <!-- Active: "wpuf-bg-gray-100 wpuf-text-gray-900", Not Active: "wpuf-text-gray-700" -->
        <!--                                <a href="#" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700" role="menuitem" tabindex="-1" id="menu-item-0">Post form</a>
                            <a href="#" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">A simple post form with a really long form title</a>
                            <a href="#" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700" role="menuitem" tabindex="-1" id="menu-item-2">Product form</a>
                            <a href="#" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700" role="menuitem" tabindex="-1" id="menu-item-2">conditional post form</a>-->
                            <?php
                            foreach ( $forms as $form ) {
                                ?>
                                <a class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100 hover:wpuf-text-gray-900 focus:wpuf-shadow-none focus:wpuf-outline-none" href="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-' . $form_type . '-forms&action=edit&id=' . $form->ID ) ); ?>"><?php echo esc_html( $form->post_title ); ?></a>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
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
                printf(
                    "<span class=\"form-id wpuf-flex wpuf-items-center wpuf-py-2 wpuf-px-4 wpuf-rounded-md wpuf-border wpuf-border-slate-300 hover:wpuf-cursor-pointer\" title=\"%s\" data-clipboard-text='%s'>#{{ post.ID }} <span id=\"default-icon\" class=\"wpuf-ml-2\">
                        <svg class=\"wpuf-w-3.5 wpuf-h-3.5\" aria-hidden=\"true\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"currentColor\" viewBox=\"0 0 18 20\">
                            <path
                                d=\"M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z\"/>
                        </svg>
                    </span>
                    <span id=\"success-icon\" class=\"wpuf-hidden wpuf-ml-2 wpuf-inline-flex wpuf-items-center\">
                        <svg class=\"wpuf-w-3.5 wpuf-h-3.5 wpuf-text-blue-700 dark:wpuf-text-blue-500\" aria-hidden=\"true\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 16 12\">
                            <path stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M1 5.917 5.724 10.5 15 1.5\"/>
                        </svg>
                    </span></span>",
                    esc_html( __( 'Click to copy shortcode', 'wp-user-frontend' ) ),
                    '[' . esc_attr( $shortcodes[0]['name'] ) . ' id="' . esc_attr( $form_id ) . '"]'
                );
                ?>
        <!--                    <div class="wpuf-flex wpuf-rounded-md wpuf-shadow-sm">
                    <div class="wpuf-relative wpuf-flex focus-within:wpuf-z-10 wpuf-border wpuf-border-slate-200 wpuf-rounded-s-lg">
                        <input disabled type="text" name="shortcode" id="shortcode" class="wpuf-block !wpuf-rounded-none !wpuf-rounded-l-md !wpuf-border-0 wpuf-py-1.5 !wpuf-pl-10 wpuf-text-gray-900 placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 disabled:wpuf-cursor-pointer" placeholder="<?php // echo esc_attr( $shortcode ); ?>">
                    </div>
                    <button type="button" class="wpuf-relative wpuf-border wpuf-border-slate-200 wpuf-rounded-e-lg wpuf&#45;&#45;ml-px wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 wpuf-rounded-r-md wpuf-pr-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 hover:wpuf-bg-gray-50">
                    <span id="default-icon" class="wpuf-ml-2">
                        <svg class="wpuf-w-3.5 wpuf-h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                            <path
                                d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                        </svg>
                    </span>
                    <span id="success-icon" class="wpuf-hidden wpuf-ml-2 wpuf-inline-flex wpuf-items-center">
                        <svg class="wpuf-w-3.5 wpuf-h-3.5 wpuf-text-blue-700 dark:wpuf-text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                        </svg>
                    </span>
                    </button>
                </div>-->
                <?php
            }
            ?>
            <a :href="'<?php echo get_wpuf_preview_page(); ?>?wpuf_preview=1&form_id=' + post.ID" target="_blank" class="wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50"><?php esc_html_e( 'Preview', 'wp-user-frontend' ); ?></a>

            <button
                @click="save_form_builder"
                type="button"
                :disabled="is_form_saving"
                :class="is_form_saving ? 'wpuf-cursor-wait' : 'wpuf-cursor-pointer'"
                class="wpuf-rounded-full wpuf-bg-indigo-600 wpuf-px-3.5 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"><?php esc_html_e( 'Save Form', 'wp-user-frontend' ); ?></button>

            <button v-else type="button" class="button button-primary button-ajax-working" disabled>
                <span class="loader"></span> <?php esc_html_e( 'Saving Form Data', 'wp-user-frontend' ); ?>
            </button>
        </div>
</form>
