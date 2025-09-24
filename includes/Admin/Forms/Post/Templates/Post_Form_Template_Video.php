<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

use WeDevs\Wpuf\Admin\Forms\Form_Template;

/**
 * Video content submission form template
 */
class Post_Form_Template_Video extends Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = true;
        $this->title       = __( 'Video Content Submission', 'wp-user-frontend' );
        $this->description = __( 'Form for submitting video content with embed links and descriptions.', 'wp-user-frontend' );
        $this->image       = WPUF_ASSET_URI . '/images/templates/video.svg';
        $this->form_fields = [
            [
                'input_type'       => 'text',
                'template'         => 'post_title',
                'required'         => 'yes',
                'label'            => __( 'Video Title', 'wp-user-frontend' ),
                'name'             => 'post_title',
                'is_meta'          => 'no',
                'help'             => '',
                'css'              => '',
                'placeholder'      => __( 'Enter your video\'s main title', 'wp-user-frontend' ),
                'default'          => '',
                'size'             => '40',
                'wpuf_cond'        => $this->conditionals,
                'wpuf_visibility'  => $this->get_default_visibility_prop(),
                'restriction_to'   => 'max',
                'restriction_type' => 'character',
                'width'            => 'large',
                'show_in_post'     => 'yes',
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_content',
                'required'            => 'yes',
                'label'               => __( 'Video Description', 'wp-user-frontend' ),
                'name'                => 'post_content',
                'is_meta'             => 'no',
                'help'                => __( 'Provide a detailed description and any accompanying text for your video.', 'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '8',
                'cols'                => '25',
                'placeholder'         => '',
                'default'             => '',
                'rich'                => 'yes',
                'insert_image'        => 'yes',
                'wpuf_cond'           => $this->conditionals,
                'wpuf_visibility'     => $this->get_default_visibility_prop(),
                'restriction_to'      => 'max',
                'restriction_type'    => 'character',
                'text_editor_control' => [],
                'width'               => 'large',
                'show_in_post'        => 'yes',
            ],
            [
                'input_type'      => 'website_url',
                'template'        => 'website_url',
                'required'        => 'yes',
                'label'           => __( 'Video URL', 'wp-user-frontend' ),
                'name'            => 'video_url',
                'is_meta'         => 'yes',
                'help'            => __( 'Paste the link to your video (e.g., from YouTube or Vimeo). This will be automatically embedded in your post.', 'wp-user-frontend' ),
                'css'             => '',
                'placeholder'     => __( 'https://youtube.com/watch?v=... or https://vimeo.com/...', 'wp-user-frontend' ),
                'default'         => '',
                'size'            => '40',
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
                'show_in_post'    => 'yes',
            ],
            [
                'input_type'      => 'image_upload',
                'template'        => 'featured_image',
                'count'           => '1',
                'required'        => 'yes',
                'label'           => __( 'Set a Thumbnail', 'wp-user-frontend' ),
                'button_label'    => __( 'Upload Thumbnail', 'wp-user-frontend' ),
                'name'            => 'featured_image',
                'is_meta'         => 'no',
                'help'            => __( 'Upload a custom preview image that will be shown before your video plays.', 'wp-user-frontend' ),
                'css'             => '',
                'max_size'        => '1024',
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
                'show_in_post'    => 'yes',
            ],
            [
                'input_type'      => 'taxonomy',
                'template'        => 'taxonomy',
                'required'        => 'yes',
                'label'           => __( 'Video Category', 'wp-user-frontend' ),
                'name'            => 'category',
                'is_meta'         => 'no',
                'help'            => __( 'Choose the best category to organize your video.', 'wp-user-frontend' ),
                'first'           => __( '- Select -', 'wp-user-frontend' ),
                'css'             => '',
                'type'            => 'select',
                'orderby'         => 'name',
                'order'           => 'ASC',
                'exclude_type'    => 'exclude',
                'exclude'         => [],
                'woo_attr'        => 'no',
                'woo_attr_vis'    => 'no',
                'options'         => [],
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
                'show_inline'     => false,
                'show_in_post'     => 'yes',
            ],
            [
                'input_type'      => 'text',
                'template'        => 'post_tags',
                'required'        => 'no',
                'label'           => __( 'Tags (Optional)', 'wp-user-frontend' ),
                'name'            => 'tags',
                'is_meta'         => 'no',
                'help'            => __( 'Add optional keywords that help people find your video. Separate multiple tags with commas.', 'wp-user-frontend' ),
                'css'             => '',
                'placeholder'     => __( 'e.g., tutorial, vlog, review, funny', 'wp-user-frontend' ),
                'default'         => '',
                'size'            => '40',
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
                'show_in_post'    => 'yes',
            ],
            [
                'input_type'       => 'text',
                'template'         => 'text_field',
                'required'         => 'no',
                'label'            => __( 'Your Name / Credit', 'wp-user-frontend' ),
                'name'             => 'video_credit',
                'is_meta'          => 'yes',
                'help'             => '',
                'css'              => '',
                'placeholder'      => __( 'How you\'d like to be credited', 'wp-user-frontend' ),
                'default'          => '',
                'size'             => '40',
                'wpuf_cond'        => $this->conditionals,
                'wpuf_visibility'  => $this->get_default_visibility_prop(),
                'width'            => 'large',
                'show_in_post'     => 'yes',
            ],
        ];

        $this->form_settings = [
            'post_type'                  => 'post',
            'expiration_settings'        => [],
            'post_status'                => 'draft',
            'default_cat'                => '-1',
            'post_permission'            => 'guest_post',
            'guest_post'                 => 'true',
            'guest_details'              => 'on',
            'name_label'                 => 'Name',
            'email_label'                => 'E-Mail',
            'guest_email_verify'         => 'on',
            'message_restrict'           => __( 'This page is restricted. Please {login} / {register} to view this page.', 'wp-user-frontend' ),
            'redirect_to'                => 'same',
            'comment_status'             => 'closed',
            'submit_text'                => __( 'Submit Video', 'wp-user-frontend' ),
            'submit_button_cond'         => [
                'condition_status' => 'no',
                'cond_logic'       => 'any',
                'conditions'       => [
                    [
                        'name'             => '',
                        'operator'         => '=',
                        'option'           => '',
                    ],
                ],
            ],
            'draft_post'                 => 'true',
            'edit_post_status'           => 'draft',
            'edit_redirect_to'           => 'same',
            'update_message'             => __( 'Your video has been updated successfully.', 'wp-user-frontend' ),
            'edit_url'                   => '',
            'update_text'                => __( 'Update Video', 'wp-user-frontend' ),
            'form_template'              => 'post_form_template_video',
            'notification'               => [
                'new'                        => 'on',
                'new_to'                     => get_option( 'admin_email' ),
                'new_subject'                => 'New video submission',
                'new_body'                   => 'Hi,
A new video has been submitted to your site {sitename} ({siteurl}).

Here are the details:
Video Title: {post_title}
Video Description: {post_content}
Video URL: {video_url}
Credit: {video_credit}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}',
                'edit'                       => 'on',
                'edit_to'                    => get_option( 'admin_email' ),
                'edit_subject'               => 'Video has been updated',
                'edit_body'                  => 'Hi,
The video "{post_title}" has been updated.

Here are the details:
Video Title: {post_title}
Video Description: {post_content}
Video URL: {video_url}
Credit: {video_credit}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}'
            ],
        ];
    }
}