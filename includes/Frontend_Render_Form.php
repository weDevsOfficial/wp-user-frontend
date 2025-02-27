<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Admin\Subscription;

class Frontend_Render_Form {
    private static $_instance;

    public static $meta_key = 'wpuf_form';

    public static $separator = ' | ';

    private $form_condition_key = 'wpuf_cond';

    private $field_count = 0;

    public $multiform_start = 0;

    public $form_fields = [];

    public $form_settings = [];

    /**
     * Send json error message
     *
     * @param string $error
     */
    public function send_error( $error ) {
        echo json_encode(
            [
                'success' => false,
                'error'   => $error,
            ]
        );
        die();
    }



    /**
     * render submit button
     *
     * @param [type] $form_id       [description]
     * @param [type] $form_settings [description]
     * @param [type] $post_id       [description]
     */
    public function submit_button( $form_id, $form_settings, $post_id = null ) { ?>

        <li class="wpuf-submit">
            <div class="wpuf-label">
                &nbsp;
            </div>

            <?php wp_nonce_field( 'wpuf_form_add' ); ?>
            <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
            <input type="hidden" name="page_id" value="<?php echo get_post( $post_id ) ? esc_attr( get_the_ID() ) : '0'; ?>">
            <input type="hidden" id="del_attach" name="delete_attachments[]">
            <input type="hidden" name="action" value="wpuf_submit_post">

            <?php do_action( 'wpuf_submit_btn', $form_id, $form_settings ); ?>

            <?php
            if ( $post_id ) {
                $cur_post = get_post( $post_id );
                ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                <input type="hidden" name="post_date" value="<?php echo esc_attr( $cur_post->post_date ); ?>">
                <input type="hidden" name="comment_status" value="<?php echo esc_attr( $cur_post->comment_status ); ?>">
                <input type="hidden" name="post_author" value="<?php echo esc_attr( $cur_post->post_author ); ?>">
                <input type="submit" class="wpuf-submit-button wpuf_submit_<?php echo esc_attr( $form_id ); ?>" name="submit" value="<?php echo esc_attr( $form_settings['update_text'] ); ?>" />
                <?php
            } else {
                ?>
                <input type="submit" class="wpuf-submit-button wpuf_submit_<?php echo esc_attr( $form_id ); ?>" name="submit" value="<?php echo esc_attr( $form_settings['submit_text'] ); ?>" />
            <?php } ?>

            <?php if ( isset( $form_settings['draft_post'] ) && $form_settings['draft_post'] == 'true' ) { ?>
                <a href="#" class="btn" id="wpuf-post-draft"><?php esc_html_e( 'Save Draft', 'wp-user-frontend' ); ?></a>
            <?php } ?>
        </li>

        <?php
    }

    /**
     * guest post field
     *
     * @param [type] $form_settings [description]
     */
    public function guest_fields( $form_settings ) {
        ?>
        <li class="el-name">
            <div class="wpuf-label">
                <label><?php echo esc_html( $form_settings['name_label'] ); ?> <span class="required">*</span></label>
            </div>

            <div class="wpuf-fields">
                <input type="text" required="required" data-required="yes" data-type="text" name="guest_name" value="" size="40">
            </div>
        </li>

        <li class="el-email">
            <div class="wpuf-label">
                <label><?php echo esc_html( $form_settings['email_label'] ); ?> <span class="required">*</span></label>
            </div>

            <div class="wpuf-fields">
                <input type="email" required="required" data-required="yes" data-type="email" name="guest_email" value="" size="40">
            </div>
        </li>
        <?php
    }

    /**
     * Form preview handler
     *
     * @return void
     */
    public function preview_form() {
        $form_id = isset( $_GET['form_id'] ) ? intval( wp_unslash( $_GET['form_id'] ) ) : 0;

        if ( $form_id ) {
            ?>

            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>__( 'Form Preview', 'wp-user-frontend' )</title>
                    <link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'assets/css/frontend-forms.css', __DIR__ ) ); ?>">

                    <style type="text/css">
                        body {
                            margin: 0;
                            padding: 0;
                            background: #eee;
                        }

                        .container {
                            width: 700px;
                            margin: 0 auto;
                            margin-top: 20px;
                            padding: 20px;
                            background: #fff;
                            border: 1px solid #DFDFDF;
                            -webkit-box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                            box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                        }
                    </style>

                    <script type="text/javascript" src="<?php echo esc_url( includes_url( 'js/jquery/jquery.js' ) ); ?>"></script>
                </head>
                <body>
                    <div class="container">
                        <?php $this->render_form( $form_id, null, null, null ); ?>
                    </div>
                </body>
            </html>

            <?php
        } else {
            wp_die( 'Error generating the form preview' );
        }

        exit;
    }

    /**
     * render form
     *
     * @param [type] $form_id [description]
     * @param [type] $post_id [description]
     * @param array  $atts    [description]
     * @param [type] $form    [description]
     */
    public function render_form( $form_id, $post_id = null, $atts = [], $form = null ) {
        $form_status = get_post_status( $form_id );

        if ( ! $form_status ) {
            echo wp_kses_post( '<div class="wpuf-message">' . __( 'Your selected form is no longer available.', 'wp-user-frontend' ) . '</div>' );

            return;
        }

        if ( 'publish' !== $form_status ) {
            echo wp_kses_post( '<div class="wpuf-message">' . __( "Please make sure you've published your form.", 'wp-user-frontend' ) . '</div>' );

            return;
        }

        $label_position = isset( $this->form_settings['label_position'] ) ? $this->form_settings['label_position'] : 'left';

        $layout = isset( $this->form_settings['form_layout'] ) ? $this->form_settings['form_layout'] : 'layout1';

        $theme_css = isset( $this->form_settings['use_theme_css'] ) ? $this->form_settings['use_theme_css'] : 'wpuf-style';

        do_action( 'wpuf_before_form_render', $form_id );

        if ( ! empty( $layout ) ) {
            wp_enqueue_style( 'wpuf-' . $layout );
        }

        if ( ! is_user_logged_in() && $this->form_settings['guest_post'] !== 'true' ) {
            echo wp_kses_post( '<div class="wpuf-message">' . $this->form_settings['message_restrict'] . '</div>' );

            return;
        }

        if (
                isset( $this->form_settings['role_base'] )
                && wpuf_validate_boolean( $this->form_settings['role_base'] )
                && ! wpuf_user_has_roles( $this->form_settings['roles'] )
            ) {
            ?>
            <div class="wpuf-message"><?php esc_html_e( 'You do not have sufficient permissions to access this form.', 'wp-user-frontend' ); ?></div>
            <?php

            return;
        }

        if ( $this->form_fields ) {
            ?>

                <form class="wpuf-form-add wpuf-form-<?php echo esc_attr( $layout ); ?> <?php echo ( 'layout1' === $layout ) ? esc_html( $theme_css ) : 'wpuf-style'; ?>" action="" method="post">

                    <script type="text/javascript">
                        if ( typeof wpuf_conditional_items === 'undefined' ) {
                            wpuf_conditional_items = [];
                        }

                        if ( typeof wpuf_plupload_items === 'undefined' ) {
                            wpuf_plupload_items = [];
                        }

                        if ( typeof wpuf_map_items === 'undefined' ) {
                            wpuf_map_items = [];
                        }
                    </script>

                    <ul class="wpuf-form form-label-<?php echo esc_attr( $label_position ); ?>">

                    <?php

                        do_action( 'wpuf_form_fields_top', $form, $this->form_fields );

                    if ( ! $post_id ) {
                        do_action( 'wpuf_add_post_form_top', $form_id, $this->form_settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_top', $form_id, $post_id, $this->form_settings );
                    }

                    if ( ! is_user_logged_in() && $this->form_settings['guest_post'] == 'true' && $this->form_settings['guest_details'] == 'true' ) {
                        $this->guest_fields( $this->form_settings );
                    }

                        $this->render_featured_field( $post_id );

                        wpuf()->fields->render_fields( $this->form_fields, $form_id, $atts, $type = 'post', $post_id );

                        $this->submit_button( $form_id, $this->form_settings, $post_id );

                    if ( ! $post_id ) {
                        do_action( 'wpuf_add_post_form_bottom', $form_id, $this->form_settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_bottom', $form_id, $post_id, $this->form_settings );
                    }

                    ?>

                    </ul>

                </form>

                <?php
        } //endif

        do_action( 'wpuf_after_form_render', $form_id );
    }

    /**
     * Render a checkbox for enabling feature item
     */
    public function render_featured_field( $post_id = null ) {
        $user_sub = Subscription::get_user_pack( get_current_user_id() );
        $is_featured = false;
        if ( $post_id ) {
            $stickies = get_option( 'sticky_posts' );
            $is_featured   = in_array( intval( $post_id ), $stickies, true );
        }

        $post_type     = ! empty( $this->form_settings['post_type'] ) ? $this->form_settings['post_type'] : 'post';
        $featured_item = ! empty( $user_sub['total_feature_item'] ) ? $user_sub['total_feature_item'] : 0;

        if ( ! empty( $user_sub['total_feature_item'] ) || $is_featured ) {
            ?>
            <li class="wpuf-el field-size-large" data-label="Is featured">
                <div class="wpuf-label">
                    <label for="wpuf_is_featured"><?php esc_html_e( 'Featured', 'wp-user-frontend' ); ?></label>
                </div>
                <div >
                    <label >
                         <input type="checkbox" class="wpuf_is_featured" name="is_featured_item" value="1" <?php echo $is_featured ? 'checked' : ''; ?> >
                         <span class="wpuf-items-table-containermessage-box" id="remaining-feature-item"> <?php echo sprintf( __( 'Mark the %s as featured (remaining %d)', 'wp-user-frontend' ), $post_type, $featured_item ); ?></span>
                    </label>
                </div>
            </li>
            <script>
                (function ($) {
                    $('.wpuf_is_featured').on('change', function (e) {
                        var counter = $('.wpuf-message-box');
                        var count = parseInt( counter.text().match(/\d+/) );
                        if ($(this).is(':checked')) {
                            counter.text( counter.text().replace( /\d+/, count - 1 ) );
                        } else {
                            counter.text( counter.text().replace( /\d+/, count + 1 ) );
                        }
                    })
                })(jQuery)
            </script>
            <?php
        }
    }

    /**
     * get Input fields
     *
     * @param array $form_vars
     *
     * @return array
     */
    public function get_input_fields( $form_vars ) {
        $ignore_lists = [ 'section_break', 'html' ];
        $post_vars    = $meta_vars = $taxonomy_vars = [];

        foreach ( $form_vars as $key => $value ) {
            // get column field input fields
            if ( $value['input_type'] == 'column_field' ) {
                $inner_fields = $value['inner_fields'];

                foreach ( $inner_fields as $column_key => $column_fields ) {
                    if ( ! empty( $column_fields ) ) {
                        // ignore section break and HTML input type
                        foreach ( $column_fields as $column_field_key => $column_field ) {
                            if ( in_array( $column_field['input_type'], $ignore_lists ) ) {
                                continue;
                            }

                            //separate the post and custom fields
                            if ( isset( $column_field['is_meta'] ) && $column_field['is_meta'] == 'yes' ) {
                                $meta_vars[] = $column_field;
                                continue;
                            }

                            if ( $column_field['input_type'] == 'taxonomy' ) {

                                // don't add "category"
                                // if ( $column_field['name'] == 'category' ) {
                                //     continue;
                                // }

                                $taxonomy_vars[] = $column_field;
                            } else {
                                $post_vars[] = $column_field;
                            }
                        }
                    }
                }
                continue;
            }

            // ignore section break and HTML input type
            if ( in_array( $value['input_type'], $ignore_lists ) ) {
                continue;
            }

            //separate the post and custom fields
            if ( isset( $value['is_meta'] ) && $value['is_meta'] == 'yes' ) {
                $meta_vars[] = $value;
                continue;
            }

            if ( $value['input_type'] == 'taxonomy' ) {

                // don't add "category"
                // if ( $value['name'] == 'category' ) {
                //     continue;
                // }

                $taxonomy_vars[] = $value;
            } else {
                $post_vars[] = $value;
            }
        }

        return [ $post_vars, $taxonomy_vars, $meta_vars ];
    }
}
