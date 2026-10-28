<?php
/**
 * Subscription packs admin
 *
 * @package WP_User_Frontend
 */

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields;
use WeDevs\Wpuf\Admin\Subscriptions\Pack_Screen;
use WeDevs\Wpuf\Admin\Subscriptions\User_Profile;

/**
 * Manage Subscription packs: the hooks, delegating to the pack editor schema
 * (`Subscriptions\Pack_Fields`), the packs admin screen
 * (`Subscriptions\Pack_Screen`) and the user profile section
 * (`Subscriptions\User_Profile`). Every public method and hook callable
 * stays on this class.
 *
 * @since WPUF_SINCE Split by concern; the hooks and public methods are unchanged.
 */
class Admin_Subscription {

    /**
     * @var Pack_Fields|null
     */
    private $pack_fields = null;

    /**
     * @var Pack_Screen|null
     */
    private $pack_screen = null;

    /**
     * @var User_Profile|null
     */
    private $user_profile = null;

    /**
     * The constructor
     */
    public function __construct() {
        add_filter( 'wpuf_subscription_additional_fields', [ $this, 'third_party_cpt_options' ] );

        add_action( 'show_user_profile', [ $this, 'profile_subscription_details' ], 30 );
        add_action( 'edit_user_profile', [ $this, 'profile_subscription_details' ], 30 );
        add_action( 'personal_options_update', [ $this, 'profile_subscription_update' ] );
        add_action( 'edit_user_profile_update', [ $this, 'profile_subscription_update' ] );

        add_action( 'wpuf_load_subscription_page', [ $this, 'remove_notices' ] );
        add_action( 'wpuf_load_subscription_page', [ $this, 'enqueue_admin_scripts' ] );

        add_action( 'admin_init', [ $this, 'set_default_sort_order_for_existing_subscriptions' ] );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::get_sections()
     *
     * @since WPUF_SINCE
     */
    public function get_sections() {
        return $this->pack_fields()->get_sections();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::get_sub_sections()
     *
     * @since WPUF_SINCE
     */
    public function get_sub_sections() {
        return $this->pack_fields()->get_sub_sections();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::get_fields()
     *
     * @since WPUF_SINCE
     */
    public function get_fields() {
        return $this->pack_fields()->get_fields();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::get_dependent_fields()
     *
     * @since WPUF_SINCE
     */
    public function get_dependent_fields() {
        return $this->pack_fields()->get_dependent_fields();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::get_post_types()
     *
     * @since WPUF_SINCE
     */
    public function get_post_types( $post_types = null ) {
        return $this->pack_fields()->get_post_types( $post_types );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields::third_party_cpt_options()
     *
     * @since WPUF_SINCE
     */
    public function third_party_cpt_options( $additional_options ) {
        return $this->pack_fields()->third_party_cpt_options( $additional_options );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Screen::enqueue_admin_scripts()
     *
     * @since WPUF_SINCE
     */
    public function enqueue_admin_scripts() {
        return $this->pack_screen()->enqueue_admin_scripts();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Screen::remove_notices()
     *
     * @since WPUF_SINCE
     */
    public function remove_notices() {
        return $this->pack_screen()->remove_notices();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Screen::set_default_sort_order_for_existing_subscriptions()
     *
     * @since WPUF_SINCE
     */
    public function set_default_sort_order_for_existing_subscriptions() {
        return $this->pack_screen()->set_default_sort_order_for_existing_subscriptions();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\User_Profile::profile_subscription_details()
     *
     * @since WPUF_SINCE
     */
    public function profile_subscription_details( $profileuser ) {
        return $this->user_profile()->profile_subscription_details( $profileuser );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\User_Profile::profile_subscription_update()
     *
     * @since WPUF_SINCE
     */
    public function profile_subscription_update( $user_id ) {
        return $this->user_profile()->profile_subscription_update( $user_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\User_Profile::packdropdown_without_recurring()
     *
     * @since WPUF_SINCE
     */
    public function packdropdown_without_recurring( $packs, $selected = '' ) {
        return $this->user_profile()->packdropdown_without_recurring( $packs, $selected );
    }

    /**
     * Remove the pack of a user.
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User
     * @param int $pack_id Pack the user had
     *
     * @return bool
     */
    public function remove_user_pack( $user_id, $pack_id = 0 ) {
        return User_Profile::remove_user_pack( $user_id, $pack_id );
    }

    /**
     * The pack fields group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return Pack_Fields
     */
    private function pack_fields() {
        if ( ! $this->pack_fields ) {
            $this->pack_fields = new Pack_Fields( $this );
        }

        return $this->pack_fields;
    }

    /**
     * The pack screen group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return Pack_Screen
     */
    private function pack_screen() {
        if ( ! $this->pack_screen ) {
            $this->pack_screen = new Pack_Screen( $this );
        }

        return $this->pack_screen;
    }

    /**
     * The user profile group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return User_Profile
     */
    private function user_profile() {
        if ( ! $this->user_profile ) {
            $this->user_profile = new User_Profile( $this );
        }

        return $this->user_profile;
    }
}
