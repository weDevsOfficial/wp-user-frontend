<div class="wrap">
    <h2 class="with-headway-icon">
        <span class="title-area">
            <?php
                esc_html_e( 'Post Forms', 'wp-user-frontend' );

                if ( current_user_can( wpuf_admin_role() ) ) {
                    ?>
                    <a href="<?php echo esc_url( $add_new_page_url ); ?>" id="new-wpuf-post-form" class="page-title-action add-form"><?php esc_html_e( 'Add Form', 'wp-user-frontend' ); ?></a>
                    <?php
                }
            ?>
        </span>
        <span class="flex-end">
            <span class="headway-icon"></span>
            <a class="canny-link" target="_blank" href="<?php echo esc_url( 'https://wpuf.canny.io/ideas' ); ?>">💡 <?php esc_html_e( 'Submit Ideas', 'wp-user-frontend' ); ?></a>
        </span>
    </h2>

    <div class="list-table-wrap wpuf-post-form-wrap">
        <div class="list-table-inner wpuf-post-form-wrap-inner">
            <form method="get">
                <input type="hidden" name="page" value="wpuf-post-forms">
                <?php
                    $wpuf_post_form = new WeDevs\Wpuf\Admin\Forms\Post\Templates\List_Table_Admin_Post_Forms();
                    $wpuf_post_form->prepare_items();
                    $wpuf_post_form->search_box( __( 'Search Forms', 'wp-user-frontend' ), 'wpuf-post-form-search' );

                    if ( current_user_can( wpuf_admin_role() ) ) {
                        $wpuf_post_form->views();
                    }

                    $wpuf_post_form->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

    <div class="wpuf-footer-help">
        <span class="wpuf-footer-help-content">
            <span class="dashicons dashicons-editor-help"></span>
            <?php printf( wp_kses_post( __( 'Learn more about <a href="%s" target="_blank">Frontend Posting</a>', 'wp-user-frontend' ) ), 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/?utm_source=wpuf-footer-help&utm_medium=text-link&utm_campaign=learn-more-frontend-posting' ); ?>
        </span>
    </div>
</div>
