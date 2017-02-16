<div class="wrap">
    <h2>
        <?php
            _e( 'Profile Forms', 'wpuf' );

            if ( current_user_can( wpuf_admin_role() ) ):
            ?>
                <a href="<?php echo $add_new_page_url; ?>" id="new-wpuf-profile-form" class="add-new-h2"><?php _e( 'Add Form', 'wpuf' ); ?></a>
            <?php
            endif;
        ?>
    </h2>


    <div class="list-table-wrap wpuf-profile-form-wrap">
        <div class="list-table-inner wpuf-profile-form-wrap-inner">

            <form method="get">
                <input type="hidden" name="page" value="wpuf-profile-forms">
                <?php
                    $wpuf_profile_form = new WPUF_Admin_Profile_Forms_List_Table();
                    $wpuf_profile_form->prepare_items();
                    $wpuf_profile_form->search_box( __( 'Search Forms', 'wpuf' ), 'wpuf-profile-form-search' );

                    if ( current_user_can( wpuf_admin_role() ) ) {
                        $wpuf_profile_form->views();
                    }

                    $wpuf_profile_form->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
