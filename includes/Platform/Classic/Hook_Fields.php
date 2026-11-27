<?php
/**
 * Fields other plugins print through classic PHP form hooks
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Classic;

use WP_Post;

/**
 * The classic edit screens (coupon, subscription pack) fired actions inside
 * their forms where other plugins printed fields, and those plugins saved
 * the fields from `$_POST` on save. The React screens keep that working:
 * render() fires the hooks the classic way (global `$post` set) and returns
 * the markup, sanitized for form fields; the app shows it and sends the
 * inputs back; with_post_data() exposes them in `$_POST` while the store
 * saves, so `save_post` and the screen's save hooks see them as before.
 *
 * @since WPUF_SINCE
 */
class Hook_Fields {

    /**
     * Fire classic form hooks and return what they printed.
     *
     * @since WPUF_SINCE
     *
     * @param WP_Post  $edited Post the classic screen edited (global `$post` while the hooks run)
     * @param callable $fire   Fires the hooks
     *
     * @return string Markup, sanitized with wp_kses (form tags allowed); '' when none
     */
    public function render( WP_Post $edited, callable $fire ) {
        global $post;

        $previous = $post;
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- the classic screens' listeners read global $post; restored below.
        $post = $edited;

        ob_start();

        try {
            $fire();
        } finally {
            $html = trim( (string) ob_get_clean() );
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restore.
            $post = $previous;
        }

        return '' === $html ? '' : wp_kses( $html, $this->tags() );
    }

    /**
     * Clean the values the app sends back for those fields (scalars and
     * lists, HTML limited as in post content).
     *
     * @since WPUF_SINCE
     *
     * @param mixed $values Values
     *
     * @return array
     */
    public function clean( $values ) {
        $clean = [];

        foreach ( (array) $values as $key => $value ) {
            $key = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $key );

            if ( '' === $key ) {
                continue;
            }

            if ( is_array( $value ) ) {
                $clean[ $key ] = $this->clean( $value );
            } elseif ( is_scalar( $value ) ) {
                $clean[ $key ] = wp_kses_post( (string) $value );
            }
        }

        return $clean;
    }

    /**
     * Run a save with the fields' values in `$_POST`, as the classic form
     * posted them, and put `$_POST` back afterwards.
     *
     * @since WPUF_SINCE
     *
     * @param array    $values Values (clean())
     * @param callable $save   The save
     *
     * @return mixed What the save returns
     */
    public function with_post_data( array $values, callable $save ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- the REST request is authenticated; $_POST is restored below.
        $backup = $_POST;
        $_POST  = array_merge( $_POST, wp_slash( $values ) );

        try {
            return $save();
        } finally {
            $_POST = $backup;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Tags and attributes the fields may use.
     *
     * @return array
     */
    private function tags() {
        $common = [
            'id'    => true,
            'class' => true,
            'style' => true,
            'for'   => true,
            'name'  => true,
            'title' => true,
        ];
        $input  = array_merge(
            $common,
            [
                'type'        => true,
                'value'       => true,
                'checked'     => true,
                'selected'    => true,
                'disabled'    => true,
                'placeholder' => true,
                'min'         => true,
                'max'         => true,
                'step'        => true,
                'multiple'    => true,
                'rows'        => true,
                'cols'        => true,
                'size'        => true,
            ]
        );

        $tags = wp_kses_allowed_html( 'post' );

        foreach ( [ 'table', 'tbody', 'thead', 'tr', 'td', 'th', 'label', 'p', 'span', 'div', 'small', 'strong', 'code', 'br', 'a', 'section', 'h2', 'h3', 'h4' ] as $tag ) {
            $tags[ $tag ] = array_merge(
                isset( $tags[ $tag ] ) ? $tags[ $tag ] : [],
                $common,
                [
                    'colspan' => true,
                    'scope'   => true,
                    'valign'  => true,
                    'href'    => true,
                    'target'  => true,
                ]
            );
        }

        foreach ( [ 'input', 'select', 'option', 'textarea', 'optgroup' ] as $tag ) {
            $tags[ $tag ] = $input + [ 'label' => true ];
        }

        return $tags;
    }
}
