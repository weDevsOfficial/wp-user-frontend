<?php

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Admin\Forms\Form;
use WP_Query;

/**
 * The preview class
 *
 * This is a clever technique to preview a form without having to set any placeholder page by
 * setting the page templates to singular pages (page.php, single.php and index.php). Setting
 * the posts per page to 1 and changing the page title and contents dynamiclly allows us to
 * preview the form without any placeholder page.
 *
 * This technique requires the theme to have at least the above mentioned templates in the theme
 * and requires to have the WordPress Loop, otherwise we wouldn't be able to set the title and
 * the page content dynamically.
 *
 * The technique is borrowed from Ninja Forms (thanks guys!)
 */
class Form_Preview {

    /**
     * Form id
     *
     * @var int
     */
    private $form_id;

    /**
     * is_preview
     *
     * @var string
     */
    private $is_preview = true;

    public function __construct() {
        if ( ! isset( $_GET['wpuf_preview'] ) && empty( $_GET['wpuf'] ) ) {
            return;
        }

        // Security: Check user has proper capabilities before allowing preview
        if ( ! is_user_logged_in() || ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have permission to preview this form.', 'wp-user-frontend' ), 403 );
        }

        // Security: Validate and sanitize form_id parameter
        $this->form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
        add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
        // add_filter( 'template_include', [ $this, 'template_include' ] );
        add_filter( 'the_title', [ $this, 'the_title' ] );
        add_filter( 'the_content', [ $this, 'the_content' ] );
        add_filter( 'get_the_excerpt', [ $this, 'the_content' ] );
        add_filter( 'post_thumbnail_html', '__return_empty_string' );
    }

    /**
     * Set the page title
     *
     * @param string $title
     *
     * @return string
     */
    public function the_title( $title ) {
        if ( ! in_the_loop() ) {
            return $title;
        }

        $form = new Form( $this->form_id );
        if ( ! $form ) {
            return $title;
        }
        $preview = $this->is_preview ? 'Preview' : '';

        return $form->get_title() . ' ' . $preview;
    }

    /**
     * Set the content of the page
     *
     * @param string $content
     *
     * @return string
     */
    public function the_content( $content ) {
        // Security: Double-check admin capabilities
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            return __( 'You do not have permission to preview this form.', 'wp-user-frontend' );
        }

        // Security: Validate form_id is a valid integer to prevent injection
        $form_id = absint( $this->form_id );
        if ( $form_id === 0 ) {
            return __( 'Invalid form ID.', 'wp-user-frontend' );
        }

        return do_shortcode( sprintf( '[wpuf_form id="%d"]', $form_id ) );
    }

    /**
     * Set the posts to one
     *
     * @param WP_Query $query
     *
     * @return void
     */
    public function pre_get_posts( $query ) {
        if ( $query->is_main_query() ) {
            $query->set( 'posts_per_page', 1 );
        }
    }

    /**
     * Limit the page templates to singular pages only
     *
     * @return string
     */
    public function template_include() {
        return locate_template( [ 'page.php', 'single.php', 'index.php' ] );
    }
}
