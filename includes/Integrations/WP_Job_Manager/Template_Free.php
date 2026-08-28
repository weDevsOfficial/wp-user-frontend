<?php
// DESCRIPTION: WPUF form template for creating WP Job Manager `job_listing`
// posts. Ships the 11-field free submission form matching WPJM core meta keys.

namespace WeDevs\Wpuf\Integrations\WP_Job_Manager;

use WeDevs\Wpuf\Admin\Forms\Form_Template;

/**
 * "Post a Job" form template (Free)
 *
 * @since WPUF_SINCE
 */
class Template_Free extends Form_Template {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->enabled       = class_exists( 'WP_Job_Manager' );
        $this->title         = __( 'Post a Job', 'wp-user-frontend' );
        $this->description   = __(
            'Form for submitting jobs. The WP Job Manager plugin is required.',
            'wp-user-frontend'
        );
        $this->image         = WPUF_ASSET_URI . '/images/templates/post.svg';
        $this->form_fields   = $this->get_form_fields();
        $this->form_settings = $this->get_form_settings();
    }

    /**
     * Get form fields
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_form_fields() {
        $form_fields = [
            [
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => __( 'Job Title', 'wp-user-frontend' ),
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => __( 'Enter the job title', 'wp-user-frontend' ),
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_content',
                'required'            => 'yes',
                'label'               => __( 'Description', 'wp-user-frontend' ),
                'name'                => 'post_content',
                'is_meta'             => 'no',
                'help'                => __( 'Full job description', 'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '5',
                'cols'                => '25',
                'placeholder'         => '',
                'default'             => '',
                'rich'                => 'yes',
                'insert_image'        => 'yes',
                'word_restriction'    => '',
                'wpuf_cond'           => $this->conditionals,
                'text_editor_control' => [],
                'id'                  => uniqid( 'wpuf_', true ),
                'is_new'              => true,
            ],
            [
                'input_type'  => 'text',
                'template'    => 'text_field',
                'required'    => 'no',
                'label'       => __( 'Location', 'wp-user-frontend' ),
                'name'        => '_job_location',
                'is_meta'     => 'yes',
                'help'        => __( 'e.g., "London, UK" or "Remote"', 'wp-user-frontend' ),
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type' => 'checkbox',
                'template'   => 'checkbox_field',
                'required'   => 'no',
                'label'      => __( 'Remote Position', 'wp-user-frontend' ),
                'name'       => '_remote_position',
                'is_meta'    => 'yes',
                'options'    => [
                    '1' => __( 'This is a remote position', 'wp-user-frontend' ),
                ],
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid( 'wpuf_', true ),
                'is_new'     => true,
            ],
            [
                'input_type' => 'taxonomy',
                'template'   => 'taxonomy',
                'required'   => 'no',
                'label'      => __( 'Job Type', 'wp-user-frontend' ),
                'name'       => 'job_listing_type',
                'is_meta'    => 'no',
                'type'       => 'select',
                'tax'        => 'job_listing_type',
                'exclude_type' => 'exclude',
                'exclude'    => '',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'first'      => __( '— Select —', 'wp-user-frontend' ),
                'woo_attr'   => 'no',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid( 'wpuf_', true ),
                'is_new'     => true,
            ],
            [
                'input_type'  => 'text',
                'template'    => 'text_field',
                'required'    => 'yes',
                'label'       => __( 'Application Email or URL', 'wp-user-frontend' ),
                'name'        => '_application',
                'is_meta'     => 'yes',
                'help'        => __( 'Email or URL applicants should use to apply', 'wp-user-frontend' ),
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type'  => 'text',
                'template'    => 'text_field',
                'required'    => 'yes',
                'label'       => __( 'Company Name', 'wp-user-frontend' ),
                'name'        => '_company_name',
                'is_meta'     => 'yes',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type' => 'url',
                'template'   => 'website_url',
                'required'   => 'no',
                'label'      => __( 'Company Website', 'wp-user-frontend' ),
                'name'       => '_company_website',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'size'       => 40,
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid( 'wpuf_', true ),
                'is_new'     => true,
            ],
            [
                'input_type'  => 'text',
                'template'    => 'text_field',
                'required'    => 'no',
                'label'       => __( 'Company Tagline', 'wp-user-frontend' ),
                'name'        => '_company_tagline',
                'is_meta'     => 'yes',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type'  => 'text',
                'template'    => 'text_field',
                'required'    => 'no',
                'label'       => __( 'Twitter Username', 'wp-user-frontend' ),
                'name'        => '_company_twitter',
                'is_meta'     => 'yes',
                'help'        => __( 'Without the @ symbol', 'wp-user-frontend' ),
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid( 'wpuf_', true ),
                'is_new'      => true,
            ],
            [
                'input_type' => 'url',
                'template'   => 'website_url',
                'required'   => 'no',
                'label'      => __( 'Company Video', 'wp-user-frontend' ),
                'name'       => '_company_video',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'size'       => 40,
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid( 'wpuf_', true ),
                'is_new'     => true,
            ],
        ];

        /**
         * Filter the WP Job Manager free-template form fields.
         *
         * @since WPUF_SINCE
         *
         * @param array         $form_fields Field definitions.
         * @param Template_Free $template    The template instance.
         */
        return apply_filters( 'wpuf_wpjm_free_form_fields', $form_fields, $this );
    }

    /**
     * Get form settings
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_form_settings() {
        $post_status = get_option( 'job_manager_submission_requires_approval' )
            ? 'pending'
            : 'publish';

        return [
            'post_type'        => 'job_listing',
            'post_status'      => $post_status,
            'default_cat'      => '-1',
            'guest_post'       => 'false',
            'message_restrict' => __(
                'This page is restricted. Please Log in / Register to view this page.',
                'wp-user-frontend'
            ),
            'redirect_to'      => 'post',
            'comment_status'   => 'open',
            'submit_text'      => __( 'Submit Job', 'wp-user-frontend' ),
            'edit_post_status' => $post_status,
            'edit_redirect_to' => 'same',
            'update_message'   => sprintf(
                // translators: %1$s is opening link tag, %2$s is closing link tag
                __(
                    'Job has been updated successfully. %1$sView job%2$s',
                    'wp-user-frontend'
                ),
                '<a target="_blank" href="{link}">',
                '</a>'
            ),
            'edit_url'         => '',
            'update_text'      => __( 'Update Job', 'wp-user-frontend' ),
            'form_template'    => 'post_form_template_wp_job_manager',
            'notification'     => [
                'new'          => 'on',
                'new_to'       => get_option( 'admin_email' ),
                'new_subject'  => __( 'A new job has been submitted', 'wp-user-frontend' ),
                'new_body'     => __(
                    "Hi,\nA new job has been submitted on your site {sitename} ({siteurl}).\n\nJob Title: {post_title}\nDescription: {post_content}\nAuthor: {author}\nJob URL: {permalink}\nEdit URL: {editlink}",
                    'wp-user-frontend'
                ),
                'edit'         => 'on',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => __( 'A job has been updated', 'wp-user-frontend' ),
                'edit_body'    => __(
                    "Hi,\nThe job \"{post_title}\" has been updated.\n\nJob URL: {permalink}\nEdit URL: {editlink}",
                    'wp-user-frontend'
                ),
            ],
        ];
    }
}
