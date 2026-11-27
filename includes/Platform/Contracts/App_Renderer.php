<?php
/**
 * App renderer contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

/**
 * How a frontend part (post form, registration, profile, account, login)
 * is printed: as a React mount or by its classic templates. Pro and add-ons
 * resolve it from the container by this interface
 * (`wpuf()->platform()->get( App_Renderer::class )`).
 *
 * @since WPUF_SINCE
 */
interface App_Renderer {

    /**
     * Whether the part renders as a React mount on this request.
     *
     * @since WPUF_SINCE
     *
     * @param string $part Part (forms, registration, profile, account, login)
     *
     * @return bool
     */
    public function mounts( $part );

    /**
     * Run a mount build: hooks that print during it know a mount is built.
     *
     * @since WPUF_SINCE
     *
     * @param string   $part  Part
     * @param callable $build Produces the markup
     *
     * @return mixed What $build returned
     */
    public function build( $part, callable $build );

    /**
     * Whether a mount build is running.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function building();

    /**
     * The mount markup of a form schema, its assets enqueued.
     *
     * @since WPUF_SINCE
     *
     * @param array  $schema The form schema (Form_Schema_Provider::build())
     * @param string $part   Part
     *
     * @return string
     */
    public function form_markup( array $schema, $part );

    /**
     * Enqueue a frontend app's script and style.
     *
     * @since WPUF_SINCE
     *
     * @param string $app App (forms, account, login)
     *
     * @return void
     */
    public function enqueue( $app );
}
