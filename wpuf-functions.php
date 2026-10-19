<?php

use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Encryption_Helper;
use WeDevs\Wpuf\Free\Pro_Prompt;
use WeDevs\Wpuf\Frontend\Payment;

// Include modules functions
require_once WPUF_INCLUDES . '/functions/modules.php';
// Single React admin app helpers
require_once WPUF_INCLUDES . '/functions/admin-app.php';

// Global functions by domain (every function keeps its name; loaded here, in this order).
require_once WPUF_INCLUDES . '/functions/helpers.php';
require_once WPUF_INCLUDES . '/functions/settings.php';
require_once WPUF_INCLUDES . '/functions/posts.php';
require_once WPUF_INCLUDES . '/functions/users.php';
require_once WPUF_INCLUDES . '/functions/forms.php';
require_once WPUF_INCLUDES . '/functions/shortcodes.php';
require_once WPUF_INCLUDES . '/functions/payments.php';
require_once WPUF_INCLUDES . '/functions/admin.php';

add_action( 'init', 'wpuf_buffer_start' );

add_filter( 'media_upload_tabs', 'wpuf_unset_media_tab' );

add_filter( 'get_edit_post_link', 'wpuf_override_admin_edit_link', 10, 2 );

/**
 * Create HTML dropdown list of Categories.
 *
 * @since 2.1.0
 *
 * @uses Walker
 */
// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- legacy file: these walkers have always lived alongside the helper functions; moving them would change the include contract.
class WPUF_Walker_Category_Multi extends Walker {

    /**
     * @see Walker::$tree_type
     *
     * @var string
     */
    public $tree_type = 'category';

    /**
     * @see Walker::$db_fields
     *
     * @var array
     */
    public $db_fields = [
        'parent' => 'parent',
        'id' => 'term_id',
    ];

    /**
     * @see Walker::start_el()
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category category data object
     * @param int    $depth    Depth of category. Used for padding.
     * @param array  $args     uses 'selected' and 'show_count' keys, if they exist
     */
    public function start_el( &$output, $category, $depth = 0, $args = [], $id = 0 ) {
        $pad = str_repeat( '&nbsp;', $depth * 3 );

        $cat_name = apply_filters( 'list_cats', $category->name, $category );
        $output .= "\t<option class=\"level-$depth\" value=\"" . $category->term_id . '"';

        if ( in_array( $category->term_id, $args['selected'], true ) ) {
            $output .= ' selected="selected"';
        }

        $output .= '>';
        $output .= $pad . $cat_name;

        if ( $args['show_count'] ) {
            $output .= '&nbsp;&nbsp;(' . $category->count . ')';
        }

        $output .= "</option>\n";
    }
}

/**
 * Category checklist walker
 *
 * @since 0.8
 */
// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound -- legacy file: see the note on WPUF_Walker_Category_Multi above.
class WPUF_Walker_Category_Checklist extends Walker {
    public $tree_type = 'category';

    public $db_fields = [
        'parent' => 'parent',
        'id' => 'term_id',
    ]; //TODO: decouple this

    public function start_lvl( &$output, $depth = 0, $args = [] ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent<ul class='children'>\n";
    }

    public function end_lvl( &$output, $depth = 0, $args = [] ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "$indent</ul>\n";
    }

    public function start_el( &$output, $category, $depth = 0, $args = [], $current_object_id = 0 ) {
        $taxonomy = $args['taxonomy'];

        $required = '';

        if ( ! empty( $args['required'] ) && 'yes' === $args['required'] ) {
            $required = ' data-required="yes" ';
        }

        if ( empty( $taxonomy ) ) {
            $taxonomy = 'category';
        }

        if ( 'category' === $taxonomy ) {
            $name = 'category';
        } else {
            $name = $taxonomy;
        }

        if ( 'yes' === $args['show_inline'] ) {
            $inline_class = 'wpuf-checkbox-inline';
        } else {
            $inline_class = '';
        }

        $class = isset( $args['class'] ) ? $args['class'] : '';
        $category_name = esc_html( apply_filters( 'the_category', $category->name ) );

        $output .= sprintf(
            '<li class="%s" id="%s-%s" data-label="%s"><label class="selectit"><input class="%s" value="%s" type="checkbox" data-type="checkbox" name="%s[]" id="in-%s-%s" %s %s %s /> %s</label>',
            esc_attr( $inline_class ), esc_attr( $taxonomy ), esc_attr( $category->term_id ), esc_attr( $args['label'] ), esc_attr( $class ), esc_attr( $category->term_id ), esc_attr( $name ), esc_attr( $taxonomy ), esc_attr( $category->term_id ),
            checked( in_array( $category->term_id, $args['selected_cats'], true ), true, false ),
            disabled( empty( $args['disabled'] ), false, false ), $required, esc_html( $category_name )
        );
    }

    public function end_el( &$output, $category, $depth = 0, $args = [] ) {
        $output .= "</li>\n";
    }
}

add_filter( 'wpuf_addpost_notice', 'wpuf_addpost_notice' );

add_filter( 'get_avatar', 'wpuf_get_avatar', 99, 6 );

add_filter( 'get_avatar_data', 'wpuf_custom_avatar_data', 10, 2 );

add_filter( 'the_content', 'wpuf_show_custom_fields' );

/**
 * Include a template file
 *
 * Looks up first on the theme directory, if not found
 * lods from pro plugin folder
 *
 * @since 3.1.11
 * @since 3.5.27_PRO function moved to pro
 *
 * @param string $file file name or path to file
 */
/*function wpuf_load_pro_template( $file, $args = [] ) {
    //phpcs:ignore
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }

    if ( wpuf()->is_pro() ) {
        $child_theme_dir    = get_stylesheet_directory() . '/wpuf/';
        $parent_theme_dir   = get_template_directory() . '/wpuf/';
        $wpuf_pro_dir       = WPUF_PRO_INCLUDES . '/templates/';

        if ( file_exists( $child_theme_dir . $file ) ) {
            include $child_theme_dir . $file;
        } elseif ( file_exists( $parent_theme_dir . $file ) ) {
            include $parent_theme_dir . $file;
        } else {
            include $wpuf_pro_dir . $file;
        }
    }
}*/

/*
 * Generates a random integer for a WPUF form field id (the builder does the same in JS)
 *
 * @since WPUF
 *
 * @return int
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */

/*
 * Polyfill of array_column function
 *
 * @since 2.4.3
 */

add_filter( 'display_post_states', 'wpuf_admin_page_states', 10, 2 );

add_action( 'wpuf_before_form_render', 'wpuf_show_form_schedule_message' );

add_action( 'wpuf_before_form_render', 'wpuf_show_form_limit_message' );

/*
 * Editor toolbar primary button list
 *
 * @param string $type
 *
 * @return array
 */

// @todo: move this to frontend class
add_filter( 'the_content', 'wpuf_modify_shortcodes' );

add_filter( 'wp_nav_menu_objects', 'wpuf_filter_logout_menu_items', 10, 1 );

add_filter( 'render_block_core/navigation-link', 'wpuf_refresh_logout_navigation_link', 10, 2 );

add_action( 'wp_head', 'wpuf_logout_visibility_css', 100 );
