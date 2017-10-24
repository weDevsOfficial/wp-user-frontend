<?php

/**
 * Normal post form
 */
class WPUF_Post_Form_Template_Post extends WPUF_Post_Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = true;
        $this->title       = __( 'Post Form', 'wpuf' );
        $this->description = __( 'Form for creating a blog post.', 'wpuf' );
        $this->image       = WPUF_ASSET_URI . '/images/templates/post.png';
        $this->form_fields = array(
            array(
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => 'Post Title',
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => 'Please enter your post name',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals
            ),
            array(
                'input_type'   => 'taxonomy',
                'template'     => 'taxonomy',
                'required'     => 'yes',
                'label'        => 'Category',
                'name'         => 'category',
                'is_meta'      => 'no',
                'help'         => 'Select a category for your post',
                'css'          => '',
                'type'         => 'select',
                'orderby'      => 'name',
                'order'        => 'ASC',
                'exclude_type' => 'exclude',
                'exclude'      => '',
                'woo_attr'     => 'no',
                'woo_attr_vis' => 'no',
                'options'      => array(),
                'wpuf_cond'    => $this->conditionals
            ),
            array(
                'input_type'       => 'textarea',
                'template'         => 'post_content',
                'required'         => 'yes',
                'label'            => 'Post description',
                'name'             => 'post_content',
                'is_meta'          => 'no',
                'help'             => 'Write the full description of your Post',
                'css'              => '',
                'rows'             => '5',
                'cols'             => '25',
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'yes',
                'insert_image'     => 'yes',
                'word_restriction' => '',
                'wpuf_cond'        => $this->conditionals
            ),
            array(
                'input_type' => 'image_upload',
                'template'   => 'featured_image',
                'count'      => '1',
                'required'   => 'yes',
                'label'      => 'Featured Image',
                'name'       => 'featured_image',
                'is_meta'    => 'no',
                'help'       => 'Upload the main image of your post',
                'css'        => '',
                'max_size'   => '1024',
                'wpuf_cond'  => $this->conditionals
            ),
            array(
                'input_type'  => 'textarea',
                'template'    => 'post_excerpt',
                'required'    => 'no',
                'label'       => 'Excerpt',
                'name'        => 'post_excerpt',
                'is_meta'     => 'no',
                'help'        => 'Provide a short description of this post (optional)',
                'css'         => '',
                'rows'        => '5',
                'cols'        => '25',
                'placeholder' => '',
                'default'     => '',
                'rich'        => 'no',
                'wpuf_cond'   => $this->conditionals
            ),
            array(
                'input_type'  => 'text',
                'template'    => 'post_tags',
                'required'    => 'no',
                'label'       => 'Tags',
                'name'        => 'tags',
                'is_meta'     => 'no',
                'help'        => 'Separate tags with commas.',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals
            ),
        );

        $this->form_settings = array (
            'post_type'                  => 'post',
            'post_status'                => 'publish',
            'default_cat'                => '-1',
            'guest_post'                 => 'false',
            'message_restrict'           => 'This page is restricted. Please Log in / Register to view this page.',
            'redirect_to'                => 'post',
            'comment_status'             => 'open',
            'submit_text'                => 'Create Post',
            'edit_post_status'           => 'publish',
            'edit_redirect_to'           => 'same',
            'update_message'             => 'Post has been updated successfully. <a target="_blank" href="%link%">View post</a>',
            'edit_url'                   => '',
            'update_text'                => 'Update Post',
            'form_template'              => __CLASS__,
            'notification'               => array(
                'new'                        => 'on',
                'new_to'                     => get_option( 'admin_email' ),
                'new_subject'                => 'New post has been created',
                'new_body'                   => 'Hi,
A new post has been created in your site %sitename% (%siteurl%).

Here is the details:
Post Title: %post_title%
Description: %post_content%
Short Description: %post_excerpt%
Author: %author%
Post URL: %permalink%
Edit URL: %editlink%',
                'edit'                       => 'off',
                'edit_to'                    => get_option( 'admin_email' ),
                'edit_subject'               => 'Post has been edited',
                'edit_body'                  => 'Hi,
The post "%post_title%" has been updated.

Here is the details:
Post Title: %post_title%
Description: %post_content%
Short Description: %post_excerpt%
Author: %author%
Post URL: %permalink%
Edit URL: %editlink%',
                ),
            );
    }
}