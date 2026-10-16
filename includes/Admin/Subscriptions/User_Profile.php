<?php
/**
 * User profile subscription
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Admin_Subscription;
use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * A user's subscription pack on the profile screen: the details section, its
 * save, the pack dropdown and the pack removal (`remove_user_pack()`, REST `admin/users/{id}/pack`).
 *
 * @since WPUF_SINCE Moved out of Admin_Subscription, which keeps the hooks and delegates.
 */
class User_Profile {

    /**
     * The facade: callbacks and methods of the other groups go through it.
     *
     * @var Admin_Subscription
     */
    protected $admin;

    /**
     * @since WPUF_SINCE
     *
     * @param Admin_Subscription $admin The facade.
     */
    public function __construct( Admin_Subscription $admin ) {
        $this->admin = $admin;
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
                    $recurring_des = sprintf(
                        // translators: %1$s and %2$s are Billing cycle number and Billing cycle period
                        __( 'For each %1$s %2$s', 'wp-user-frontend' ),
                        $pack->meta_value['billing_cycle_number'],
                        $pack->meta_value['cycle_period'],
                        $pack->meta_value['trial_duration_type']
                    );
                    $recurring_des .= ! empty( $pack->meta_value['billing_limit'] ) ? sprintf(
                        // translators: %s is Billing limit
                        __( ', for %s installments', 'wp-user-frontend' ),
                        $pack->meta_value['billing_limit']
                    ) : '';
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
                                <td><?php $is_post_exp_selected ? esc_html_e( 'Yes', 'wp-user-frontend' ) : esc_html_e( 'No', 'wp-user-frontend' ); ?>
                                </td>
                            </tr>
                            <tr class="wpuf-post-exp-time">
                                <?php
                                $time_type_array = [
                                    'year'  => 100,
                                    'month' => 12,
                                    'day'   => 30,
                                ];
                                ?>
                                <th><?php esc_html_e( 'Post Expiration Time', 'wp-user-frontend' ); ?></th>
                                <td>
                                    <select name="post_expiration_settings[expiration_time_value]" id="wpuf-expiration_time_value"
                                        disabled>
                                        <?php
                                        for ( $i = 1; $i <= $time_type_array[ $time_type ]; $i++ ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $i ); ?>" <?php echo $i == $time_value ? 'selected' : ''; ?>><?php echo esc_attr( $i ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <select name="post_expiration_settings[expiration_time_type]" id="wpuf-expiration_time_type"
                                        disabled>
                                        <?php
                                        foreach ( $time_type_array as $each_time_type => $each_time_type_val ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $each_time_type ); ?>" <?php echo $each_time_type == $time_type ? 'selected' : ''; ?>>
                                                <?php echo esc_html( ucfirst( $each_time_type ) ); ?></option>
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
                                $allowed_tax_id_arr = $this->pack_meta_value( $pack_id, '_sub_allowed_term_ids' );

                                if ( ! $allowed_tax_id_arr ) {
                                    $allowed_tax_id_arr = [];
                                }

                                $builtin_taxs = get_taxonomies(
                                    [
                                        '_builtin' => true,
                                    ],
                                    'objects'
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
                                            if ( in_array( (int) $tax_term->term_id, array_map( 'intval', (array) $allowed_tax_id_arr ), true ) ) {
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
                                            if ( in_array( (int) $tax_term->term_id, array_map( 'intval', (array) $allowed_tax_id_arr ), true ) ) {
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
                        <a class="btn button-secondary wpuf-assing-pack-btn wpuf-add-pack"
                            href="#"><?php esc_html_e( 'Assign Package', 'wp-user-frontend' ); ?></a>
                        <a class="btn button-secondary wpuf-assing-pack-btn wpuf-cancel-pack" style="display:none;"
                            href="#"><?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?></a>
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
                            <span
                                class="description"><?php esc_html_e( 'Only non-recurring pack can be assigned', 'wp-user-frontend' ); ?></span>
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
                    <a class="btn button-secondary wpuf-delete-pack-btn" href="javascript:"
                        data-userid="<?php echo esc_attr( $userdata->ID ); ?>"
                        data-packid="<?php echo isset( $user_sub['pack_id'] ) ? esc_attr( $user_sub['pack_id'] ) : ''; ?>"><?php esc_html_e( 'Delete Package', 'wp-user-frontend' ); ?></a>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Update user profile lock
     *
     * @param int $user_id
     */
    public function profile_subscription_update( $user_id ) {
        // Same gate as the details box: users may not hand themselves a pack
        // from their own profile screen (every user has edit_user on itself).
        if ( ! current_user_can( 'edit_users' ) || ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }
        $nonce = isset( $_REQUEST['wpuf-subscription-nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf-subscription-nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'update-profile_' . $user_id ) ) {
            return;
        }

        if ( ! isset( $_POST['pack_id'] ) ) {
            return;
        }

        if ( isset( $_POST['wpuf_profile_mail_noti'] ) ) {
            $wpuf_profile_mail_noti = sanitize_text_field( wp_unslash( $_POST['wpuf_profile_mail_noti'] ) );
            Stores::user_packs()->set_assign_notification( $user_id, $wpuf_profile_mail_noti );
        }

        $pack_id = isset( $_POST['pack_id'] ) ? intval( wp_unslash( $_POST['pack_id'] ) ) : '';
        // The user being saved, never a user id taken from the request.
        $u_id      = (int) $user_id;
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
            $cost           = isset( $pack->meta_value['billing_amount'] ) ? (float) $pack->meta_value['billing_amount'] : 0;
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

    public function packdropdown_without_recurring( $packs, $selected = '' ) {
        $packs = isset( $packs ) ? $packs : [];

        foreach ( $packs as $key => $pack ) {
            $recurring = isset( $pack->meta_value['recurring_pay'] ) ? $pack->meta_value['recurring_pay'] : '';

            if ( wpuf_is_option_on( $recurring ) ) {
                continue;
            }
            ?>
            <option value="<?php echo esc_attr( $pack->ID ); ?>" <?php selected( $selected, $pack->ID ); ?>>
                <?php echo esc_attr( $pack->post_title ); ?></option>
            <?php
        }
    }

    /**
     * Remove the pack of a user (the user profile screen's remove button, REST
     * `wpuf/v1/admin/users/{id}/pack`): the user pack, the PayPal recurring
     * profile, the subscriber row. The caller checks the capability.
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User
     * @param int $pack_id Pack the user had (0: unknown, the subscriber row stays)
     *
     * @return bool Whether the user pack was removed
     */
    public static function remove_user_pack( $user_id, $pack_id = 0 ) {
        $deleted = (bool) Stores::user_packs()->delete( $user_id );

        // A recurring PayPal pack is cancelled at PayPal too (the old call here
        // named a method that no longer exists and fatalled before subscriber_cancel).
        if ( Stores::user_packs()->paypal_subscription_id( $user_id ) ) {
            wpuf()->paypal->subscription_cancel( $user_id );
        }

        if ( $pack_id ) {
            wpuf()->subscription->subscriber_cancel( $user_id, $pack_id );
        }

        return $deleted;
    }

    /**
     * One meta value of a pack through the subscription store ('' when unknown).
     *
     * @since WPUF_SINCE
     *
     * @param int    $pack_id Pack id
     * @param string $key     Meta key
     *
     * @return mixed
     */
    private function pack_meta_value( $pack_id, $key ) {
        $pack = Stores::subscriptions()->read( $pack_id );

        return $pack && isset( $pack['meta'][ $key ] ) ? $pack['meta'][ $key ] : '';
    }
}
