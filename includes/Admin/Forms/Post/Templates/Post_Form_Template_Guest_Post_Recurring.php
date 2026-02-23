<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

use WeDevs\Wpuf\Admin\Forms\Form_Template;

/**
 * Guest post submission form with mandatory recurring subscription
 *
 * @since 4.2.2
 */
class Post_Form_Template_Guest_Post_Recurring extends Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = true;
        $this->title       = __( 'Guest Post (Recurring Subscription)', 'wp-user-frontend' );
        $this->description = __( 'Accept guest post submissions with a mandatory recurring subscription. Collects title, content, excerpt, featured image, category, and tags.', 'wp-user-frontend' );
        $this->image       = WPUF_ASSET_URI . '/images/templates/guest-post-recurring.svg';

        $this->form_fields = [
            [
                'input_type'       => 'text',
                'template'         => 'post_title',
                'required'         => 'yes',
                'label'            => __( 'Post Title', 'wp-user-frontend' ),
                'name'             => 'post_title',
                'is_meta'          => 'no',
                'help'             => __( 'Choose a compelling and descriptive title for your guest post.', 'wp-user-frontend' ),
                'css'              => '',
                'placeholder'      => __( 'Enter your article title here', 'wp-user-frontend' ),
                'default'          => '',
                'size'             => '40',
                'wpuf_cond'        => $this->conditionals,
                'wpuf_visibility'  => $this->get_default_visibility_prop(),
                'restriction_to'   => 'max',
                'restriction_type' => 'character',
                'width'            => 'large',
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_content',
                'required'            => 'yes',
                'label'               => __( 'Post Content', 'wp-user-frontend' ),
                'name'                => 'post_content',
                'is_meta'             => 'no',
                'help'                => __( 'Provide the full content of your guest post. Ensure it\'s well-written and relevant.', 'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '5',
                'cols'                => '25',
                'placeholder'         => __( 'Write your article content here', 'wp-user-frontend' ),
                'default'             => '',
                'rich'                => 'yes',
                'insert_image'        => 'yes',
                'wpuf_cond'           => $this->conditionals,
                'wpuf_visibility'     => $this->get_default_visibility_prop(),
                'restriction_to'      => 'max',
                'restriction_type'    => 'character',
                'text_editor_control' => [],
                'width'               => 'large',
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_excerpt',
                'required'            => 'no',
                'label'               => __( 'Post Excerpt', 'wp-user-frontend' ),
                'name'                => 'post_excerpt',
                'is_meta'             => 'no',
                'help'                => __( 'A short summary of your post, usually displayed on archive pages. Keep it concise.', 'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '5',
                'cols'                => '25',
                'placeholder'         => __( 'Enter a brief summary here', 'wp-user-frontend' ),
                'default'             => '',
                'rich'                => 'no',
                'wpuf_cond'           => $this->conditionals,
                'wpuf_visibility'     => $this->get_default_visibility_prop(),
                'restriction_to'      => 'max',
                'restriction_type'    => 'character',
                'text_editor_control' => [],
                'width'               => 'large',
            ],
            [
                'input_type'      => 'image_upload',
                'template'        => 'featured_image',
                'count'           => '1',
                'required'        => 'yes',
                'label'           => __( 'Featured Image', 'wp-user-frontend' ),
                'button_label'    => __( 'Featured Image', 'wp-user-frontend' ),
                'name'            => 'featured_image',
                'is_meta'         => 'no',
                'help'            => __( 'Select an image that best represents your article. This will be the main image for your post.', 'wp-user-frontend' ),
                'css'             => '',
                'max_size'        => '1024',
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
            ],
            [
                'input_type'      => 'taxonomy',
                'template'        => 'taxonomy',
                'required'        => 'yes',
                'label'           => __( 'Category', 'wp-user-frontend' ),
                'name'            => 'category',
                'is_meta'         => 'no',
                'help'            => __( 'Choose the most relevant category for your guest post.', 'wp-user-frontend' ),
                'first'           => __( 'Select a category', 'wp-user-frontend' ),
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
            ],
            [
                'input_type'      => 'text',
                'template'        => 'post_tags',
                'required'        => 'no',
                'label'           => __( 'Tags', 'wp-user-frontend' ),
                'name'            => 'tags',
                'is_meta'         => 'no',
                'help'            => __( 'Add relevant keywords or phrases that describe your post.', 'wp-user-frontend' ),
                'css'             => '',
                'placeholder'     => __( 'Enter tags (comma-separated)', 'wp-user-frontend' ),
                'default'         => '',
                'size'            => '40',
                'wpuf_cond'       => $this->conditionals,
                'wpuf_visibility' => $this->get_default_visibility_prop(),
                'width'           => 'large',
            ],
        ];

        $this->form_settings = [
            'post_type'             => 'post',
            'post_status'           => 'draft',
            'default_cat'           => '-1',
            'post_permission'       => 'guest_post',
            'guest_post'            => 'true',
            'guest_details'         => 'on',
            'name_label'            => 'Name',
            'email_label'           => 'E-Mail',
            'guest_email_verify'    => 'on',
            'message_restrict'      => __( 'This page is restricted. Please {login} / {register} to view this page.', 'wp-user-frontend' ),
            'redirect_to'           => 'post',
            'comment_status'        => 'open',
            'show_form_title'       => 'true',
            'form_description'      => __( 'Submit your guest post (this submission is under your active subscription plan). Include your article content, tags, excerpt, and featured image.', 'wp-user-frontend' ),
            'submit_text'           => __( 'Submit Guest Post', 'wp-user-frontend' ),
            'submit_button_cond'    => [
                'condition_status' => 'no',
                'cond_logic'       => 'any',
                'conditions'       => [
                    [
                        'name'     => '',
                        'operator' => '=',
                        'option'   => '',
                    ],
                ],
            ],
            'draft_post'            => 'true',
            'edit_post_status'      => 'draft',
            'edit_redirect_to'      => 'same',
            'update_message'        => __( 'Post has been updated successfully. <a target="_blank" href="{link}">View post</a>', 'wp-user-frontend' ),
            'edit_url'              => '',
            'update_text'           => __( 'Update Post', 'wp-user-frontend' ),
            'payment_options'       => 'true',
            'choose_payment_option' => 'force_pack_purchase',
            'force_pack_purchase'   => 'true',
            'enable_pay_per_post'   => 'false',
            'fallback_ppp_enable'   => 'on',
            'use_theme_css'         => 'wpuf-style',
            'form_layout'           => 'layout1',
            'form_template'         => 'post_form_template_guest_post_recurring',
            'notification'          => [
                'new'          => 'on',
                'new_to'       => get_option( 'admin_email' ),
                'new_subject'  => 'New guest post submission (subscription)',
                'new_body'     => 'Hi,
A new guest post has been submitted to your site {sitename} ({siteurl}).

Here are the details:
Post Title: {post_title}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}',
                'edit'         => 'on',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => 'Guest post has been updated',
                'edit_body'    => 'Hi,
The guest post "{post_title}" has been updated.

Here are the details:
Post Title: {post_title}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}',
            ],
        ];
    }
}
