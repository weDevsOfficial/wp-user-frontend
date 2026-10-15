<?php
/**
 * Pack editor schema
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Admin_Subscription;
use WeDevs\Wpuf\Frontend\Payment;

/**
 * The subscription pack editor's schema: sections, sub sections, fields (with
 * their Pro previews), dependent fields, the post types a pack can allow and
 * the third-party CPT options filter. Read by the React subscriptions screen
 * (`enqueue_admin_scripts()`) and the classic pack metabox.
 *
 * @since WPUF_SINCE Moved out of Admin_Subscription, which keeps the hooks and delegates.
 */
class Pack_Fields {

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
            'wpuf_subscription_section_details',
            [
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
            'wpuf_subscription_section_payment',
            [
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
                                ),
                                admin_url( 'admin.php?page=wpuf-settings#wpuf_payment' )
                            ),
                        ],
                    ],
                ],
            ]
        );

        $advanced = apply_filters(
            'wpuf_subscription_section_advanced',
            [
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
            'wpuf_subscription_overview_fields',
            [
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
            'wpuf_subscription_access_fields',
            [
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
                    'sort_order'   => [
                        'id'          => 'sort-order',
                        'name'        => 'sort_order',
                        'db_key'      => '_sort_order',
                        'db_type'     => 'meta',
                        'type'        => 'input-number',
                        'label'       => __( 'Sort Order', 'wp-user-frontend' ),
                        'tooltip'     => __( 'Plans with lower numbers appear first on the frontend, keep default value 1. Cannot keep it empty.', 'wp-user-frontend' ),
                        'placeholder' => __( 'Enter sort order', 'wp-user-frontend' ),
                        'is_required' => true,
                        'default'     => '1',
                        'validation'  => [
                            'required' => true,
                            'min'      => 1,
                        ],
                    ],
                    'publish_time' => [
                        'id'      => 'publish-time',
                        'name'    => 'publish-time',
                        'db_key'  => 'post_date',
                        'db_type' => 'post',
                        'type'    => 'time-date',
                        'label'   => __( 'Publish Time', 'wp-user-frontend' ),
                        'tooltip' => __( 'Specify the time when you want the subscription to be published', 'wp-user-frontend' ),
                        'default' => wpuf_current_datetime()->format( 'Y-m-d H:i:s' ),
                    ],
                ],
            ]
        );
        $expiration         = apply_filters(
            'wpuf_subscription_expiration_fields',
            [
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
                            'Send an e-mail to the author after exceeding post expiration time',
                            'wp-user-frontend'
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
                            'If enabled, number of posts will be restored if the post is deleted.',
                            'wp-user-frontend'
                        ),
                        'default' => false,
                        'is_pro'  => true,
                    ],
                ],
            ]
        );
        $payment            = apply_filters(
            'wpuf_subscription_payment_fields',
            [
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
                                'id'      => 'wpuf-expiration-number',
                                'name'    => 'wpuf-expiration-number',
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
                            'Set the duration for the subscription to remain active before expiring. Enter -1 for no expiration',
                            'wp-user-frontend'
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
            'wpuf_subscription_content_limits_fields',
            [
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
            'wpuf_subscription_design_elements_fields',
            [
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
            'wpuf_subscription_additional_fields',
            [
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
                'expiration_time'    => true,
                'post_status'        => true,
                'send_mail'          => true,
                'expiration_message' => true,
            ],
            'send_mail'        => [
                'expiration_message' => true,
            ],
            'enable_recurring' => [
                'payment_cycle' => true,
                'stop_cycle'    => true,
                'billing_limit' => true,
                'trial'         => true,
                'trial_period'  => true,
                'billing_cycle' => true,
                'expire_in'     => false,
            ],
            'stop_cycle'       => [
                'billing_limit' => true,
            ],
            'trial'            => [
                'trial_period' => true,
            ],
        ];

        return apply_filters( 'wpuf_subscriptions_dependent_fields', $fields );
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
                    <th><label
                            for="wpuf-<?php echo esc_attr( $key ); ?>"><?php printf( 'Number of %s', esc_html( $post_type_object->label ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" size="20" style="" id="wpuf-<?php echo esc_attr( $key ); ?>"
                            value="<?php echo intval( $name ); ?>" name="post_type_name[<?php echo esc_attr( $key ); ?>]" />
                        <div><span
                                class="description"><span><?php printf( 'How many %s the user can list with this pack? Enter <strong>-1</strong> for unlimited.', esc_html( $key ) ); ?></span></span>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }

        return ob_get_clean();
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
                    'tooltip'       => sprintf(
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
}
