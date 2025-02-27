<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Lib\Gateway\Paypal;

/**
 * Manage Subscription packs
 */
class Admin_Subscription {
    /**
     * The constructor
     */
    public function __construct() {
        add_filter( 'manage_wpuf_subscription_posts_columns', [ $this, 'subscription_columns_head' ] );
        add_filter( 'post_updated_messages', [ $this, 'form_updated_message' ] );
        add_filter( 'wpuf_subscription_additional_fields', [ $this, 'third_party_cpt_options' ] );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'manage_wpuf_subscription_posts_custom_column', [ $this, 'subscription_columns_content' ], 10, 2 );

        // new subscription metabox hooks
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

        add_action( 'show_user_profile', [ $this, 'profile_subscription_details' ], 30 );
        add_action( 'edit_user_profile', [ $this, 'profile_subscription_details' ], 30 );
        add_action( 'personal_options_update', [ $this, 'profile_subscription_update' ] );
        add_action( 'edit_user_profile_update', [ $this, 'profile_subscription_update' ] );

        // display help link to docs
        add_action( 'admin_notices', [ $this, 'add_help_link' ] );

        // new subscription metabox hooks
        add_action( 'admin_print_styles-post-new.php', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_print_styles-post.php', [ $this, 'enqueue_scripts' ] );

        add_action( 'wpuf_load_subscription_page', [ $this, 'remove_notices' ] );
        add_action( 'wpuf_load_subscription_page', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'wpuf_load_subscription_page', [ $this, 'modify_admin_footer_text' ] );
    }

    /**
     * Add third party plugins (i.e.: WooCommerce, Elementor etc.) custom post type options
     *
     * @return array
     */
    public function third_party_cpt_options( $additional_options ) {
        $post_types = wpuf()->subscription->get_all_post_type();

        $ignore_list = [
            'post',
            'page',
            'user_request',
            'wp_navigation',
            'wp_template',
            'wp_template_part',
        ];

        foreach ( $post_types as $key => $name ) {
            $post_type_object = get_post_type_object( $key );

            if ( in_array( $key, $ignore_list, true ) ) {
                continue;
            }

            if ( $post_type_object ) {
                $additional_options['additional'][ $key ] = [
                    'id'            => $key,
                    'name'          => $key,
                    'db_key'        => 'additional_cpt_options',
                    'db_type'       => 'meta_serialized',
                    'serialize_key' => $key,
                    'type'          => 'input-number',
                    'label'         => sprintf(
                        // translators: %s: post type label
                        __( 'Number of %s', 'wp-user-frontend' ),
                        esc_html( $post_type_object->label )
                    ),
                    'tooltip' => sprintf(
                        // translators: %s: post type label
                        __(
                            'Set the maximum number of %s users can create within their subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        esc_html( $key )
                    ),
                    'default'       => '-1',
                ];
            }
        }

        return $additional_options;
    }

    /**
     * Enqueue scripts for subscription page
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'wpuf-admin-subscriptions' );
        wp_enqueue_script( 'wpuf-subscriptions' );
        wp_enqueue_style( 'wpuf-admin-subscriptions' );

        wp_localize_script(
            'wpuf-admin-subscriptions', 'wpufSubscriptions',
            [
                'version'         => WPUF_VERSION,
                'assetUrl'        => WPUF_ASSET_URI,
                'siteUrl'         => site_url(),
                'currencySymbol'  => wpuf_get_currency( 'symbol' ),
                'supportUrl'      => esc_url(
                    'https://wedevs.com/contact/?utm_source=wpuf-subscription'
                ),
                'isProActive'     => class_exists( 'WP_User_Frontend_Pro' ),
                'upgradeUrl'      => esc_url(
                    'https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpuf-subscription'
                ),
                'nonce'           => wp_create_nonce( 'wp_rest' ),
                'sections'        => $this->get_sections(),
                'subSections'     => $this->get_sub_sections(),
                'fields'          => $this->get_fields(),
                'dependentFields' => $this->get_dependent_fields(),
                'perPage'         => apply_filters( 'wpuf_subscription_per_page', 9 ),
            ]
        );
    }

    /**
     * Remove admin notices from this page
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function remove_notices() {
        add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
    }

    /**
     * Add settings metaboxes
     */
    public function add_meta_boxes() {
        add_meta_box( 'wpuf-metabox-subscription', __( 'Pack Description', 'wp-user-frontend' ), [ $this, 'pack_description_metabox' ], 'wpuf_subscription', 'normal', 'high' );
        add_meta_box( 'wpuf_subs_metabox', 'Subscription Options', [ $this, 'subs_meta_box' ], 'wpuf_subscription' );
    }

    /**
     * Custom post update message
     *
     * @param array $messages
     *
     * @return array
     */
    public function form_updated_message( $messages ) {
        $message = [
            0  => '',
            1  => __( 'Subscription pack updated.', 'wp-user-frontend' ),
            2  => __( 'Custom field updated.', 'wp-user-frontend' ),
            3  => __( 'Custom field deleted.', 'wp-user-frontend' ),
            4  => __( 'Subscription pack updated.', 'wp-user-frontend' ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( 'Subscription pack restored to revision from %s', 'wp-user-frontend' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => __( 'Subscription pack published.', 'wp-user-frontend' ),
            7  => __( 'Subscription pack saved.', 'wp-user-frontend' ),
            8  => __( 'Subscription pack submitted.', 'wp-user-frontend' ),
            9  => '',
            10 => __( 'Subscription pack draft updated.', 'wp-user-frontend' ),
        ];

        $messages['wpuf_subscription'] = $message;

        return $messages;
    }

    /**
     * Update user profile lock
     *
     * @param int $user_id
     */
    public function profile_subscription_update( $user_id ) {
        if ( ! is_admin() && ! current_user_can( 'edit_users' ) ) {
            return;
        }
        $nonce = isset( $_REQUEST['wpuf-subscription-nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf-subscription-nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'update-profile_' . $user_id ) ) {
            return;
        }

        if ( ! isset( $_POST['pack_id'] ) ) {
            return;
        }

        if ( isset( $_POST['wpuf_profile_mail_noti'] ) ) {
            $wpuf_profile_mail_noti = sanitize_text_field( wp_unslash( $_POST['wpuf_profile_mail_noti'] ) );
            update_user_meta( $user_id, '_pack_assign_notification', $wpuf_profile_mail_noti );
        }

        $pack_id   = isset( $_POST['pack_id'] ) ? intval( wp_unslash( $_POST['pack_id'] ) ) : '';
        $u_id   = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : '';
        $pack      = wpuf()->subscription->get_subscription( $pack_id );
        $user_pack = wpuf()->subscription->get_user_pack( $u_id );

        if ( isset( $user_pack['pack_id'] ) && $pack_id == $user_pack['pack_id'] ) {
            //updating number of posts

            if ( isset( $user_pack['posts'] ) ) {
                $p_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
                foreach ( $user_pack['posts'] as $post_type => $post_num ) {
                    $user_pack['posts'][ $post_type ] = $p_type;
                }
            }

            //post expiration enable or disable

            if ( isset( $_POST['is_post_expiration_enabled'] ) ) {
                $user_pack['_enable_post_expiration'] = sanitize_text_field( wp_unslash( $_POST['is_post_expiration_enabled'] ) );
            } else {
                unset( $user_pack['_enable_post_expiration'] );
            }

            //updating post time
            if ( isset( $_POST['post_expiration_settings'] ) ) {
                $post_expiration_settings = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_expiration_settings'] ) );

                $user_pack['_post_expiration_time'] = $post_expiration_settings['expiration_time_value'] . ' ' . $post_expiration_settings['expiration_time_type'];

                echo esc_html( $user_pack['_post_expiration_time'] );
            }

            if ( isset( $user_pack['recurring'] ) && wpuf_is_option_on( $user_pack['recurring'] ) ) {
                foreach ( $user_pack['posts'] as $type => $value ) {
                    $user_pack['posts'][ $type ] = isset( $_POST[ $type ] ) ? sanitize_text_field( wp_unslash( $_POST[ $type ] ) ) : 0;
                }
            } else {
                foreach ( $user_pack['posts'] as $type => $value ) {
                    $user_pack['posts'][ $type ] = isset( $_POST[ $type ] ) ? sanitize_text_field( wp_unslash( $_POST[ $type ] ) ) : 0;
                }
                $user_pack['expire'] = isset( $_POST['expire'] ) && 'Unlimited' !== $_POST['expire'] ? wpuf_date2mysql( sanitize_text_field( wp_unslash( $_POST['expire'] ) ) ) : $user_pack['expire'];
            }
            wpuf_get_user( $user_id )->subscription()->update_meta( $user_pack );
        } else {
            if ( $pack_id == '-1' ) {
                return;
            }

            $user_info      = get_userdata( $user_id );
            $cost           = $pack->meta_value['billing_amount'];
            $billing_amount = apply_filters( 'wpuf_payment_amount', $cost );
            $tax_amount     = $billing_amount - $cost;

            $data = [
                'user_id'          => $user_id,
                'status'           => 'completed',
                'subtotal'         => $cost,
                'tax'              => $tax_amount,
                'cost'             => $billing_amount,
                'post_id'          => 0,
                'pack_id'          => $pack_id,
                'payer_first_name' => $user_info->first_name,
                'payer_last_name'  => $user_info->last_name,
                'payer_email'      => $user_info->user_email,
                'payment_type'     => 'bank',
                'payer_address'    => null,
                'transaction_id'   => 0,
                'created'          => current_time( 'mysql' ),
                'profile_id'       => null,
            ];

            $is_recurring = false;

            if ( isset( $user_pack['recurring'] ) && wpuf_is_option_on( $user_pack['recurring'] ) ) {
                $is_recurring = true;
            }

            Payment::insert_payment( $data, 0, $is_recurring );
        }
    }

    /**
     * Subscription column headings
     *
     * @param array $head
     *
     * @return array
     */
    public function subscription_columns_head( $head ) {
        unset( $head['date'] );
        $head['title']          = __( 'Pack Name', 'wp-user-frontend' );
        $head['amount']         = __( 'Amount', 'wp-user-frontend' );
        $head['subscribers']    = __( 'Subscribers', 'wp-user-frontend' );
        $head['recurring']      = __( 'Recurring', 'wp-user-frontend' );
        $head['duration']       = __( 'Duration', 'wp-user-frontend' );

        return $head;
    }

    /**
     * Susbcription lists column content
     *
     * @param string $column_name
     * @param int    $post_ID
     *
     * @return void
     */
    public function subscription_columns_content( $column_name, $post_ID ) {
        switch ( $column_name ) {
            case 'amount':
                $amount = get_post_meta( $post_ID, '_billing_amount', true );

                if ( intval( $amount ) == 0 ) {
                    $amount = __( 'Free', 'wp-user-frontend' );
                } else {
                    $amount = wpuf_format_price( $amount );
                }
                echo esc_html( $amount );
                break;

            case 'subscribers':
                $users = wpuf()->subscription->subscription_pack_users( $post_ID );

                echo wp_kses_post( '<a href="' . admin_url( 'edit.php?post_type=wpuf_subscription&page=wpuf_subscribers&post_ID=' . $post_ID ) . '" />' . count( $users ) . '</a>' );
                break;

            case 'recurring':
                $recurring = get_post_meta( $post_ID, '_recurring_pay', true );

                if ( wpuf_is_option_on( $recurring ) ) {
                    esc_html_e( 'Yes', 'wp-user-frontend' );
                } else {
                    esc_html_e( 'No', 'wp-user-frontend' );
                }
                break;

            case 'duration':
                $recurring_pay        = get_post_meta( $post_ID, '_recurring_pay', true );
                $billing_cycle_number = get_post_meta( $post_ID, '_billing_cycle_number', true );
                $cycle_period         = get_post_meta( $post_ID, '_cycle_period', true );

                if ( wpuf_is_option_on( $recurring_pay ) ) {
                    echo esc_attr( $billing_cycle_number . ' ' . $cycle_period ) . '\'s (cycle)';
                } else {
                    $expiration_number    = get_post_meta( $post_ID, '_expiration_number', true );
                    $expiration_period    = get_post_meta( $post_ID, '_expiration_period', true );
                    echo esc_attr( $expiration_number . ' ' . $expiration_period ) . '\'s';
                }
                break;
        }
    }

    public function get_post_types( $post_types = null ) {
        if ( ! $post_types ) {
            $post_types = wpuf()->subscription->get_all_post_type();
        }

        ob_start();

        foreach ( $post_types as $key => $name ) {
            $post_type_object = get_post_type_object( $key );

            if ( $post_type_object ) { ?>
                <tr>
                    <th><label for="wpuf-<?php echo esc_attr( $key ); ?>"><?php printf( 'Number of %s', esc_html( $post_type_object->label ) ); ?></label></th>
                    <td>
                        <input type="text" size="20" style="" id="wpuf-<?php echo esc_attr( $key ); ?>" value="<?php echo intval( $name ); ?>" name="post_type_name[<?php echo esc_attr( $key ); ?>]" />
                        <div><span class="description"><span><?php printf( 'How many %s the user can list with this pack? Enter <strong>-1</strong> for unlimited.', esc_html( $key ) ); ?></span></span></div>
                    </td>
                </tr>
                <?php
            }
        }

        return ob_get_clean();
    }

    /**
     * Replaces default post editor with a simiple rich editor
     *
     * @param int $pack_id
     *
     * @return void
     */
    public function pack_description_metabox( $pack_id = null ) {
        global $post;

        wp_editor(
            $post->post_content, 'post_content', [
                'editor_height' => 100,
                'quicktags' => false,
                'media_buttons' => false,
            ]
        );
    }

    /**
     * Subscription settings metabox
     *
     * @return void
     */
    public function subs_meta_box() {
        global $post;

        $sub_meta = wpuf()->subscription->get_subscription_meta( $post->ID, $post );

        $hidden_recurring_class       = ! wpuf_is_option_on( $sub_meta['_recurring_pay'] ) ? 'none' : '';
        $hidden_trial_class           = ! wpuf_is_option_on( $sub_meta['_trial_status'] ) ? 'none' : '';
        $hidden_expire                = ! wpuf_is_option_on( $sub_meta['_recurring_pay'] ) ? 'none' : '';
        $is_post_exp_selected         = isset( $sub_meta['_enable_post_expiration'] ) && wpuf_is_option_on( $sub_meta['_enable_post_expiration'] ) ? 'checked' : '';
        $_post_expiration_time        = explode( ' ', isset( $sub_meta['_post_expiration_time'] ) ? $sub_meta['_post_expiration_time'] : ' ' );
        $time_value                   = isset( $_post_expiration_time[0] ) ? $_post_expiration_time[0] : 1;
        $time_type                    = isset( $_post_expiration_time[1] ) ? $_post_expiration_time[1] : 'day';

        $expired_post_status          = isset( $sub_meta['_expired_post_status'] ) ? $sub_meta['_expired_post_status'] : '';
        $is_enable_mail_after_expired = isset( $sub_meta['_enable_mail_after_expired'] ) && wpuf_is_option_on( $sub_meta['_enable_mail_after_expired'] ) ? 'checked' : '';
        $post_expiration_message      = isset( $sub_meta['_post_expiration_message'] ) ? $sub_meta['_post_expiration_message'] : '';
        $featured_item                = ! empty( $sub_meta['_total_feature_item'] ) ? $sub_meta['_total_feature_item'] : 0;
        $remove_featured_item         = ! empty( $sub_meta['_remove_feature_item'] ) ? $sub_meta['_remove_feature_item'] : 0;
        $billing_amount               = ! empty( $sub_meta['billing_amount'] ) ? esc_attr( $sub_meta['billing_amount'] ) : 0;
        ?>

        <div class="wpuf-subscription-pack-settings">
            <nav class="subscription-nav-tab">
                <ul>
                    <li class="tab-current">
                        <a href="#wpuf-payment-settings">
                            <span class="dashicons dashicons-cart"></span>
                            <?php esc_html_e( 'Payment Settings', 'wp-user-frontend' ); ?>
                        </a>
                    </li>

                    <li>
                        <a href="#wpuf-post-restriction">
                            <span class="dashicons dashicons-admin-post"></span>
                            <?php esc_html_e( 'Posting Restriction', 'wp-user-frontend' ); ?>
                        </a>
                    </li>

                    <?php do_action( 'wpuf_admin_subs_nav_tab', $post ); ?>
                </ul>
            </nav>

            <div class="subscription-nav-content">
                <section id="wpuf-payment-settings">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><label for="wpuf-billing-amount">
                                <span class="wpuf-biling-amount wpuf-subcription-expire" style="display: <?php echo esc_attr( $hidden_expire ); ?>;"><?php esc_html_e( 'Billing amount:', 'wp-user-frontend' ); ?></span>
                                <span class="wpuf-billing-cycle wpuf-recurring-child" style="display: <?php echo esc_attr( $hidden_recurring_class ); ?>;"><?php esc_html_e( 'Billing amount each cycle:', 'wp-user-frontend' ); ?></span></label></th>
                            <td>
                                <?php echo esc_attr( wpuf_get_currency( 'symbol' ) ); ?>
                                <input type="text" size="20" style="" id="wpuf-billing-amount" value="<?php echo esc_attr( $sub_meta['billing_amount'] ); ?>" name="billing_amount" />
                                <div><span class="description"></span></div>
                            </td>
                        </tr>
                        <tr class="wpuf-subcription-expire" style="display: <?php echo esc_attr( $hidden_expire ); ?>;">
                            <th><label for="wpuf-expiration-number"><?php esc_html_e( 'Expires In:', 'wp-user-frontend' ); ?></label></th>
                            <td>
                                <input type="text" size="20" style="" id="wpuf-expiration-number" value="<?php echo esc_attr( $sub_meta['expiration_number'] ); ?>" name="expiration_number" />

                                <select id="expiration-period" name="expiration_period">
                                    <?php echo esc_html( $this->option_field( $sub_meta['expiration_period'] ) ); ?>
                                </select>
                                <div><span class="description"></span></div>
                            </td>
                        </tr>

                        <?php do_action( 'wpuf_admin_subscription_detail', $sub_meta, $hidden_recurring_class, $hidden_trial_class, $this ); ?>
                        </tbody>
                    </table>
                </section>
                <section id="wpuf-post-restriction">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><label for="wpuf-sticky-item"><?php esc_html_e( 'Number of featured item', 'wp-user-frontend' ); ?></label></th>
                            <td>
                                <input type="text" size="20" style="" id="wpuf-sticky-item" value="<?php echo intval( $featured_item ); ?>" name="total_feature_item" />
                                <br>
                                <span class="description"><?php esc_html_e( 'How many items a user can set as featured, including all post types', 'wp-user-frontend' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wpuf-sticky-item"><?php esc_html_e( 'Remove featured item on subscription expiry', 'wp-user-frontend' ); ?></label></th>
                            <td>
                                <label for="">
                                    <input type="checkbox"  value="on" <?php echo esc_attr( wpuf_is_option_on( $remove_featured_item ) ? 'checked' : '' ); ?> name="remove_feature_item" />
                                    <?php esc_html_e( 'The featured item will be removed if the subscription expires', 'wp-user-frontend' ); ?>
                                </label>
                            </td>
                        </tr>
                            <?php
                                echo wp_kses(
                                    $this->get_post_types( $sub_meta['post_type_name'] ),
                                    [
                                        'div'    => [],
                                        'tr'     => [],
                                        'td'     => [],
                                        'th'     => [],
                                        'label'  => [
                                            'for' => [],
                                        ],
                                        'input' => [
                                            'type'  => [],
                                            'size'  => [],
                                            'style' => [],
                                            'id'    => [],
                                            'value' => [],
                                            'name'  => [],
                                        ],
                                        'span' => [
                                            'class' => [],
                                        ],
                                        'strong' => [],
                                    ]
                                );
                            ?>
                            <?php
                            // do_action( 'wpuf_admin_subscription_detail', $sub_meta, $hidden_recurring_class, $hidden_trial_class, $this );
                            ?>
                            <tr class="wpuf-metabox-post_expiration">

                                <th><?php esc_html_e( 'Post Expiration', 'wp-user-frontend' ); ?></th>

                                <td>
                                    <label>
                                        <input type="checkbox" id="wpuf-enable_post_expiration" name="post_expiration_settings[enable_post_expiration]" value="on" <?php echo esc_attr( $is_post_exp_selected ); ?> />
                                        <?php esc_html_e( 'Enable Post Expiration', 'wp-user-frontend' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr class="wpuf-metabox-post_expiration wpuf_subscription_expiration_field">
                                <?php
                                $timeType_array = [
                                    'year',
                                    'month',
                                    'day',
                                ];
                                ?>
                                <th class="wpuf-post-exp-time"> <?php esc_html_e( 'Post Expiration Time', 'wp-user-frontend' ); ?> </th>
                                <td class="wpuf-post-exp-time">
                                    <input type="number" name="post_expiration_settings[expiration_time_value]" id="wpuf-expiration_time_value" value="<?php echo $time_value; ?>" id="wpuf-expiration_time_value" min="1">
                                    <select name="post_expiration_settings[expiration_time_type]" id="wpuf-expiration_time_type">
                                        <?php
                                        foreach ( $timeType_array as $each_time_type ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $each_time_type ); ?>" <?php echo $each_time_type == $time_type ? 'selected' : ''; ?>><?php echo esc_html( ucfirst( $each_time_type ) . '(s)' ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                            </tr>
                            <tr class="wpuf_subscription_expiration_field">
                                <th>
                                    <?php esc_html_e( 'Post Status', 'wp-user-frontend' ); ?>
                                </th>
                                <td>
                                    <?php $post_statuses = get_post_statuses(); ?>
                                    <select name="post_expiration_settings[expired_post_status]" id="wpuf-expired_post_status">
                                        <?php
                                        foreach ( $post_statuses as $post_status => $text ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $post_status ); ?>" <?php echo ( $expired_post_status == $post_status ) ? 'selected' : ''; ?>><?php echo esc_html( $text ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Status of post after post expiration time is over ', 'wp-user-frontend' ); ?></p>
                                </td>
                            </tr>
                            <tr class="wpuf_subscription_expiration_field">
                                <th>
                                    <?php esc_html_e( 'Expiration Mail', 'wp-user-frontend' ); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="post_expiration_settings[enable_mail_after_expired]" value="on" <?php echo esc_attr( $is_enable_mail_after_expired ); ?> />
                                        <?php esc_html_e( 'Send Expiration Email to Post Author', 'wp-user-frontend' ); ?>
                                    </label>

                                    <p class="help">
                                        <?php esc_html_e( 'Send Mail to Author After Exceeding Post Expiration Time', 'wp-user-frontend' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="wpuf_subscription_expiration_field">
                                <th><?php esc_html_e( 'Expiration Message', 'wp-user-frontend' ); ?></th>
                                <td>
                                    <textarea name="post_expiration_settings[post_expiration_message]" id="wpuf-post_expiration_message" cols="50" rows="5"><?php echo esc_attr( $post_expiration_message ); ?></textarea>
                                    <p class="description">
                                        <strong>
                                            <?php
                                            printf(
                                            // translators: %1$s: {post_author}, %2$s: {post_url}, %3$s: {blogname}, %4$s: {post_title}, %5$s: {post_status}
                                                __( 'You may use: %1$s %2$s %3$s %4$s %5$s', 'wp-user-frontend' ),
                                                '{post_author}',
                                                '{post_url}',
                                                '{blogname}',
                                                '{post_title}',
                                                '{post_status}'
                                            )
                                            ?>
                                        </strong>
                                    </p>
                                </td>
                            </tr>

                            <?php
                                /**
                                 * @since 2.7.0
                                 */
                                do_action( 'wpuf_admin_subscription_post_restriction', $sub_meta, $post, $this );
                            ?>
                        </tbody>
                    </table>
                </section>

                <?php do_action( 'wpuf_admin_subs_nav_content', $post ); ?>
            </div>
            <?php wp_nonce_field( 'subs_meta_box_nonce', 'meta_box_nonce' ); ?>
        </div>

        <?php
    }

    /**
     * Enqueue script for subscription editor page
     *
     * @return void
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();

        if ( 'wpuf_subscription' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-metabox-tabs' );
    }

    /**
     * Enqueue script for profile
     *
     * @return void
     */
    public function enqueue_profile_script() {
        $screen = get_current_screen();

        if ( 'profile' != $screen->base ) {
            return;
        }

        // wp_enqueue_script( 'wpuf-admin-profile-subs', WPUF_ASSET_URI . '/js/admin-profile-subs.js', [ 'jquery' ] );
    }

    /**
     * Option fields for date type
     *
     * @param string $selected
     *
     * @return void
     */
    public function option_field( $selected ) {
        ?>
        <option value="day" <?php selected( $selected, 'day' ); ?> ><?php esc_html_e( 'Day(s)', 'wp-user-frontend' ); ?></option>
        <option value="week" <?php selected( $selected, 'week' ); ?> ><?php esc_html_e( 'Week(s)', 'wp-user-frontend' ); ?></option>
        <option value="month" <?php selected( $selected, 'month' ); ?> ><?php esc_html_e( 'Month(s)', 'wp-user-frontend' ); ?></option>
        <option value="year" <?php selected( $selected, 'year' ); ?> ><?php esc_html_e( 'Year(s)', 'wp-user-frontend' ); ?></option>
        <?php
    }

    public function packdropdown_without_recurring( $packs, $selected = '' ) {
        $packs = isset( $packs ) ? $packs : [];

        foreach ( $packs as $key => $pack ) {
            $recurring = isset( $pack->meta_value['recurring_pay'] ) ? $pack->meta_value['recurring_pay'] : '';

            if ( wpuf_is_option_on( $recurring ) ) {
                continue;
            }
            ?>
            <option value="<?php echo esc_attr( $pack->ID ); ?>" <?php selected( $selected, $pack->ID ); ?>><?php echo esc_attr( $pack->post_title ); ?></option>
            <?php
        }
    }

    /**
     * Adds the postlock form in users profile
     *
     * @param object $profileuser
     */
    public function profile_subscription_details( $profileuser ) {
        if ( ! current_user_can( 'edit_users' ) ) {
            return;
        }

        wp_enqueue_script( 'wpuf-subscriptions' );

        $current_user = wpuf_get_user();

        if ( ! $current_user->subscription()->current_pack_id() ) {
            // return;
        }

        $userdata = get_userdata( $profileuser->ID ); //wp 3.3 fix

        $packs    = wpuf()->subscription->get_subscriptions();
        $user_sub = wpuf()->subscription->get_user_pack( $userdata->ID );
        $pack_id  = isset( $user_sub['pack_id'] ) ? $user_sub['pack_id'] : '';
        ?>
        <div class="wpuf-user-subscription" style="width: 640px;">
            <h3><?php esc_html_e( 'WPUF Subscription Information', 'wp-user-frontend' ); ?></h3>

            <?php

            if ( isset( $user_sub['pack_id'] ) ) {
                $pack         = wpuf()->subscription->get_subscription( $user_sub['pack_id'] );
                $details_meta = wpuf()->subscription->get_details_meta_value();

                $billing_amount = ( isset( $pack->meta_value['billing_amount'] ) && intval( $pack->meta_value['billing_amount'] ) > 0 ) ? $details_meta['symbol'] . $pack->meta_value['billing_amount'] : __( 'Free', 'wp-user-frontend' );
                $recurring_pay  = isset( $pack->meta_value['recurring_pay'] ) && wpuf_is_option_on( $pack->meta_value['recurring_pay'] );

                if ( $billing_amount && $recurring_pay ) {
                    $recurring_des = sprintf( __( 'For each %1$s %2$s', 'wp-user-frontend' ), $pack->meta_value['billing_cycle_number'], $pack->meta_value['cycle_period'], $pack->meta_value['trial_duration_type'] );
                    $recurring_des .= ! empty( $pack->meta_value['billing_limit'] ) ? sprintf( __( ', for %s installments', 'wp-user-frontend' ), $pack->meta_value['billing_limit'] ) : '';
                    $recurring_des = $recurring_des;
                } else {
                    $recurring_des = '';
                }
                ?>
                <div class="wpuf-user-sub-info">

                    <div class="wpuf-sub-summary">
                        <div class="sub-name">
                            <span class="label">
                                <?php esc_html_e( 'Subcription Name', 'wp-user-frontend' ); ?>
                            </span>

                            <span class="value">
                                <?php echo isset( $pack->post_title ) ? esc_html( $pack->post_title ) : ''; ?>
                            </span>
                        </div>

                        <div class="sub-price">
                            <span class="label">
                                <?php esc_html_e( 'Billing Info', 'wp-user-frontend' ); ?>
                            </span>

                            <span class="value">
                                <?php echo esc_html( $billing_amount ); ?>

                                <?php if ( $recurring_des ) { ?>
                                    <p><?php echo esc_html( $recurring_des ); ?></p>
                                <?php } ?>
                            </span>
                        </div>

                        <?php if ( isset( $user_sub['recurring'] ) && wpuf_is_option_on( $user_sub['recurring'] ) ) { ?>
                            <div class="info">
                                <p><?php esc_html_e( 'This user is using recurring subscription pack', 'wp-user-frontend' ); ?></p>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="wpuf-sub-section remaining-posts">
                        <h4><?php esc_html_e( 'Remaining Posting Count', 'wp-user-frontend' ); ?></h4>

                        <table class="form-table">
                            <?php if ( ! empty( $user_sub['total_feature_item'] ) ) { ?>
                            <tr>
                                <th><label><?php esc_html_e( 'Number of featured item', 'wp-user-frontend' ); ?></label></th>
                                <td><?php echo esc_attr( $user_sub['total_feature_item'] ); ?></td>
                            </tr>
                            <?php } ?>
                            <?php
                            if ( $user_sub['posts'] ) {
                                foreach ( $user_sub['posts'] as $key => $value ) {
                                    $post_type_object = get_post_type_object( $key );

                                    if ( $post_type_object ) {
                                        ?>
                                        <tr>
                                            <th><label><?php echo esc_html( $post_type_object->labels->name ); ?></label></th>
                                            <td><?php echo esc_attr( $value ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </table>
                    </div>

                    <div class="wpuf-sub-section post-expiration">
                        <h4><?php esc_html_e( 'Subscription Expiration Info', 'wp-user-frontend' ); ?></h4>

                        <table class="form-table">
                            <?php
                            if ( wpuf_is_option_on( $user_sub['recurring'] ) ) {
                                if ( ! empty( $user_sub['expire'] ) ) {
                                    $expire = ( $user_sub['expire'] == 'unlimited' ) ? ucfirst( 'unlimited' ) : wpuf_get_date( wpuf_date2mysql( $user_sub['expire'] ) );
                                    ?>
                                    <tr>
                                        <th><label><?php esc_html_e( 'Expire date:', 'wp-user-frontend' ); ?></label></th>
                                        <td><?php echo esc_html( $expire ); ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                            $is_post_exp_selected  = isset( $user_sub['_enable_post_expiration'] ) ? 'checked' : '';
                            $_post_expiration_time = explode( ' ', isset( $user_sub['_post_expiration_time'] ) ? $user_sub['_post_expiration_time'] : '' );
                            $time_value            = isset( $_post_expiration_time[0] ) && ! empty( $_post_expiration_time[0] ) ? $_post_expiration_time[0] : '1';
                            $time_type             = isset( $_post_expiration_time[1] ) && ! empty( $_post_expiration_time[1] ) ? $_post_expiration_time[1] : 'day';
                            ?>
                            <tr>
                                <th><label><?php esc_html_e( 'Post Expiration Enabled', 'wp-user-frontend' ); ?></label></th>
                                <td><?php $is_post_exp_selected ? _e( 'Yes', 'wp-user-frontend' ) : _e( 'No', 'wp-user-frontend' ); ?></td>
                            </tr>
                            <tr class="wpuf-post-exp-time">
                                <?php
                                $timeType_array = [
                                    'year'  => 100,
                                    'month' => 12,
                                    'day'   => 30,
                                ];
                                ?>
                                <th><?php esc_html_e( 'Post Expiration Time', 'wp-user-frontend' ); ?></th>
                                <td>
                                    <select name="post_expiration_settings[expiration_time_value]" id="wpuf-expiration_time_value" disabled>
                                        <?php
                                        for ( $i = 1; $i <= $timeType_array[ $time_type ]; $i++ ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $i ); ?>" <?php echo $i == $time_value ? 'selected' : ''; ?>><?php echo esc_attr( $i ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <select name="post_expiration_settings[expiration_time_type]" id="wpuf-expiration_time_type" disabled>
                                        <?php
                                        foreach ( $timeType_array as $each_time_type => $each_time_type_val ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $each_time_type ); ?>" <?php echo $each_time_type == $time_type ? 'selected' : ''; ?>><?php echo esc_html( ucfirst( $each_time_type ) ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="wpuf-sub-section tax-restriction">
                        <h4><?php esc_html_e( 'Allowed Taxonomy Terms', 'wp-user-frontend' ); ?></h4>

                        <table class="form-table">
                            <tr>
                                <?php
                                    $allowed_tax_id_arr = [];
                                $allowed_tax_id_arr                     = get_post_meta( $pack_id, '_sub_allowed_term_ids', true );

                                if ( ! $allowed_tax_id_arr ) {
                                    $allowed_tax_id_arr = [];
                                }

                                $builtin_taxs = get_taxonomies(
                                    [
                                        '_builtin' => true,
                                    ], 'objects'
                                );

                                foreach ( $builtin_taxs as $builtin_tax ) {
                                    if ( is_taxonomy_hierarchical( $builtin_tax->name ) ) {
                                        $tax_terms = get_terms(
                                            [
                                                'taxonomy'   => $builtin_tax->name,
                                                'hide_empty' => false,
                                            ]
                                        );

                                        foreach ( $tax_terms as $tax_term ) {
                                            if ( in_array( $tax_term->term_id, $allowed_tax_id_arr ) ) {
                                                ?>
                                 <td> <?php echo esc_html( $tax_term->name ); ?> </td>
                                                <?php
                                            }
                                        }
                                    }
                                }

                                $custom_taxs = get_taxonomies( [ '_builtin' => false ], 'objects' );

                                foreach ( $custom_taxs as $custom_tax ) {
                                    if ( is_taxonomy_hierarchical( $custom_tax->name ) ) {
                                        $tax_terms = get_terms(
                                            [
                                                'taxonomy'   => $custom_tax->name,
                                                'hide_empty' => false,
                                            ]
                                        );

                                        foreach ( $tax_terms as $tax_term ) {
                                            if ( in_array( $tax_term->term_id, $allowed_tax_id_arr ) ) {
                                                ?>
                                 <td> <?php echo esc_html( $tax_term->name ); ?> </td>
                                                <?php
                                            }
                                        }
                                    }
                                }
                                ?>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php
            }
            ?>

            <?php if ( ! isset( $user_sub['recurring'] ) || wpuf_is_option_on( $user_sub['recurring'] ) ) { ?>

                <?php if ( empty( $user_sub ) ) { ?>
                    <div class="wpuf-sub-actions">
                        <a class="btn button-secondary wpuf-assing-pack-btn wpuf-add-pack" href="#"><?php esc_html_e( 'Assign Package', 'wp-user-frontend' ); ?></a>
                        <a class="btn button-secondary wpuf-assing-pack-btn wpuf-cancel-pack" style="display:none;" href="#"><?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?></a>
                    </div>
                <?php } ?>

                <table class="form-table wpuf-pack-dropdown" disabled="disabled" style="display: none;">
                    <tr>
                        <th><label for="wpuf_sub_pack"><?php esc_html_e( 'Select Package:', 'wp-user-frontend' ); ?> </label></th>
                        <td>
                            <select name="pack_id" id="wpuf_sub_pack">
                                <option value="-1"><?php esc_html_e( '&mdash; Select &mdash;', 'wp-user-frontend' ); ?></option>
                                <?php $this->packdropdown_without_recurring( $packs, $pack_id ); //wpuf()->subscription->packdropdown( $packs, $selected = '' ); ?>
                            </select>
                            <br>
                            <span class="description"><?php esc_html_e( 'Only non-recurring pack can be assigned', 'wp-user-frontend' ); ?></span>
                        </td>
                    </tr>
                </table>
            <?php } ?>
            <?php
            wp_nonce_field( 'update-profile_' . $userdata->ID, 'wpuf-subscription-nonce' );
            do_action( 'wpuf_admin_subscription_content', $userdata->ID );
            ?>
            <?php if ( ! empty( $user_sub ) ) { ?>
                <div class="wpuf-sub-actions">
                    <a class="btn button-secondary wpuf-delete-pack-btn" href="javascript:" data-userid="<?php echo esc_attr( $userdata->ID ); ?>" data-packid="<?php echo isset( $user_sub['pack_id'] ) ? esc_attr( $user_sub['pack_id'] ) : ''; ?>"><?php esc_html_e( 'Delete Package', 'wp-user-frontend' ); ?></a>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    public function lenght_type_option( $selected ) {
        for ( $i = 1; $i <= 30; $i++ ) {
            ?>
                <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, $selected ); ?>><?php echo esc_html( $i ); ?></option>
            <?php
        }
    }

    /**
     * Ajax function. Delete user package
     *
     * @since 2.2.7
     */
    public function delete_user_package() {
        $nonce = isset( $_REQUEST['wpuf_subscription_delete_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf_subscription_delete_nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-subscription-delete-nonce' ) ) {
            return;
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            return;
        }

        $userid = isset( $_POST['userid'] ) ? intval( wp_unslash( $_POST['userid'] ) ) : 0;

        echo esc_html( delete_user_meta( $userid, '_wpuf_subscription_pack' ) );
        $wpuf_paypal = new Paypal();
        $wpuf_paypal->recurring_change_status( $userid, 'Cancel' );

        if ( isset( $_POST['packid'] ) ) {
            $pack_id = intval( wp_unslash( $_POST['packid'] ) );
            wpuf()->subscription->subscriber_cancel( $userid, $pack_id );
        }
        exit;
    }

    /**
     * Add help link to the subscriptions listing page
     *
     * @return void
     */
    public function add_help_link() {
        $screen = get_current_screen();

        if ( 'edit-wpuf_subscription' != $screen->id ) {
            return;
        }
        ?>
        <div class="wpuf-footer-help">
            <span class="wpuf-footer-help-content">
                <span class="dashicons dashicons-editor-help"></span>
                <?php printf( wp_kses_post( __( 'Learn more about <a href="%s" target="_blank">Subscription</a>', 'wp-user-frontend' ) ), 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/?utm_source=wpuf-footer-help&utm_medium=text-link&utm_campaign=learn-more-subscription' ); ?>
            </span>
        </div>

        <script type="text/javascript">
            jQuery(function($) {
                $('.wpuf-footer-help').appendTo('.wrap');
            });
        </script>
        <?php
    }

    /**
     * Get all the sections of the subscription settings
     *
     * @since 4.0.11
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            [
                'id'    => 'subscription_details',
                'title' => __( 'Subscription Details', 'wp-user-frontend' ),
            ],
            [
                'id'    => 'payment_settings',
                'title' => __( 'Payment Settings', 'wp-user-frontend' ),
            ],
            [
                'id'    => 'advanced_configuration',
                'title' => __( 'Advanced Configuration', 'wp-user-frontend' ),
            ],
        ];

        return apply_filters( 'wpuf_subscriptions_sections', $sections );
    }

    /**
     * Get all the sub-sections of the subscription settings
     *
     * @since 4.0.11
     *
     * @return array
     */
    public function get_sub_sections() {
        $subscription_details = apply_filters(
            'wpuf_subscription_section_details', [
                'subscription_details' => [
                    [
                        'id'    => 'overview',
                        'label' => __( 'Overview', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'access_and_visibility',
                        'label' => __( 'Access and Visibility', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'post_expiration',
                        'label' => __( 'Post Expiration', 'wp-user-frontend' ),
                    ],
                ],
            ]
        );

        $payment = apply_filters(
            'wpuf_subscription_section_payment', [
                'payment_settings' => [
                    [
                        'id'     => 'payment_details',
                        'label'  => __( 'Payment Details', 'wp-user-frontend' ),
                        'notice' => [
                            'type'    => 'attention',
                            'message' => sprintf(
                                // translators: %s: Payment Settings URL
                                __(
                                    'For subscriptions to work correctly, please ensure the payment gateway and related settings are properly configured in the <a href="%s">Payment Settings</a>',
                                    'wp-user-frontend'
                                ), admin_url( 'admin.php?page=wpuf-settings' )
                            ),
                        ],
                    ],
                ],
            ]
        );

        $advanced = apply_filters(
            'wpuf_subscription_section_advanced', [
                'advanced_configuration' => [
                    [
                        'id'    => 'content_limit',
                        'label' => __( 'Content Limit', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'design_elements',
                        'label' => __( 'Design Elements', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'additional',
                        'label' => __( 'Additional Options', 'wp-user-frontend' ),
                    ],
                ],
            ]
        );

        return apply_filters( 'wpuf_subscription_sub_sections', array_merge( $subscription_details, $payment, $advanced ) );
    }

    /**
     * Returns all the subscription fields that are used in the sections
     *
     * @since 4.0.11
     *
     * @return array
     */
    public function get_fields() {
        $overview           = apply_filters(
            'wpuf_subscription_overview_fields', [
                'overview' => [
                    'plan_name'    => [
                        'id'          => 'plan-name',
                        'name'        => 'plan-name',
                        'db_key'      => 'post_title',
                        'db_type'     => 'post',
                        'type'        => 'input-text',
                        'label'       => __( 'Plan Name', 'wp-user-frontend' ),
                        'tooltip'     => __( 'Enter a name for this subscription plan. E.g., "Featured Article Subscription"', 'wp-user-frontend' ),
                        'placeholder' => __( 'Enter subscription name', 'wp-user-frontend' ),
                        'is_required' => true,
                        'default'     => '',
                    ],
                    'plan_summary' => [
                        'id'          => 'plan-summary',
                        'name'        => 'plan-summary',
                        'db_key'      => 'post_content',
                        'db_type'     => 'post',
                        'type'        => 'textarea',
                        'label'       => __( 'Plan Summary', 'wp-user-frontend' ),
                        'tooltip'     => __(
                            'Provide a brief description of this subscription plan to help users understand key features or benefits',
                            'wp-user-frontend'
                        ),
                        'placeholder' => __( 'Write briefly what this subscription is about', 'wp-user-frontend' ),
                        'default'     => '',
                    ],
                ],
            ]
        );
        $access             = apply_filters(
            'wpuf_subscription_access_fields', [
                'access_and_visibility' => [
                    'plan_slug'    => [
                        'id'          => 'plan-slug',
                        'name'        => 'plan-slug',
                        'db_key'      => 'post_name',
                        'db_type'     => 'post',
                        'type'        => 'input-text',
                        'label'       => __( 'Plan Slug', 'wp-user-frontend' ),
                        'tooltip'     => __(
                            'Enter a unique slug for the subscription. Leave it blank for WordPress default slug',
                            'wp-user-frontend'
                        ),
                        'placeholder' => __( 'Enter plan slug', 'wp-user-frontend' ),
                        'default'     => '',
                    ],
                    'publish_time' => [
                        'id'          => 'publish-time',
                        'name'        => 'publish-time',
                        'db_key'      => 'post_date',
                        'db_type'     => 'post',
                        'type'        => 'time-date',
                        'label'       => __( 'Publish Time', 'wp-user-frontend' ),
                        'tooltip'     => __( 'Specify the time when you want the subscription to be published', 'wp-user-frontend' ),
                        'default'     => wpuf_current_datetime()->format( 'Y-m-d H:i:s' ),
                    ],
                ],
            ]
        );
        $expiration         = apply_filters(
            'wpuf_subscription_expiration_fields', [
                'post_expiration' => [
                    'post_expiration'      => [
                        'id'      => 'post-expiration',
                        'name'    => 'post-expiration',
                        'db_key'  => '_enable_post_expiration',
                        'db_type' => 'meta',
                        'type'    => 'switcher',
                        'label'   => __( 'Enable Post Expiration', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Enable post expiration for this subscription plan. If enabled, posts in this plan will expire after a certain period, as specified here',
                            'wp-user-frontend'
                        ),
                        'default' => false,
                    ],
                    'expiration_time'      => [
                        'id'      => 'expiration-time',
                        'name'    => 'expiration-time',
                        'type'    => 'inline',
                        'db_key'  => '_post_expiration_time',
                        'db_type' => 'meta',
                        'key_id'  => 'expiration_time',
                        'label'   => __( 'Expiration Time', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Specify the duration after which your posts will automatically disappear from frontend',
                            'wp-user-frontend'
                        ),
                        'fields'  => [
                            'expiration_value' => [
                                'id'      => 'post-expiration-value',
                                'name'    => 'post-expiration-value',
                                'type'    => 'input-number',
                                'db_key'  => '_post_expiration_number',
                                'db_type' => 'meta',
                                'key_id'  => 'expiration_value',
                                'default' => -1,
                            ],
                            'expiration_unit'  => [
                                'id'      => 'post-expiration-unit',
                                'name'    => 'post-expiration-unit',
                                'type'    => 'select',
                                'db_key'  => '_post_expiration_period',
                                'db_type' => 'meta',
                                'key_id'  => 'expiration_unit',
                                'options' => [
                                    'forever' => __( 'Never', 'wp-user-frontend' ),
                                    'day'     => __( 'Day(s)', 'wp-user-frontend' ),
                                    'week'    => __( 'Week(s)', 'wp-user-frontend' ),
                                    'month'   => __( 'Month(s)', 'wp-user-frontend' ),
                                    'year'    => __( 'Year(s)', 'wp-user-frontend' ),
                                ],
                                'default' => 'day',
                            ],
                        ],
                    ],
                    'post_status'          => [
                        'id'          => 'post-status',
                        'name'        => 'post-status',
                        'db_key'      => '_expired_post_status',
                        'db_type'     => 'meta',
                        'type'        => 'select',
                        'options'     => [
                            'publish' => __( 'Publish', 'wp-user-frontend' ),
                            'draft'   => __( 'Draft', 'wp-user-frontend' ),
                            'pending' => __( 'Pending Review', 'wp-user-frontend' ),
                        ],
                        'label'       => __( 'Post Status', 'wp-user-frontend' ),
                        'tooltip'     => __( 'Status of post after post expiration time is over', 'wp-user-frontend' ),
                        'placeholder' => __(
                            'Post status will be changed to the selected one when expiration time is over',
                            'wp-user-frontend'
                        ),
                        'key_id'      => 'post_status',
                        'default'     => 'publish',
                    ],
                    'send_mail'            => [
                        'id'      => 'is-send-mail',
                        'name'    => 'is-send-mail',
                        'db_key'  => '_enable_mail_after_expired',
                        'db_type' => 'meta',
                        'type'    => 'switcher',
                        'label'   => __( 'Send Expiration Mail', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Send an e-mail to the author after exceeding post expiration time', 'wp-user-frontend'
                        ),
                        'key_id'  => 'send_mail',
                        'default' => '',
                    ],
                    'expiration_message'   => [
                        'id'          => 'expiration-message',
                        'name'        => 'expiration-message',
                        'db_key'      => '_post_expiration_message',
                        'db_type'     => 'meta',
                        'type'        => 'textarea',
                        'label'       => __( 'Expiration Message', 'wp-user-frontend' ),
                        'tooltip'     => __(
                            'Craft a personalized message that will be sent to users when their posts expire',
                            'wp-user-frontend'
                        ),
                        'description' => __(
                            'You may use: {post_author} {post_url} {blogname} {post_title} {post_status}',
                            'wp-user-frontend'
                        ),
                        'placeholder' => __(
                            'Write the expiration message here',
                            'wp-user-frontend'
                        ),
                        'key_id'      => 'expiration_message',
                        'default'     => '',
                    ],
                    'post_number_rollback' => [
                        'id'      => 'post-number-rollback',
                        'name'    => 'post-number-rollback',
                        'db_key'  => 'postnum_rollback_on_delete',
                        'db_type' => 'meta',
                        'type'    => 'switcher',
                        'label'   => __( 'Enable Post Number Rollback', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'If enabled, number of posts will be restored if the post is deleted.', 'wp-user-frontend'
                        ),
                        'default' => false,
                    ],
                ],
            ]
        );
        $payment            = apply_filters(
            'wpuf_subscription_payment_fields', [
                'payment_details' => [
                    'billing_amount'   => [
                        'id'      => 'billing-amount',
                        'name'    => 'billing-amount',
                        'db_key'  => '_billing_amount',
                        'db_type' => 'meta',
                        'type'    => 'input-number',
                        'label'   => __( 'Billing Amount', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Enter the billing amount for the subscription that will be charged to users who subscribe to this plan',
                            'wp-user-frontend'
                        ),
                        'default' => 0,
                    ],
                    'expire_in'        => [
                        'id'      => 'subs-expiration-time',
                        'name'    => 'subs-expiration-time',
                        'type'    => 'inline',
                        'fields'  => [
                            'subs_expiration_value' => [
                                'id'      => 'subs-expiration-value',
                                'name'    => 'subs-expiration-value',
                                'type'    => 'input-number',
                                'db_key'  => '_expiration_number',
                                'db_type' => 'meta',
                                'default' => -1,
                            ],
                            'subs_expiration_unit'  => [
                                'id'      => 'subs-expiration-unit',
                                'name'    => 'subs-expiration-unit',
                                'db_key'  => '_expiration_period',
                                'db_type' => 'meta',
                                'type'    => 'select',
                                'options' => [
                                    'day'   => __( 'Day(s)', 'wp-user-frontend' ),
                                    'week'  => __( 'Week(s)', 'wp-user-frontend' ),
                                    'month' => __( 'Month(s)', 'wp-user-frontend' ),
                                    'year'  => __( 'Year(s)', 'wp-user-frontend' ),
                                ],
                                'default' => 'day',
                            ],
                        ],
                        'key_id'  => 'expiration_time',
                        'label'   => __( 'Expire In', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Set the duration for the subscription to remain active before expiring. Enter -1 for no expiration', 'wp-user-frontend'
                        ),
                    ],
                    'enable_recurring' => [
                        'type'    => 'switcher',
                        'label'   => __( 'Enable Recurring Payment', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Enable recurring payments for this subscription. Users will be charged automatically at the end of each billing cycle until the subscription is canceled',
                            'wp-user-frontend'
                        ),
                        'default' => false,
                        'is_pro'  => true,
                    ],
                ],
            ]
        );
        $content_limit      = apply_filters(
            'wpuf_subscription_content_limits_fields', [
                'content_limit' => [
                    'number_of_posts'         => [
                        'id'            => 'number-of-posts',
                        'name'          => 'number-of-posts',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'post',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Posts', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set the maximum number of posts users can list within their subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_pages'         => [
                        'id'            => 'number-of-pages',
                        'name'          => 'number-of-pages',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'page',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Pages', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set the maximum number of pages a user can list within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_user_requests' => [
                        'id'            => 'number-of-user-requests',
                        'name'          => 'number-of-user-requests',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'user_request',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of User Requests', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set the maximum number of user requests allowed within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                ],
            ]
        );
        $design_element     = apply_filters(
            'wpuf_subscription_design_elements_fields', [
                'design_elements' => [
                    'number_of_blocks'         => [
                        'id'            => 'number-of-blocks',
                        'name'          => 'number-of-blocks',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'wp_block',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Reusable Block', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set the maximum number of reusable blocks that users can create within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_templates'      => [
                        'id'            => 'number-of-templates',
                        'name'          => 'number-of-templates',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'wp_template',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Templates', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set the maximum number of templates users can use during the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_template_parts' => [
                        'id'            => 'number-of-template-parts',
                        'name'          => 'number-of-template-parts',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'wp_template_part',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Template Parts', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set maximum number of template parts that users can create within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_global_styles'  => [
                        'id'            => 'number-of-global-styles',
                        'name'          => 'number-of-global-styles',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'wp_global_styles',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Global Styles', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set maximum number of global styles that users can use within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                    'number_of_menus'          => [
                        'id'            => 'number-of-menus',
                        'name'          => 'number-of-menus',
                        'db_key'        => '_post_type_name',
                        'db_type'       => 'meta_serialized',
                        'serialize_key' => 'wp_navigation',
                        'type'          => 'input-number',
                        'label'         => __( 'Maximum Number of Navigation Menus', 'wp-user-frontend' ),
                        'tooltip'       => __(
                            'Set maximum number of navigation menus that users can use within the subscription period. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default'       => '-1',
                    ],
                ],
            ]
        );
        $additional_options = apply_filters(
            'wpuf_subscription_additional_fields', [
                'additional' => [
                    'number_of_featured_items' => [
                        'id'      => 'number-of-featured-items',
                        'name'    => 'number-of-featured-items',
                        'db_key'  => '_total_feature_item',
                        'db_type' => 'meta',
                        'type'    => 'input-number',
                        'label'   => __( 'Maximum Number of Featured Items', 'wp-user-frontend' ),
                        'tooltip' => __(
                            'Limit the featured items users can display during their subscription. Featured items gain more visibility, enhancing content or product exposure. Enter -1 for unlimited',
                            'wp-user-frontend'
                        ),
                        'default' => '-1',
                    ],
                    'remove_featured_item'     => [
                        'id'      => 'remove-featured-item',
                        'name'    => 'remove-featured-item',
                        'db_key'  => '_remove_feature_item',
                        'db_type' => 'meta',
                        'type'    => 'switcher',
                        'label'   => __( 'Remove Featured Item', 'wp-user-frontend' ),
                        'tooltip' => __( 'Remove featured items when plan expires', 'wp-user-frontend' ),
                        'default' => '-1',
                    ],
                ],
            ]
        );

        $fields = [
            'subscription_details'   => array_merge( $overview, $access, $expiration ),
            'payment_settings'       => $payment,
            'advanced_configuration' => array_merge( $content_limit, $design_element, $additional_options ),
        ];

        return apply_filters( 'wpuf_subscriptions_fields', $fields );
    }

    /**
     * Get all the fields that depend on other fields
     *
     * @since 4.0.11
     *
     * @return array
     */
    public function get_dependent_fields() {
        $fields = [
            'post_expiration'  => [
                'expiration_time'    => 'hide',
                'post_status'        => 'hide',
                'send_mail'          => 'hide',
                'expiration_message' => 'hide',
            ],
            'send_mail'        => [
                'expiration_message' => 'hide',
            ],
            'enable_recurring' => [
                'payment_cycle' => 'hide',
                'stop_cycle'    => 'hide',
                'billing_limit' => 'hide',
                'trial'         => 'hide',
                'trial_period'  => 'hide',
                'expire_in'     => 'show',
            ],
            'stop_cycle'       => [
                'billing_limit' => 'hide',
            ],
            'trial'            => [
                'trial_period' => 'hide',
            ],
        ];

        return apply_filters( 'wpuf_subscriptions_dependent_fields', $fields );
    }

    /**
     * Modify the admin footer text
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function modify_admin_footer_text() {
        add_action( 'admin_footer_text', [ $this, 'admin_footer_text' ] );
    }

    /**
     * Modify the admin footer text
     *
     * @since 4.0.11
     *
     * @param string $footer_text
     *
     * @return string
     */
    public function admin_footer_text( $footer_text ) {
        $footer_text = __( 'Thank you for using <strong>WP User Frontend</strong>.', 'wp-user-frontend' );
        $footer_text .= ' ' . sprintf(
            // Translators: %s: link to the classic UI
            __( 'Use the <a href="%s">classic UI</a>.', 'wp-user-frontend' ), admin_url( 'edit.php?post_type=wpuf_subscription' )
        );

        return $footer_text;
    }
}
