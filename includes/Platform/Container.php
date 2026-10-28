<?php
/**
 * Platform service container
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

/**
 * Small service container: shared or per-call factories, resolved lazily, and
 * tags to collect services by role (e.g. every `Contracts\Hookable`).
 *
 * In-house by design (D2): a bundled third-party container would need vendor
 * scoping on every release.
 *
 * @since WPUF_SINCE
 */
class Container {

    /**
     * Factories by id.
     *
     * @var callable[]
     */
    private $factories = [];

    /**
     * Whether an id resolves to one shared instance.
     *
     * @var bool[]
     */
    private $shared = [];

    /**
     * Resolved shared instances by id.
     *
     * @var array
     */
    private $instances = [];

    /**
     * Ids by tag, in the order they were tagged.
     *
     * @var array
     */
    private $tags = [];

    /**
     * Register a shared service: the factory runs once, on first get().
     *
     * @since WPUF_SINCE
     *
     * @param string   $id      Service id (usually the class name)
     * @param callable $factory Receives the container, returns the service
     *
     * @return void
     */
    public function share( $id, callable $factory ) {
        $this->factories[ $id ] = $factory;
        $this->shared[ $id ]    = true;
        unset( $this->instances[ $id ] );
    }

    /**
     * Register a factory service: every get() returns a new instance.
     *
     * @since WPUF_SINCE
     *
     * @param string   $id      Service id
     * @param callable $factory Receives the container (and get() arguments)
     *
     * @return void
     */
    public function bind( $id, callable $factory ) {
        $this->factories[ $id ] = $factory;
        $this->shared[ $id ]    = false;
        unset( $this->instances[ $id ] );
    }

    /**
     * Resolve a service.
     *
     * @since WPUF_SINCE
     *
     * @param string $id      Service id
     * @param mixed  ...$args Extra arguments for a factory (bind) service
     *
     * @throws NotFoundException When the id is not registered.
     *
     * @return mixed
     */
    public function get( $id, ...$args ) {
        if ( ! $this->has( $id ) ) {
            throw new NotFoundException( sprintf( 'WPUF platform service "%s" is not registered.', esc_html( $id ) ) );
        }

        if ( ! $this->shared[ $id ] ) {
            return call_user_func( $this->factories[ $id ], $this, ...$args );
        }

        if ( ! array_key_exists( $id, $this->instances ) ) {
            $this->instances[ $id ] = call_user_func( $this->factories[ $id ], $this );
        }

        return $this->instances[ $id ];
    }

    /**
     * Drop the built instance of a shared service; the next get() builds it again.
     * (Tests: the WordPress test framework restores the hooks between tests, so an
     * instance built in an earlier test carries none.)
     *
     * @since WPUF_SINCE
     *
     * @param string $id Service id
     *
     * @return void
     */
    public function forget( $id ) {
        unset( $this->instances[ $id ] );
    }

    /**
     * Whether a service id is registered.
     *
     * @since WPUF_SINCE
     *
     * @param string $id Service id
     *
     * @return bool
     */
    public function has( $id ) {
        return is_string( $id ) && isset( $this->factories[ $id ] );
    }

    /**
     * Whether a registered id resolves to one shared instance.
     *
     * @since WPUF_SINCE
     *
     * @param string $id Service id
     *
     * @return bool
     */
    public function is_shared( $id ) {
        return $this->has( $id ) && $this->shared[ $id ];
    }

    /**
     * Tag a registered service.
     *
     * @since WPUF_SINCE
     *
     * @param string $id  Service id
     * @param string $tag Tag (usually an interface name)
     *
     * @return void
     */
    public function add_tag( $id, $tag ) {
        if ( ! isset( $this->tags[ $tag ] ) ) {
            $this->tags[ $tag ] = [];
        }

        if ( ! in_array( $id, $this->tags[ $tag ], true ) ) {
            $this->tags[ $tag ][] = $id;
        }
    }

    /**
     * Ids carrying a tag, in tagging order.
     *
     * @since WPUF_SINCE
     *
     * @param string $tag Tag
     *
     * @return string[]
     */
    public function tagged_ids( $tag ) {
        return isset( $this->tags[ $tag ] ) ? $this->tags[ $tag ] : [];
    }

    /**
     * Resolve every service carrying a tag, in tagging order.
     *
     * @since WPUF_SINCE
     *
     * @param string $tag Tag
     *
     * @return array
     */
    public function tagged( $tag ) {
        return array_map( [ $this, 'get' ], $this->tagged_ids( $tag ) );
    }
}
