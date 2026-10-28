<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Platform\Tools\ToolsService;

/**
 * Manage Import Export
 *
 * @since 2.2
 */
class Admin_Tools {

    public function __construct() {
        add_filter( 'upload_mimes', [ $this, 'add_json_mime_type' ] );
    }

    /**
     * Import json file into database
     *
     * @param array $file
     *
     * @return bool
     */
    public static function import_json_file( $file ) {
        $forms = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

        return wpuf()->platform()->get( ToolsService::class )->import_forms( is_array( $forms ) ? $forms : [] );
    }

    /**
     * Get all available shortcodes organized by category
     *
     * @since 4.2.9
     *
     * @return array
     */
    public function get_all_shortcodes() {
        $shortcodes = [
            'account' => [
                'title'       => __( 'Account & Profile', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for user account management, login, and registration.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_account]',
                        'description' => __( 'Displays the user account page with profile, posts, and subscription info.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf_dashboard]',
                        'description' => __( 'Shows the frontend dashboard where users can view and manage their posts.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_dashboard post_type="post"]',
                    ],
                    [
                        'code'        => '[wpuf_editprofile]',
                        'description' => __( 'Displays the profile edit form for logged-in users.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf-login]',
                        'description' => __( 'Displays a login form for users.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-login redirect="https://example.com"]',
                    ],
                    [
                        'code'        => '[wpuf-registration]',
                        'description' => __( 'Displays a registration form. Use id attribute to specify a form.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-registration id="123"]',
                    ],
                    [
                        'code'        => '[wpuf_profile]',
                        'description' => __( 'Displays a profile form for users to update their profile. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_profile id="123"]',
                        'pro'         => true,
                    ],
                ],
            ],
            'forms' => [
                'title'       => __( 'Post Forms', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for displaying and editing post submission forms.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_form]',
                        'description' => __( 'Displays a post submission form. Use id attribute to specify which form.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_form id="123"]',
                    ],
                    [
                        'code'        => '[wpuf_edit]',
                        'description' => __( 'Allows users to edit their submitted posts.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                ],
            ],
            'subscription' => [
                'title'       => __( 'Subscription & Payments', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for subscription packs and user subscription information.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_sub_pack]',
                        'description' => __( 'Displays all available subscription packs/pricing plans.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_sub_pack]',
                    ],
                    [
                        'code'        => '[wpuf_sub_info]',
                        'description' => __( 'Shows the current user\'s subscription information and status.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                ],
            ],
            'user_directory' => [
                'title'       => __( 'User Directory', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for displaying and managing user directories.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf-edit-users]',
                        'description' => __( 'Displays a list of users that can be edited (admin only).', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf_user_listing]',
                        'description' => __( 'Displays a user directory listing with search and filters. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_user_listing id="123"]',
                        'pro'         => true,
                    ],
                    [
                        'code'        => '[wpuf_user_listing_id]',
                        'description' => __( 'Displays a single user profile from the directory. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_user_listing_id id="123"]',
                        'pro'         => true,
                    ],
                ],
            ],
            'content_restriction' => [
                'title'       => __( 'Content Restriction (Pro)', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for restricting content based on subscriptions or user roles.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_restrict]',
                        'description' => __( 'Restricts content inside the shortcode to specific subscription packs.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_restrict pack="1,2"]Content here[/wpuf_restrict]',
                        'pro'         => true,
                    ],
                    [
                        'code'        => '[wpuf_partial_restriction]',
                        'description' => __( 'Shows partial content with a message to subscribe for full access.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_partial_restriction]',
                        'pro'         => true,
                    ],
                ],
            ],
            'utility' => [
                'title'       => __( 'Utility Shortcodes', 'wp-user-frontend' ),
                'description' => __( 'Helper shortcodes for displaying meta fields and other utilities.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf-meta]',
                        'description' => __( 'Displays a custom field/meta value. Use name attribute to specify the meta key.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-meta name="my_custom_field"]',
                    ],
                    [
                        'code'        => '[wpuf_qr]',
                        'description' => __( 'Displays a QR code for a field value. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_qr]',
                        'pro'         => true,
                    ],
                ],
            ],
        ];

        /**
         * Filter the shortcodes list for the tools page
         *
         * @since 4.2.9
         *
         * @param array $shortcodes The shortcodes array organized by category
         */
        return apply_filters( 'wpuf_tools_shortcodes_list', $shortcodes );
    }

    /**
     * Add json file mime type to upload in WP Media
     *
     * @since 3.2.0
     *
     * @param array $mime_types
     *
     * @return array
     */
    public function add_json_mime_type( $mime_types ) {
        $mime_types['json'] = 'application/json';

        return $mime_types;
    }

    /**
     * Delete all posts by a post type
     *
     * @param string $post_type
     *
     * @return void
     */
    public function delete_post_type( $post_type ) {
        wpuf()->platform()->get( ToolsService::class )->delete_post_type( $post_type );
    }
}
