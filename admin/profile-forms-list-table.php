<?php
/**
 * Profile Forms list table class
 *
 * @since 2.5
 */
class WPUF_Admin_Profile_Forms_List_Table extends WP_List_Table {

    /**
     * Class constructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        global $status, $page, $page_status;

        parent::__construct( array(
            'singular' => 'profile-form',
            'plural'   => 'profile-forms',
            'ajax'     => false
        ) );
    }

    /**
     * Top filters like All, Published, Trash etc
     *
     * @since 2.5
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=wpuf-profile-forms' );

        $post_statuses  = apply_filters( 'wpuf_profile_forms_list_table_post_statuses', array(
            'all'       => __( 'All', 'wpuf' ),
            'publish'   => __( 'Published', 'wpuf' ),
            'trash'     => __( 'Trash', 'wpuf' )
        ) );

        $current_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';

        $post_counts = (array) wp_count_posts( 'wpuf_profile' );

        if ( isset( $post_counts['auto-draft'] ) ) {
            unset( $post_counts['auto-draft'] );
        }

        foreach ( $post_statuses as $status => $status_title ) {
            $link = ( 'all' === $status ) ? $base_link : admin_url( 'admin.php?page=wpuf-profile-forms&post_status=' . $status );

            if ( $status === $current_status ) {
                $class = 'current';
            } else {
                $class = '';
            }

            switch ( $status ) {
                case 'all':
                    $without_trash = $post_counts;
                    unset( $without_trash['trash'] );

                    $count = array_sum( $without_trash );
                    break;

                default:
                    $count = $post_counts[ $status ];
                    break;
            }

            $status_links[ $status ] = sprintf(
                '<a class="%s" href="%s">%s <span class="count">(%s)</span></a>',
                $class, $link, $status_title, $count
            );
        }

        return apply_filters( 'wpuf_profile_forms_list_table_get_views', $status_links, $post_statuses, $current_status );
    }

    /**
     * Message to show if no item found
     *
     * @since 2.5
     *
     * @return void
     */
    public function no_items() {
        _e( 'No form found.', 'wpuf' );
    }

    /**
     * Bulk actions dropdown
     *
     * @since 2.5
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [];

        if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
            $actions['trash'] = __( 'Move to Trash', 'wpuf' );
        }

        if ( isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] ) {
            $actions['restore'] = __( 'Restore', 'wpuf' );
            $actions['delete']  = __( 'Delete Permanently', 'wpuf' );
        }

        return apply_filters( 'wpuf_profile_forms_list_table_get_bulk_actions', $actions );
    }

    /**
     * List table search box
     *
     * @since 2.5
     *
     * @param string $text
     * @param string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_GET['s'] ) && ! $this->has_items() ) {
            return;
        }

        if ( ! empty( $_GET['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_GET['orderby'] ) . '" />';
        }

        if ( ! empty( $_GET['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_GET['order'] ) . '" />';
        }

        if ( ! empty( $_GET['post_status'] ) ) {
            echo '<input type="hidden" name="post_status" value="' . esc_attr( $_GET['post_status'] ) . '" />';
        }

        do_action( 'wpuf_profile_forms_list_table_search_box', $text, $input_id );

        $input_id = $input_id . '-search-input';

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', 'profile_form_search', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <?php
    }

    /**
     * Decide which action is currently performing
     *
     * @since 2.5
     *
     * @return string
     */
    public function current_action() {

        if ( isset( $_GET['profile_form_search'] ) ) {
            return 'profile_form_search';
        }

        return parent::current_action();
    }

    /**
     * Prepare table data
     *
     * @since 2.5
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = get_option( 'posts_per_page', 20 );
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;

        $args = array(
            'offset'         => $offset,
            'posts_per_page' => $per_page,
        );

        if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            $args['s'] = $_GET['s'];
        }

        if ( isset( $_GET['post_status'] ) && !empty( $_GET['post_status'] ) ) {
            $args['post_status'] = $_GET['post_status'];
        }

        if ( isset( $_GET['orderby'] ) && !empty( $_GET['orderby'] ) ) {
            $args['orderby'] = $_GET['orderby'];
        }

        if ( isset( $_GET['order'] ) && !empty( $_GET['order'] ) ) {
            $args['order'] = $_GET['order'];
        }

        $items  = $this->item_query( $args );

        $this->counts = count( $items['count'] );
        $this->items  = $items['forms'];

        $this->set_pagination_args( array(
            'total_items' => $items['count'],
            'per_page'    => $per_page
        ) );
    }

    /**
     * WP_Query for profile forms
     *
     * @since 2.5
     *
     * @param array $args
     *
     * @return array
     */
    public function item_query( $args ) {
        $defauls = array(
            'post_status' => 'any',
            'orderby'     => 'DESC',
            'order'       => 'ID',
        );

        $args = wp_parse_args( $args, $defauls );

        $args['post_type'] = 'wpuf_profile';

        $query = new WP_Query( $args );

        $forms = [];

        if ( $query->have_posts() ) {

            $i = 0;

            while ( $query->have_posts() ) {
                $query->the_post();

                $form = $query->posts[ $i ];

                $settings = get_post_meta( get_the_ID(), 'wpuf_form_settings', true );

                $forms[ $i ] = [
                    'ID'                 => $form->ID,
                    'post_title'         => $form->post_title,
                    'post_status'        => $form->post_status,
                    'settings_user_role' => $settings['role']
                ];

                $i++;
            }
        }

        $forms = apply_filters( 'wpuf_profile_forms_list_table_query_results', $forms, $query, $args );
        $count = $query->found_posts;

        wp_reset_postdata();

        return [
            'forms' => $forms,
            'count' => $count
        ];
    }

    /**
     * Get the column names
     *
     * @since 2.5
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'form_name' => __( 'Form Name', 'wpuf' ),
            'user_role' => __( 'User Role', 'wpuf' ),
            'shortcode' => __( 'Shortcode', 'wpuf' ),
        );

        return apply_filters( 'wpuf_profile_forms_list_table_cols', $columns );
    }

    /**
     * Get sortable columns
     *
     * @since 2.5
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'form_name' => array( 'form_name', false ),
        );

        return apply_filters( 'wpuf_profile_forms_list_table_sortable_columns', $sortable_columns );
    }

    /**
     * Column values
     *
     * @since 2.5
     *
     * @param array $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'post_type':
                return $item['settings_post_type'];

            case 'user_role':
                return ucwords( $item['settings_user_role'] );

            case 'shortcode':
                return '<code>[wpuf_profile type="registration" id="' . $item['ID'] . '"]</code><br />' . '<code>[wpuf_profile type="profile" id="' . $item['ID'] . '"]</code>';

            default:
                return apply_filter( 'wpuf_profile_forms_list_table_column_default', $item, $column_name );
        }
    }

    /**
     * Checkbox column value
     *
     * @since 2.5
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="post[]" value="%d" />', $item['ID'] );
    }

    /**
     * Form name column value
     *
     * @since 2.5
     *
     * @param array $item
     *
     * @return string
     */
    public function column_form_name( $item ) {
        $actions = array();

        $edit_url       = admin_url( 'admin.php?page=wpuf-profile-forms&action=edit&id=' . $item['ID'] );

        $wpnonce        = wp_create_nonce( 'bulk-profile-forms' );
        $admin_url      = admin_url( 'admin.php?page=wpuf-profile-forms&id=' . $item['ID'] . '&_wpnonce=' . $wpnonce );

        $duplicate_url  = $admin_url . '&action=duplicate';
        $trash_url      = $admin_url . '&action=trash';
        $restore_url    = $admin_url . '&action=restore';
        $delete_url     = $admin_url . '&action=delete';

        if ( ( !isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) && current_user_can( wpuf_admin_role() ) ) {
            $actions['edit']      = sprintf( '<a href="%s">%s</a>', $edit_url, __( 'Edit', 'wpuf' ) );
            $actions['trash']     = sprintf( '<a href="%s" class="submitdelete">%s</a>', $trash_url, __( 'Trash', 'wpuf' ) );
            $actions['duplicate'] = sprintf( '<a href="%s">%s</a>', $duplicate_url, __( 'Duplicate', 'wpuf' ) );

            $title = sprintf(
                '<a class="row-title" href="%1s" aria-label="%2s">%3s</a>',
                $edit_url,
                '"' . $item['post_title'] . '" (Edit)',
                $item['post_title']
            );
        }

        if ( ( isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] ) && current_user_can( wpuf_admin_role() ) ) {
            $actions['restore'] = sprintf( '<a href="%s">%s</a>', $restore_url, __( 'Restore', 'wpuf' ) );
            $actions['delete']  = sprintf( '<a href="%s" class="submitdelete">%s</a>', $delete_url, __( 'Delete Permanently', 'wpuf' ) );

            $title = sprintf(
                '<strong>%1s</strong>',
                $item['post_title']
            );
        }

        $draft_marker = ( 'draft' === $item['post_status'] ) ?
                            '<strong> — <span class="post-state">' . __( 'Draft', 'wpuf' ) . '</span></strong>' :
                            '';

        $form_name = sprintf( '%1s %2s %3s', $title, $draft_marker, $this->row_actions( $actions ) );

        return apply_filters( 'wpuf_profile_forms_list_table_column_form_name', $form_name, $item );
    }

}
