<?php
/**
 * Form Settings Preview (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

/**
 * Pro form settings previewed in the builder: locked rows on the settings tabs, the modules menu, the taxonomy restriction rows and the option data button.
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Form_Settings_Preview extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    /**
     * Add general settings pro fields preview
     *
     * @since 4.1.0
     *
     * @param array $general_settings
     *
     * @return array
     */
    public function form_settings_preview_general( $general_settings ) {
        $general_settings['section']['before_post_settings']['pro_preview']['fields'] = [
            'enable_multistep'           => [
                'label'     => __( 'Enable Multi-Step', 'wp-user-frontend' ),
                'type'      => 'toggle',
                'value'     => 'on',
                'help_text' => __(
                    'If checked, form will be displayed in frontend in multiple steps.', 'wp-user-frontend'
                ),
                'link'      => esc_url_raw(
                    'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/how-to-add-multi-step-form/'
                ),
            ],
            'multistep_progressbar_type' => [
                'label'     => __( 'Multistep Progressbar Type', 'wp-user-frontend' ),
                'type'      => 'select',
                'help_text' => __( 'Choose how you want the progressbar', 'wp-user-frontend' ),
                'options'   => [
                    'progressive'  => __( 'Progressbar', 'wp-user-frontend' ),
                    'step_by_step' => __( 'Step by Step', 'wp-user-frontend' ),
                ],
            ],
            'ms_ac_txt_color'            => [
                'label'     => __( 'Active Text Color', 'wp-user-frontend' ),
                'type'      => 'color-picker',
                'help_text' => __( 'Text color for active step.', 'wp-user-frontend' ),
                'default'   => '#fff',
            ],
            'ms_active_bgcolor'          => [
                'label'     => __( 'Active Background Color', 'wp-user-frontend' ),
                'type'      => 'color-picker',
                'help_text' => __( 'Background color for progressbar or active step.', 'wp-user-frontend' ),
                'default'   => '#00a0d2',
            ],
            'ms_bgcolor'                 => [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => 'color-picker',
                'help_text' => __( 'Background color for normal steps.', 'wp-user-frontend' ),
                'default'   => '#E4E4E4',
            ],
        ];

        return $general_settings;
    }

    /**
     * Add notification settings pro fields preview
     *
     * @since 4.1.0
     *
     * @param array $notification_settings
     *
     * @return array
     */
    public function form_settings_preview_notification( $notification_settings ) {
        $notification_settings['section']['update_post']['pro_preview']['fields'] = [
            'notification_edit'         => [
                'label' => __( 'Enable Update Post Notification', 'wp-user-frontend' ),
                'type'  => 'toggle',
                'value' => 'on',
            ],
            'notification_edit_to'      => [
                'label' => __( 'To', 'wp-user-frontend' ),
                'type'  => 'text',
                'value' => get_option( 'admin_email' ),
            ],
            'notification_edit_subject' => [
                'label' => __( 'Subject', 'wp-user-frontend' ),
                'type'  => 'text',
                'value' => __( 'A post has been edited', 'wp-user-frontend' ),
            ],
            'notification_edit_body'    => [
                'label'     => __( 'Email Body', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'value'     => "Hi Admin, \r\n\r\nThe post \"{post_title}\" has been updated. \r\n\r\nHere is the details: \r\nPost Title: {post_title} \r\nContent: {post_content} \r\nAuthor: {author} \r\nPost URL: {permalink} \r\nEdit URL: {editlink}",
                'long_help' => '<h4 class="wpuf-m-0">You may use in to, subject & message:</h4>
                                         <p class="wpuf-leading-8">
                                         <span data-clipboard-text="{post_title}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_title}</span>
                                         <span data-clipboard-text="{post_content}" class="wpuf-post-content wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_content}</span>
                                         <span data-clipboard-text="{post_excerpt}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_excerpt}</span>
                                         <span data-clipboard-text="{tags}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{tags}</span>
                                         <span data-clipboard-text="{category}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{category}</span>
                                         <span data-clipboard-text="{author}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author}</span>
                                         <span data-clipboard-text="{author_email}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author_email}</span>
                                         <span data-clipboard-text="{author_bio}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author_bio}</span>
                                         <span data-clipboard-text="{sitename}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{sitename}</span>
                                         <span data-clipboard-text="{siteurl}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{siteurl}</span>
                                         <span data-clipboard-text="{permalink}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{permalink}</span>
                                         <span data-clipboard-text="{editlink}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{editlink}</span>
                                         <span class="wpuf-pill-green">{custom_{NAME_OF_CUSTOM_FIELD}}</span>
                                         e.g: <span class="wpuf-pill-green">{custom_website_url}</span> for <i>website_url</i> meta field</p>',
            ],
        ];

        return $notification_settings;
    }

    /**
     * Add advance settings pro fields preview
     *
     * @since 4.1.0
     *
     * @param array $advanced_settings
     *
     * @return array
     */
    public function form_settings_preview_advanced( $advanced_settings ) {
        $advanced_settings['pro_preview']['fields'] = [
            'conditional_logic' => [
                'label' => __( 'Conditional Logic on Submit', 'wp-user-frontend' ),
                'type'  => 'toggle',
                'value' => 'off',
            ],
        ];

        return $advanced_settings;
    }

    /**
     * Add notification settings pro fields preview
     *
     * @since 4.1.0
     *
     * @param array $display_settings
     *
     * @return array
     */
    public function form_settings_preview_display( $display_settings ) {
        return $display_settings;
    }

    /**
     * Add post expiration settings pro fields preview
     *
     * @since 4.1.0
     *
     * @param array $expiration_settings
     *
     * @return array
     */
    public function form_settings_preview_post_expiration( $expiration_settings ) {
        $expiration_settings['pro_preview']['fields'] = [
            'enable_post_expiration'    => [
                'label' => __( 'Enable Post Expiration', 'wp-user-frontend' ),
                'type'  => 'toggle',
                'value' => 'on',
            ],
            'inline_fields'             => [
                'fields' => [
                    'expiration_time_value' => [
                        'label' => __( 'Post Expiration Time', 'wp-user-frontend' ),
                        'type'  => 'number',
                        'value' => '7',
                    ],
                    'expiration_time_type'  => [
                        'label'   => __( 'Duration Type', 'wp-user-frontend' ),
                        'type'    => 'select',
                        'options' => [
                            'day'   => __( 'Day(s)', 'wp-user-frontend' ),
                            'week'  => __( 'Week(s)', 'wp-user-frontend' ),
                            'month' => __( 'Month(s)', 'wp-user-frontend' ),
                        ],
                    ],
                ],
            ],
            'enable_mail_after_expired' => [
                'label'     => __( 'Send post expiration email to author', 'wp-user-frontend' ),
                'type'      => 'checkbox',
                'help_text' => __( 'Verification of email addresses will be mandatory', 'wp-user-frontend' ),
            ],
            'post_expiration_message'   => [
                'label' => __( 'Form Expired Message', 'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
        ];

        return $expiration_settings;
    }

    /**
     * Add module settings pro fields preview
     *
     * @since 4.1.0
 *
     * @param array $settings
     *
     * @return array
     */
    public function form_settings_modules( $settings ) {
        $settings['modules'] = [
            'label'     => __( 'Modules', 'wp-user-frontend' ),
            'icon'      => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.42857 9.75L2.25 12L6.42857 14.25M6.42857 9.75L12 12.75L17.5714 9.75M6.42857 9.75L2.25 7.5L12 2.25L21.75 7.5L17.5714 9.75M17.5714 9.75L21.75 12L17.5714 14.25M17.5714 14.25L21.75 16.5L12 21.75L2.25 16.5L6.42857 14.25M17.5714 14.25L12 17.25L6.42857 14.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
        ];

        return $settings;
    }

    /**
     * Add taxonomy restriction options
     *
     * @since 4.0.11
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_taxonomy_restriction_section( $sections ) {
        $sections['advanced_configuration'][] = [
            'id'        => 'taxonomy_restriction',
            'label'     => __( 'Taxonomy Access', 'wp-user-frontend' ),
            'sub_label' => __( '(Control user access to specific taxonomies)', 'wp-user-frontend' ),
            'is_pro'    => true,
        ];

        return $sections;
    }

    /**
     * Add taxonomy restriction fields
     *
     * @since 4.0.11
     *
     * @param array $fields
     *
     * @return array
     */
    public function add_taxonomy_restriction_fields( $fields ) {
        $cts = get_taxonomies( [], 'objects' );

        foreach ( $cts as $ct ) {
            if ( ! is_taxonomy_hierarchical( $ct->name ) ) {
                continue;
            }

            $fields['advanced_configuration']['taxonomy_restriction'][ $ct->name ] = [
                'id'          => $ct->name,
                'name'        => $ct->name,
                'type'        => 'multi-select',
                'label'       => $ct->label,
                'term_fields' => [],
                'is_pro'      => true,
            ];
        }

        return $fields;
    }
}
