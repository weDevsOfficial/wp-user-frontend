<?php
/**
 * Onboarding: page lookups
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

/**
 * Pages of the site as the setup wizard sees them: which page holds a
 * shortcode, the page choices of a step, and keeping a chosen page's shortcode.
 *
 * @since WPUF_SINCE Moved out of Admin\Onboarding, which delegates to it.
 */
class Pages {

    /**
     * The first page already holding a shortcode
     *
     * Used so an unset page setting falls back to a page that exists rather
     * than offering to create another one.
     *
     * @since WPUF_SINCE
     *
     * @param string $tag
     *
     * @return int page id, 0 when none holds it
     */
    public function find_page_with_shortcode( $tag ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ]
        );

        foreach ( $pages as $page ) {
            if ( $this->content_has_shortcode( $page->post_content, $tag ) ) {
                return $page->ID;
            }
        }

        return 0;
    }

    /**
     * Pages for a wizard dropdown, flagging the ones already holding a shortcode
     *
     * @since WPUF_SINCE
     *
     * @param string $tag    shortcode tag to look for
     * @param string $marker  what to show in brackets on a page that has it
     *
     * @return array page id => label
     */
    public function get_pages_for_shortcode( $tag, $marker = '' ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        $list = [];

        foreach ( $pages as $page ) {
            $label = $page->post_title ? $page->post_title : __( '(no title)', 'wp-user-frontend' );

            if ( $marker && $this->content_has_shortcode( $page->post_content, $tag ) ) {
                $label .= ' (' . $marker . ')';
            }

            $list[ $page->ID ] = $label;
        }

        return $list;
    }

    /**
     * Whether content holds a shortcode
     *
     * The has_shortcode() helper only matches tags registered at the time of the
     * call, and the WPUF shortcodes are not registered on the wizard screen, so
     * the tag is matched directly instead.
     *
     * @since WPUF_SINCE
     *
     * @param string $content
     * @param string $tag
     *
     * @return bool
     */
    public function content_has_shortcode( $content, $tag ) {
        if ( false === strpos( $content, '[' ) ) {
            return false;
        }

        return (bool) preg_match( '/\[' . preg_quote( $tag, '/' ) . '[\s\]\/]/', $content );
    }

    /**
     * Whether a wizard page choice is an existing page (not trashed)
     *
     * @since WPUF_SINCE
     *
     * @param int|string $page_id Posted page id.
     *
     * @return bool
     */
    public function is_page( $page_id ) {
        $page_id = absint( $page_id );

        return $page_id && 'page' === get_post_type( $page_id ) && 'trash' !== get_post_status( $page_id );
    }

    /**
     * Put a WPUF shortcode on a page that does not have it yet
     *
     * Picking an existing page from the wizard is only useful if the page
     * actually renders the form, so the shortcode is appended when missing.
     *
     * @since WPUF_SINCE
     *
     * @param int    $page_id
     * @param string $tag       shortcode tag to look for
     * @param string $shortcode full shortcode to append
     *
     * @return bool whether the page was changed
     */
    public function ensure_page_shortcode( $page_id, $tag, $shortcode ) {
        $page = get_post( $page_id );

        if ( ! $page || 'page' !== $page->post_type ) {
            return false;
        }

        if ( $this->content_has_shortcode( $page->post_content, $tag ) ) {
            return false;
        }

        $content = trim( $page->post_content );
        $content = $content ? $content . "\n\n" . $shortcode : $shortcode;

        wp_update_post(
            [
                'ID'           => $page_id,
                'post_content' => $content,
            ]
        );

        return true;
    }

    /**
     * Whether a page holding the given shortcode already exists
     *
     * @since WPUF_SINCE
     *
     * @param string $needle
     *
     * @return bool
     */
    public function page_exists( $needle ) {
        $pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => [ 'publish', 'draft' ],
                'posts_per_page' => -1,
                'fields'         => 'ids',
                's'              => $needle,
            ]
        );

        return ! empty( $pages );
    }

    /**
     * A page list as select options, "create" first
     *
     * @param string $create_label
     * @param array  $pages        page id => label
     *
     * @return array[]
     */
    public function page_options( $create_label, $pages ) {
        $options = [
            [
                'value' => 'create',
                'label' => $create_label,
            ],
        ];

        foreach ( $pages as $page_id => $label ) {
            $options[] = [
                'value' => (string) $page_id,
                'label' => wp_strip_all_tags( $label ),
            ];
        }

        return $options;
    }

    /**
     * The stored page of a select, or "create" when it is not offered
     *
     * @param int     $page_id
     * @param array[] $options
     *
     * @return string
     */
    public function page_choice( $page_id, $options ) {
        $value = (string) absint( $page_id );

        return in_array( $value, wp_list_pluck( $options, 'value' ), true ) ? $value : 'create';
    }
}
