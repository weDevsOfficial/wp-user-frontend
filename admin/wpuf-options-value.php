<?php
/*
 * Array param definitions are as follows:
 * name    = field name
 * desc    = field description
 * tip     = question mark tooltip text
 * id      = database column name or the WP meta field name
 * class   = css class
 * css     = any on-the-fly styles you want to add to that field
 * type    = type of html field
 * req     = if the field is required or not (1=required)
 * min     = minimum number of characters allowed before saving data
 * std     = default value. not being used
 * js      = allows you to pass in javascript for onchange type events
 * vis     = if field should be visible or not. used for dropdown values field
 * visid   = this is the row css id that must correspond with the dropdown value that controls this field
 * options = array of drop-down option value/name combo
 * altclass = adds a new css class to the input field (since v3.1)
 *
 *
 */

function wpuf_build_form( $options, $values = '', $from_option = true ) {

    $options = apply_filters( 'wpuf_build_form_args', $options );

    if ( is_array( $options ) ) {

        foreach ($options as $element) {
            //var_dump( $element );
            $default = array(
                'name' => '',
                'label' => '',
                'desc' => '',
                'id' => '',
                'class' => '',
                'css' => '',
                'type' => '',
                'default' => '',
                'js' => '',
                'alt' => '',
                'options' => '',
                'size' => '25',
                'rows' => '10',
                'cols' => '40',
                'onchange' => ''
            );

            $element = array_merge( $default, $element );

            //needed for editing the form, the default values
            if ( is_array( $values ) && $from_option == false ) {

                $value = $values[$element['name']];
            } else if ( get_option( $element['name'] ) ) {

                $value = stripslashes( get_option( $element['name'] ) );
            } else {

                $value = stripslashes( $element['default'] );
            }

            switch ($element['type']) {

                case 'table_start':
                    ?>
                    <table class="<?php echo $element['class']; ?>" id="<?php echo $element['id']; ?>" style="<?php echo $element['css']; ?>">

                        <?php
                        break;

                    case 'text':
                        $element['name'] = stripslashes( $element['name'] );
                        $element['label'] = stripslashes( $element['label'] );
                        $element['desc'] = stripslashes( $element['desc'] );
                        ?>
                        <li>
                            <span class="label">
                                <label for="<?php echo $element['name']; ?>"><?php echo $element['label']; ?></label>

                                <?php if ( $element['desc'] ): ?>
                                    <span class="wpuf_help" title="<?php echo $element['desc']; ?>"></span>
                                <?php endif; ?>

                            </span>
                            <span class="input-field">
                                <input type="text" name="<?php echo $element['name']; ?>" value="<?php echo $value; ?>" id="<?php echo $element['name']; ?>" style="<?php echo $element['css']; ?>" size="<?php echo $element['size']; ?>">
                                <span class="description"><?php echo $element['desc']; ?></span>
                            </span>
                        </li>

                        <?php
                        break;

                    case 'textarea':
                        ?>
                        <li>
                            <span class="label">
                                <label for="<?php echo $element['name']; ?>"><?php echo $element['label']; ?></label>

                                <?php if ( $element['desc'] ): ?>
                                    <span class="wpuf_help" title="<?php echo $element['desc']; ?>"></span>
                                <?php endif; ?>

                            </span>
                            <span class="input-field">
                                <textarea name="<?php echo $element['name']; ?>" id="<?php echo $element['name']; ?>" style="<?php echo $element['css']; ?>" rows="<?php echo $element['rows']; ?>" cols="<?php echo $element['cols']; ?>"><?php echo $value; ?></textarea>
                                <span class="description"><?php echo $element['desc']; ?></span>
                            </span>
                        </li>

                        <?php
                        break;

                    case 'select':
                        ?>
                        <li>
                            <span class="label">
                                <label for="<?php echo $element['name']; ?>"><?php echo $element['label']; ?></label>

                                <?php if ( $element['desc'] ): ?>
                                    <span class="wpuf_help" title="<?php echo $element['desc']; ?>"></span>
                                <?php endif; ?>

                            </span>
                            <span class="input-field">

                                <?php
                                //onchange event
                                $onchange = $element['onchange'];
                                if ( $onchange ) {
                                    $onchange = ' onchange="' . $onchange . '" ';
                                } else {
                                    $onchange = ' ';
                                }
                                ?>

                                <select name="<?php echo $element['name']; ?>" id="<?php echo $element['name']; ?>"<?php echo $onchange; ?>>
                                    <?php foreach ($element['options'] as $key => $val) : ?>
                                        <option value="<?php echo $key; ?>"<?php if ( $value == $key )
                            echo ' selected="selected"'; ?>><?php echo $val; ?></option>
                                            <?php endforeach; ?>
                                </select>

                                <span class="description"><?php echo $element['desc']; ?></span>
                            </span>
                        </li>


                        <?php
                        break;

                    case 'checkbox':
                        ?>
                        <tr valign="top">
                            <td scope="row" class="label">
                                <label for="<?php echo $element['name']; ?>"><?php echo $element['label']; ?></label>

                                <?php if ( $element['desc'] ): ?>
                                    <span class="wpuf_help" title="<?php echo $element['desc']; ?>"></span>
                                <?php endif; ?>

                            </td>
                            <td>
                                <input type="checkbox" name="<?php echo $element['name'] ?>" id="<?php echo $element['name'] ?>" value="true" style="<?php echo $element['css'] ?>" <?php if ( get_option( $element['name'] ) ) { ?>checked="checked"<?php } ?> />
                                <span class="description"><?php echo $element['desc']; ?></span>
                            </td>
                        </tr>

                        <?php
                        break;

                    case 'table_end':
                        echo '</table>';
                        break;

                    case 'hidden':
                        ?>
                        <input type="hidden" name="<?php echo $element['name']; ?>" value="<?php echo $element['default']; ?>" />

                        <?php
                        break;

                    case 'submit':
                        ?>
                        <p>
                            <input type="submit" name="<?php echo $element['name']; ?>" class="<?php echo $element['class']; ?>" value="<?php echo $element['label']; ?>" style="<?php echo $element['css']; ?>" />
                        </p>

                        <?php
                        break;

                    case 'h1':
                        echo '<h1>' . $element['label'] . '</h1>';
                        break;

                    case 'title':
                        ?>
                        <div id="<?php echo sanitize_title_with_dashes( $element['label'] ); ?>" class="group">
                            <h3><?php echo $element['label']; ?></h3>

                            <ul>
                                <?php
                                break;

                            case 'title_end':
                                ?>
                            </ul>
                        </div> <!-- title-end -->
                        <?php
                        break;

                    case 'h3':
                        echo '<h3>' . $element['label'] . '</h3>';
                        break;

                    case 'h4':
                        echo '<h4>' . $element['label'] . '</h4>';
                        break;

                    case 'html':
                        echo '<li>' . $element['label'] . '</li>';
                        break;

                    default:
                        break;
                } //switch
            } //foreach
        } //is_array
    }

    /**
     * Updates the admin panel values
     */
    function wpuf_update_form() {

        if ( isset( $_POST['symple_submit'] ) ) {

            foreach ($_POST as $key => $value) {

                //update the input fields, whose names starts with symple_
                if ( symple_starts_with( $key, 'symple_' ) ) {
                    //echo "$key => $value <br>";
                    update_option( $key, symple_clean( $value ) );
                } //starts with
            } //foreach

            echo '<div id="message" class="updated fade"><p>' . __( 'Your settings have been saved.', 'symple' ) . '</p></div>';
        } //submit
    }

    /**
     * Build custom field form for add posting form
     *
     * @global type $wpdb
     * @param type $position
     */
    function wpuf_build_custom_field_form( $position = 'top', $edit = false, $post_id = 0 ) {
        global $wpdb;

        //check, if custom field is enabled
        $enabled = get_option( 'wpuf_enable_custom_field' );
        //var_dump( $enabled );
        if ( $enabled == 'no' ) {
            return false;
        }

        $table = $wpdb->prefix . 'wpuf_customfields';

        $results = $wpdb->get_results( "SELECT * FROM $table WHERE `region`='$position' ORDER BY `order`", OBJECT );

        if ( is_array( $results ) ) {

            foreach ($results as $field) {
                if ( wpuf_starts_with( $field->field, 'cf_' ) ) {

                    if ( $edit && $post_id ) {
                        $value = get_post_meta( $post_id, $field->field, true );
                    } else {
                        $value = '';
                    }

                    switch ($field->type) {
                        case 'text':
                            ?>
                            <li>
                                <label for="<?php echo $field->field; ?>">
                                    <?php echo stripslashes( $field->label ); ?>
                                    <?php if ( $field->required == 'yes' ): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <?php $class = ( $field->required == 'yes' ) ? 'requiredField' : ''; ?>
                                <input class="<?php echo $class; ?>" type="text" name="<?php echo $field->field; ?>" id="<?php echo $field->field; ?>" minlength="2" value="<?php echo stripslashes( $value ); ?>">
                                <div class="clear"></div>

                                <?php if ( $field->desc ): ?>
                                    <p class="description"><?php echo stripslashes( $field->desc ); ?></p>
                                    <div class="clear"></div>
                                <?php endif; ?>

                            </li>

                            <?php
                            break;

                        case 'textarea':
                            ?>
                            <li>
                                <label for="<?php echo $field->field; ?>">
                                    <?php echo stripslashes( $field->label ); ?>
                                    <?php if ( $field->required == 'yes' ): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <?php $class = ( $field->required == 'yes' ) ? 'requiredField' : ''; ?>
                                <textarea class="<?php echo $class; ?>" name="<?php echo $field->field; ?>" id="<?php echo $field->field; ?>"><?php echo stripslashes( $value ); ?></textarea>
                                <div class="clear"></div>

                                <?php if ( $field->desc ): ?>
                                    <p class="description"><?php echo stripslashes( $field->desc ); ?></p>
                                    <div class="clear"></div>
                                <?php endif; ?>

                            </li>

                            <?php
                            break;

                        case 'select':
                            ?>
                            <li>
                                <label for="<?php echo $field->field; ?>">
                                    <?php echo stripslashes( $field->label ); ?>
                                    <?php if ( $field->required == 'yes' ): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <select name="<?php echo $field->field; ?>">
                                    <?php
                                    $options = explode( ',', $field->values );
                                    if ( is_array( $options ) ) {
                                        foreach ($options as $opt) {
                                            $opt = trim( strip_tags( $opt ) );
                                            echo "<option value='$opt' " . selected( $value, $opt, false ) . ">$opt</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="clear"></div>

                                <?php if ( $field->desc ): ?>
                                    <p class="description"><?php echo stripslashes( $field->desc ); ?></p>
                                    <div class="clear"></div>
                                <?php endif; ?>

                            </li>

                            <?php
                            break;


                        default:
                    } //switch
                } else {
                    switch ($field->type) {
                        case 'text':
                            ?>
                            <li>
                                <label for="<?php echo $field->field; ?>">
                                    <?php echo stripslashes( $field->label ); ?>
                                    <?php if ( $field->required == 'yes' ): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <?php $class = ( $field->required == 'yes' ) ? 'requiredField' : ''; ?>
                                <input class="<?php echo $class; ?>" type="text" name="<?php echo $field->field; ?>" id="<?php echo $field->field; ?>" minlength="2" value="<?php echo stripslashes( $value ); ?>">
                                <div class="clear"></div>

                                <?php if ( $field->desc ): ?>
                                    <p class="description"><?php echo stripslashes( $field->desc ); ?></p>
                                    <div class="clear"></div>
                                <?php endif; ?>

                            </li>

                            <?php
                            break;

                        case 'select':
                            $fld = substr( $field->field, 3 );
                            $terms = get_terms( $fld );
                            //var_dump( $fld );
                            if ( $terms ) {
                                foreach ($terms as $t) {
                                    $term_option .= '<option  value="' . $t->term_id . '">' . $t->name . '</option>';
                                }
                            }
                            ?>
                            <li>
                                <label for="<?php echo $field->field; ?>">
                                    <?php echo stripslashes( $field->label ); ?>
                                    <?php if ( $field->required == 'yes' ): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <select name="<?php echo $field->field; ?>">
                                    <?php echo $term_option; ?>
                                </select>
                                <div class="clear"></div>

                                <?php if ( $field->desc ): ?>
                                    <p class="description"><?php echo stripslashes( $field->desc ); ?></p>
                                    <div class="clear"></div>
                                <?php endif; ?>

                            </li>

                        <?php
                        default :
                    }
                }
            } //foreach
        } // is_array
    }

    $wpuf_options = array(
        array(
            'type' => 'title',
            'label' => 'Label Options'
        ),
        array(
            'name' => 'wpuf_title_label',
            'label' => 'Post title label',
            'desc' => 'Label for post title',
            'type' => 'text',
            'default' => 'Title'
        ),
        array(
            'name' => 'wpuf_title_help',
            'label' => 'Post title help text',
            'desc' => 'Description for post title. Will be shown as help text, leave empty if you don\'t want anything',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_cat_label',
            'label' => 'Post category label',
            'desc' => 'Label for post category',
            'type' => 'text',
            'default' => 'Category'
        ),
        array(
            'name' => 'wpuf_cat_help',
            'label' => 'Post category help text',
            'desc' => 'Description for post category. Will be shown as help text, leave empty if you don\'t want anything',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_desc_label',
            'label' => 'Post description label',
            'desc' => 'Label for post description',
            'type' => 'text',
            'default' => 'Description'
        ),
        array(
            'name' => 'wpuf_desc_help',
            'label' => 'Post description help text',
            'desc' => 'Help text for post description',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_tag_label',
            'label' => 'Post tag label',
            'desc' => 'Label for post tags',
            'type' => 'text',
            'default' => 'Tags'
        ),
        array(
            'name' => 'wpuf_tag_help',
            'label' => 'Post tag help text',
            'desc' => 'Description for post title',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_post_submit_label',
            'label' => 'Post submit button label',
            'desc' => 'The text will be used for submit button',
            'type' => 'text',
            'default' => 'Submit Post!'
        ),
        array(
            'name' => 'wpuf_post_update_label',
            'label' => 'Post update button label',
            'desc' => 'The text will be used for update button',
            'type' => 'text',
            'default' => 'Update Post!'
        ),
        array(
            'name' => 'wpuf_post_submitting_label',
            'label' => 'Post updating button label',
            'desc' => 'The text will be used after clicking the submit button',
            'type' => 'text',
            'default' => 'Please wait...'
        ),
        array(
            'type' => 'title_end'
        ),
        array(
            'type' => 'title',
            'label' => 'Frontend Posting Options'
        ),
        array(
            'name' => 'wpuf_post_status',
            'label' => 'Post Status',
            'desc' => 'Default post status after a user submits a post',
            'type' => 'select',
            'options' => array(
                'publish' => 'Publish',
                'draft' => 'Draft',
                'pending' => 'Pending'
            )
        ),
        array(
            'name' => 'wpuf_post_author',
            'label' => 'Post Author',
            'desc' => 'The poster will be the post author by default. If you want to set the post author as an another user, you may select <b>MAP TO OTHER USER</b>',
            'type' => 'select',
            'options' => array(
                'original' => 'Original Author',
                'to_other' => 'Map to other user'
            )
        ),
        array(
            'name' => 'wpuf_map_author',
            'label' => 'Map posts to Poster',
            'desc' => 'If <b>MAP TO OTHER USER</b> selected, all submitted posts will be posted on this users account.',
            'type' => 'select',
            'options' => wpuf_list_users()
        ),
        array(
            'name' => 'wpuf_allow_choose_cat',
            'label' => 'Allow to choose Category?',
            'desc' => 'Allow Users to choose Category when they are posting?',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_exclude_cat',
            'label' => 'Exclude Category ID',
            'desc' => 'Exclude the categories from the category dropdown list. Enter category ID separated by comma',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_default_cat',
            'label' => 'Default Post Category',
            'desc' => 'If users are not allowed to choose category, select the default category that those posts will be posted.',
            'type' => 'select',
            'options' => wpuf_get_cats()
        ),
        array(
            'name' => 'wpuf_allow_attachments',
            'label' => 'Allow Attachments',
            'desc' => 'Will the users be able to add attachemtns? If they attach image, they will be added as post thumbnail. ',
            'type' => 'select',
            'options' => array(
                'no' => 'Disable',
                'yes' => 'Enable'
            )
        ),
        array(
            'name' => 'wpuf_attachment_num',
            'label' => 'Number of Attachments',
            'desc' => 'How many attachments can be attached on a post?',
            'type' => 'text',
            'size' => 2
        ),
        array(
            'name' => 'wpuf_attachment_max_size',
            'label' => 'Attachemnt max size',
            'desc' => 'Enter the maximum file size in <b>KILOBYTE</b> that is allowed to attach.',
            'type' => 'text',
            'size' => 5
        ),
        array(
            'name' => 'wpuf_editor_type',
            'label' => 'Contend Editor type',
            'desc' => 'On a RICH TEXT selection, users will be given a WYSIWYG editor. Otherwise a simple textarea.',
            'type' => 'select',
            'options' => array(
                'rich' => 'Rich Text',
                'plain' => 'Plain Text'
            )
        ),
        array(
            'name' => 'wpuf_allow_tags',
            'label' => 'Allow Post Tags',
            'desc' => 'Users will be able to add post tags.',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_enable_custom_field',
            'label' => 'Enable custom fields',
            'desc' => 'You can use additional fields on your post submission form. Add new fields by going <b>Custom Fields</b> option page.',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_enable_post_date',
            'label' => 'Enable Post date input',
            'desc' => 'This will enable users to input the posting date',
            'type' => 'select',
            'options' => array(
                'no' => 'Disable',
                'yes' => 'Enable'
            )
        ),
        array(
            'name' => 'wpuf_enable_post_expiry',
            'label' => 'Enable Post expiration',
            'desc' => 'This feature depends on <strong>Post Expirator</strong> plugin. Helps you to automatically expire any post after a certain time.',
            'type' => 'select',
            'options' => array(
                'no' => 'Disable',
                'yes' => 'Enable'
            )
        ),
        array(
            'type' => 'title_end'
        ),
        array(
            'type' => 'title',
            'label' => 'Dashboard Options'
        ),
        array(
            'name' => 'wpuf_list_post_type',
            'label' => 'Show Post Type',
            'desc' => 'Select the post type that the user will see in the dashboard',
            'type' => 'select',
            'options' => wpuf_get_post_types()
        ),
        array(
            'name' => 'wpuf_list_post_range',
            'label' => 'How many posts in a page?',
            'desc' => 'Configure how many posts will be shown in one page',
            'type' => 'text',
            'size' => 2
        ),
        array(
            'name' => 'wpuf_list_user_info',
            'label' => 'Show user bio',
            'desc' => 'Users Biographical info will be shown on the dashboard',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_list_contact_info',
            'label' => 'Show User contact info',
            'desc' => 'Contact information from users profile will be shown under author bio. So Author Bio must be <b>enabled</b>',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_list_post_count',
            'label' => 'Show post count',
            'desc' => 'Show how many posts are created by the user',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_list_user_cs',
            'label' => 'Show users custom fields',
            'desc' => 'If you want to show users other custom fields, list the custom fields names by separating with comma',
            'type' => 'textarea',
            'rows' => 3,
            'cols' => 40
        ),
        array(
            'type' => 'title_end'
        ),
        array(
            'type' => 'title',
            'label' => 'Other Options'
        ),
        array(
            'name' => 'wpuf_notify',
            'label' => 'New Post Notification',
            'type' => 'select',
            'desc' => 'A mail will be sent to admin if new post created',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_notify_poster',
            'label' => 'Post Notification to poster',
            'type' => 'select',
            'desc' => 'A mail will be sent to the post creator when the post is published',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_can_edit_post',
            'label' => 'User can edit post?',
            'desc' => 'Users will be able to edit their posts.',
            'type' => 'select',
            'options' => array(
                'yes' => 'Enable',
                'no' => 'Disable'
            )
        ),
        array(
            'name' => 'wpuf_can_del_post',
            'label' => 'User can delete post?',
            'desc' => 'Users will be able to delete their own post',
            'type' => 'select',
            'options' => array(
                'no' => 'Disable',
                'yes' => 'Enable'
            )
        ),
        array(
            'name' => 'wpuf_edit_page_url',
            'label' => 'Edit page',
            'desc' => 'Select the page where [wpuf_edit] shortcode is located.',
            'type' => 'select',
            'options' => wpuf_dropdown_page()
        ),
        array(
            'name' => 'wpuf_admin_security',
            'label' => 'Admin area access',
            'desc' => 'Allow you to block specific users from role to wordpress admin area. The setting <b>ADMINS ONLY</b> is recommended. If you want the behave as default wordpress, select <b>All Access</b>.',
            'type' => 'select',
            'options' => array(
                'install_themes' => 'Admin Only',
                'edit_others_posts' => 'Admins, Editors',
                'publish_posts' => 'Admins, Editors, Authors',
                'edit_posts' => 'Admins, Editors, Authors, Contributors',
                'read' => 'All Access'
            )
        ),
        array(
            'name' => 'wpuf_show_custom_front',
            'label' => 'Show custom fields in the post',
            'desc' => 'If you want to show the custom field data to the post, select <b>Yes</b>.',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_show_attach_inpost',
            'label' => 'Show attachments in the post',
            'desc' => 'If you want to show the uploaded attachment in the post, select <b>Yes</b>.',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_override_editlink',
            'label' => 'Override the post edit link',
            'desc' => 'Users see the edit link in post if s/he is capable to edit the post/page. Selecting <strong>Yes</strong> will override the default WordPress link',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_custom_css',
            'label' => 'Custom CSS codes',
            'desc' => 'Add your custom CSS codes if you want to. This code will be placed on page header area wrapped with style tag',
            'type' => 'textarea',
            'rows' => 8,
            'cols' => 40
        ),
        array(
            'type' => 'title_end'
        ),
        array(
            'type' => 'title',
            'label' => 'Payment Options'
        ),
        array(
            'name' => 'wpuf_sub_charge_posting',
            'label' => 'Charge for posting',
            'desc' => 'Apply charging for submitting a post',
            'type' => 'select',
            'options' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        ),
        array(
            'name' => 'wpuf_sub_force_pack',
            'label' => 'User must purchase packs for posting?',
            'desc' => "When this option is active, users can't post without purchasing a subscription package",
            'type' => 'select',
            'options' => array(
                'no' => 'Disable',
                'yes' => 'Enable'
            )
        ),
        array(
            'name' => 'wpuf_sub_currency',
            'label' => 'Currency',
            'desc' => 'Currency of the amount',
            'type' => 'select',
            'options' => array(
                'USD' => 'USD',
                'AUD' => 'AUD'
            )
        ),
        array(
            'name' => 'wpuf_sub_currency_sym',
            'label' => 'Currency Symbol',
            'desc' => 'Enter your currency symbol',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_sub_amount',
            'label' => 'Cost',
            'desc' => 'Cost per post',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_sub_paypal_mail',
            'label' => 'Paypal Email',
            'desc' => 'Enter your paypal email address where the money will be sent',
            'type' => 'text'
        ),
        array(
            'name' => 'wpuf_sub_paypal_sandbox',
            'label' => 'Enable paypal sandbox',
            'desc' => 'Wheather use paypal as testing mode or not',
            'type' => 'select',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            )
        ),
        array(
            'name' => 'wpuf_sub_pay_page',
            'label' => 'Paypal Payment Page',
            'desc' => 'This page will be used to process the payment options.',
            'type' => 'select',
            'options' => wpuf_dropdown_page()
        ),
        array(
            'name' => 'wpuf_sub_pay_thank_page',
            'label' => 'Paypal Thank you page',
            'desc' => 'After payment, users will be redirected here.',
            'type' => 'select',
            'options' => wpuf_dropdown_page()
        ),
        array(
            'type' => 'title_end'
        ),
        array(
            'type' => 'title',
            'label' => 'Support'
        ),
        array(
            'type' => 'h4',
            'label' => 'Facing any problem?'
        ),
        array(
            'type' => 'html',
            'label' => '
              <ol>
                <li>
                    <strong>Check the FAQ and the documentation</strong>
                    <p>First of all, check the <strong><a href="http://wordpress.org/extend/plugins/wp-user-frontend/faq/">FAQ</a></strong> before contacting! Most of the questions you might need answers to have already been asked and the answers are in the FAQ. Checking the FAQ is the easiest and quickest way to solve your problem.</p>
                </li>
                <li>
                    <strong>Use the Support Forum</strong>
                    <p>If you were unable to find the answer to your question on the FAQ page, you should check the <strong><a href="http://wordpress.org/tags/wp-user-frontend?forum_id=10">support forum on WordPress.org</a></strong>. If you can’t locate any topics that pertain to your particular issue, post a new topic for it.</p>
                    <p>But, remember that this is a free support forum and no one is obligated to help you. Every person who offers information to help you is a volunteer, so be polite. And, I would suggest that you read the <a href="http://wordpress.org/support/topic/68664">“Forum Rules”</a> before posting anything on this page.</p>
                </li>
                <li>
                    <strong>Got an idea?</strong>
                    <p>I would love to hear about your ideas and suggestions about the plugin. Please post them on the <strong><a href="http://wordpress.org/tags/wp-user-frontend?forum_id=10">support forum on WordPress.org</a></strong> and I will look into it</p>
                </li>
                <li>
                    <strong>Gettings no response?</strong>
                    <p>I try to answer all the question in the forum. I created the plugin without any charge and I am usually very busy with my other works. As this is a free plugin, I am not bound answer all of your questions.</p>
                </li>
                <li>
                    I spent countless hours to build this plugin, <strong>support</strong> me if you like this plugin and <a href="http://wordpress.org/extend/plugins/wp-user-frontend/">rate</a> the plugin.
                </li>
                </ol>'
        ),
        array(
            'type' => 'title_end'
        ),
    );

    $custom_fields = array(
        array(
            'name' => 'field',
            'label' => 'Field Name',
            'desc' => 'Name without space. Will be used to store the value in this custom field',
            'type' => 'text'
        ),
        array(
            'name' => 'label',
            'label' => 'Label',
            'desc' => 'This will be used as your input fields title',
            'type' => 'text'
        ),
        array(
            'name' => 'help',
            'label' => 'Help Text',
            'desc' => 'Text will be shown to user as help text',
            'type' => 'text'
        ),
        array(
            'name' => 'required',
            'label' => 'Required',
            'desc' => 'A validation criteria. User must provide input in that field',
            'type' => 'select',
            'options' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        ),
        array(
            'name' => 'region',
            'label' => 'Region',
            'desc' => 'Where do you want to show this input field?',
            'type' => 'select',
            'options' => array(
                'top' => 'Top',
                'description' => 'Before Description',
                'tag' => 'After Description',
                'bottom' => 'Bottom'
            )
        ),
        array(
            'name' => 'order',
            'label' => 'Order',
            'desc' => 'Which order this input field will show in a region',
            'type' => 'text',
            'size' => 2
        ),
        array(
            'name' => 'type',
            'label' => 'Type',
            'type' => 'select',
            'options' => array(
                'text' => 'Text Box',
                'textarea' => 'Text Area',
                'select' => 'Dropdown'
            ),
            'onchange' => 'wpuf_show(this)'
        )
    );

    $taxonomy_fields = array(
        array(
            'name' => 'field',
            'label' => 'Taxonomy Name',
            'desc' => 'The name of your custom taxonomy.',
            'type' => 'text'
        ),
        array(
            'name' => 'label',
            'label' => 'Label',
            'desc' => 'This will be used as your input fields title',
            'type' => 'text'
        ),
        array(
            'name' => 'help',
            'label' => 'Help Text',
            'desc' => 'Text will be shown to user as help text',
            'type' => 'text'
        ),
        array(
            'name' => 'required',
            'label' => 'Required',
            'desc' => 'A validation criteria. User must provide input in that field',
            'type' => 'select',
            'options' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        ),
        array(
            'name' => 'region',
            'label' => 'Region',
            'desc' => 'Where do you want to show this input field?',
            'type' => 'select',
            'options' => array(
                'top' => 'Top',
                'description' => 'Before Description',
                'tag' => 'After Description',
                'bottom' => 'Bottom'
            )
        ),
        array(
            'name' => 'order',
            'label' => 'Order',
            'desc' => 'Which order this input field will show in a region',
            'type' => 'text',
            'size' => 2
        ),
        array(
            'name' => 'type',
            'label' => 'Type',
            'type' => 'select',
            'options' => array(
                'text' => 'Text Box',
                'select' => 'Dropdown'
            )
        )
    );

//wpuf_build_custom_field_form('top');
