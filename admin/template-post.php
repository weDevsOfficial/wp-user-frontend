<?php
/**
 * Post related form templates
 *
 * @package WP User Frontend
 */
class WPUF_Admin_Template_Post extends WPUF_Admin_Template {

    public static function post_title( $field_id, $label, $values = array() ) {

        ?>
        <li class="post_title">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_title' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'post_title', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function post_content( $field_id, $label, $values = array() ) {

        $image_insert_name  = sprintf( '%s[%d][insert_image]', self::$input_name, $field_id );
        $image_insert_value = isset( $values['insert_image'] ) ? $values['insert_image'] : 'yes';
        $word_restriction_name = sprintf( '%s[%d][word_restriction]', self::$input_name, $field_id );
        $word_restriction_value = isset( $values['word_restriction'] ) && is_numeric( $values['word_restriction'] ) ? $values['word_restriction'] : '';
        ?>
        <li class="post_content">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_content' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'post_content', false, $values ); ?>
                <?php self::common_textarea( $field_id, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Enable Image Insertion', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][insert_image]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $image_insert_name ?>" value="yes"<?php checked( $image_insert_value, 'yes' ); ?> />
                            <?php _e( 'Enable image upload in post area', 'wpuf' ); ?>
                        </label>
                    </div>

                    <label><?php _e( 'Word Restriction', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" class="smallipopInput" name="<?php echo $word_restriction_name ?>" value="<?php echo $word_restriction_value; ?>" title="<?php esc_attr_e( 'Numebr of words the author to be restricted in', 'wpuf' ); ?>" />
                        </label>
                    </div>
                </div> <!-- .wpuf-form-rows -->

                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function post_excerpt( $field_id, $label, $values = array() ) {
        ?>
        <li class="post_excerpt">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'textarea' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_excerpt' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'post_excerpt', false, $values ); ?>
                <?php self::common_textarea( $field_id, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function post_tags( $field_id, $label, $values = array() ) {
        ?>
        <li class="post_tags">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_tags' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'tags', false, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function featured_image( $field_id, $label, $values = array() ) {
        $max_file_name = sprintf( '%s[%d][max_size]', self::$input_name, $field_id );
        $max_file_value = $values ? $values['max_size'] : '1024';
        $help = esc_attr( __( 'Enter maximum upload size limit in KB', 'wpuf' ) );
        ?>
        <li class="featured_image">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'image_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'featured_image' ); ?>
            <?php self::hidden_field( "[$field_id][count]", '1' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'featured_image', false, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Max. file size', 'wpuf' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_file_name; ?>" value="<?php echo $max_file_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .wpuf-form-rows -->
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function post_category( $field_id, $label, $values = array() ) {
        ?>
        <li class="post_category">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'post_category' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, 'category', false, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    public static function taxonomy( $field_id, $label, $taxonomy = '', $values = array() ) {
        $type_name          = sprintf( '%s[%d][type]', self::$input_name, $field_id );
        $order_name         = sprintf( '%s[%d][order]', self::$input_name, $field_id );
        $orderby_name       = sprintf( '%s[%d][orderby]', self::$input_name, $field_id );
        $exclude_type_name  = sprintf( '%s[%d][exclude_type]', self::$input_name, $field_id );
        $exclude_name       = sprintf( '%s[%d][exclude]', self::$input_name, $field_id );
        $woo_attr_name      = sprintf( '%s[%d][woo_attr]', self::$input_name, $field_id );
        $woo_attr_vis_name  = sprintf( '%s[%d][woo_attr_vis]', self::$input_name, $field_id );

        $type_value         = $values ? esc_attr( $values['type'] ) : 'select';
        $order_value        = $values ? esc_attr( $values['order'] ) : 'ASC';
        $orderby_value      = $values ? esc_attr( $values['orderby'] ) : 'name';
        $exclude_type_value = $values ? esc_attr( $values['exclude_type'] ) : 'exclude';
        $exclude_value      = $values ? esc_attr( $values['exclude'] ) : '';
        $woo_attr_value     = $values ? esc_attr( $values['woo_attr'] ) : 'no';
        $woo_attr_vis_value = $values ? esc_attr( $values['woo_attr_vis'] ) : 'no';
        ?>
        <li class="taxonomy <?php echo $taxonomy; ?> wpuf-conditional">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'taxonomy' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'taxonomy' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, $taxonomy, false, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Type', 'wpuf' ); ?></label>
                    <select name="<?php echo $type_name ?>">
                        <option value="select"<?php selected( $type_value, 'select' ); ?>><?php _e( 'Dropdown', 'wpuf' ); ?></option>
                        <option value="multiselect"<?php selected( $type_value, 'multiselect' ); ?>><?php _e( 'Multi Select', 'wpuf' ); ?></option>
                        <option value="checkbox"<?php selected( $type_value, 'checkbox' ); ?>><?php _e( 'Checkbox', 'wpuf' ); ?></option>
                        <option value="text"<?php selected( $type_value, 'text' ); ?>><?php _e( 'Text Input', 'wpuf' ); ?></option>
                        <option value="ajax"<?php selected( $type_value, 'ajax' ); ?>><?php _e( 'Ajax', 'wpuf' ); ?></option>
                    </select>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Order By', 'wpuf' ); ?></label>
                    <select name="<?php echo $orderby_name ?>">
                        <option value="name"<?php selected( $orderby_value, 'name' ); ?>><?php _e( 'Name', 'wpuf' ); ?></option>
                        <option value="id"<?php selected( $orderby_value, 'id' ); ?>><?php _e( 'Term ID', 'wpuf' ); ?></option>
                        <option value="slug"<?php selected( $orderby_value, 'slug' ); ?>><?php _e( 'Slug', 'wpuf' ); ?></option>
                        <option value="count"<?php selected( $orderby_value, 'count' ); ?>><?php _e( 'Count', 'wpuf' ); ?></option>
                        <option value="term_group"<?php selected( $orderby_value, 'term_group' ); ?>><?php _e( 'Term Group', 'wpuf' ); ?></option>
                    </select>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Order', 'wpuf' ); ?></label>
                    <select name="<?php echo $order_name ?>">
                        <option value="ASC"<?php selected( $order_value, 'ASC' ); ?>><?php _e( 'ASC', 'wpuf' ); ?></option>
                        <option value="DESC"<?php selected( $order_value, 'DESC' ); ?>><?php _e( 'DESC', 'wpuf' ); ?></option>
                    </select>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Selection Type', 'wpuf' ); ?></label>
                    <select name="<?php echo $exclude_type_name ?>">
                        <option value="exclude"<?php selected( $exclude_type_value, 'exclude' ); ?>><?php _e( 'Exclude', 'wpuf' ); ?></option>
                        <option value="include"<?php selected( $exclude_type_value, 'include' ); ?>><?php _e( 'Include', 'wpuf' ); ?></option>
                        <option value="child_of"<?php selected( $exclude_type_value, 'child_of' ); ?>><?php _e( 'Child of', 'wpuf' ); ?></option>
                    </select>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Selection terms', 'wpuf' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $exclude_name; ?>" title="<?php _e( 'Enter the term IDs as comma separated (without space) to exclude/include in the form.', 'wpuf' ); ?>" value="<?php echo $exclude_value; ?>" />
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'WooCommerce Attribute', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][woo_attr]", 'no' ); ?>
                            <input type="checkbox" class="woo_attr" name="<?php echo $woo_attr_name ?>" value="yes"<?php checked( $woo_attr_value, 'yes' ); ?> />
                            <?php _e( 'This taxonomy is a WooCommerce attribute', 'wpuf' ); ?>
                        </label>
                    </div>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows<?php echo $woo_attr_value == 'no' ? ' wpuf-hide' : ''; ?>">
                    <label><?php _e( 'Visibility', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][woo_attr_vis]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $woo_attr_vis_name ?>" value="yes"<?php checked( $woo_attr_vis_value, 'yes' ); ?> />
                            <?php _e( 'Visible on product page', 'wpuf' ); ?>
                        </label>
                    </div>
                </div> <!-- .wpuf-form-rows -->

                <?php self::conditional_field( $field_id, $values ); ?>
                <div class="wpuf-options">
                    <?php

                    $tax = get_terms( $taxonomy,  array(
                        'orderby'    => 'count',
                        'hide_empty' => 0
                    ) );

                    $tax = is_array( $tax ) ? $tax : array();

                    foreach($tax as $tax_obj) {
                      ?>
                        <div>
                            <input type="hidden" value="<?php echo $tax_obj->name;?>" data-taxonomy="yes" data-term-id="<?php echo $tax_obj->term_id;?>"  data-type="option">
                            <input type="hidden" value="<?php echo $tax_obj->term_id;?>" data-taxonomy="yes" data-term-id="<?php echo $tax_obj->term_id;?>"  data-type="option_value">
                        </div>
                      <?php
                    }
                    ?>
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    /**
     * Render parameter fields for numeric text field
     *
     * @since 2.2.7
     *
     * @param $field_id
     * @param $label field label
     * @param $values
     */
    /*public static function numeric_text_field( $field_id, $label, $values = array() ) {
        $step_text_field_name  = sprintf( '%s[%d][step_text_field]', self::$input_name, $field_id );
        $step_text_field_value = isset( $values['step_text_field'] )? $values['step_text_field'] : 1;
        $min_value_field_name  = sprintf( '%s[%d][min_value_field]', self::$input_name, $field_id );
        $min_value_field_value = isset( $values['min_value_field'] )? $values['min_value_field'] : 0;
        $max_value_field_name  = sprintf( '%s[%d][max_value_field]', self::$input_name, $field_id );
        $max_value_field_value = isset( $values['max_value_field'] )? $values['max_value_field'] : 100;
        ?>
        <li class="custom-field text_field">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'numeric_text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'numeric_text_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Step', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $step_text_field_name; ?>" value="<?php echo $step_text_field_value; ?>" />
                        </label>
                    </div>
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Min Value', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $min_value_field_name; ?>" value="<?php echo $min_value_field_value; ?>" />
                        </label>
                    </div>
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Max Value', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $max_value_field_name; ?>" value="<?php echo $max_value_field_value; ?>" />
                        </label>
                    </div>
                </div>
            </div> <!-- .wpuf-form-holder -->


        </li>
    <?php
    }*/

    /**
     * Render parameter fields for address field
     *
     * @param $field_id
     * @param $label
     * @param $values
     *
     */
    /*public static function address_field( $field_id, $label, $values = array() ) {
        $address_desc_name                 = sprintf( '%s[%d][address_desc]', self::$input_name, $field_id );
        $address_desc_value                = isset( $values['address_desc'] )? $values['address_desc'] : '';

        //street address
        $street_address_name               = sprintf( '%s[%d][address][street_address]', self::$input_name, $field_id );
        $street_address_checkbox_name      = sprintf( '%s[%d][address][street_address][checked]', self::$input_name, $field_id );
        $street_address_checkbox_value     = isset( $values['address']['street_address']['checked'] )? $values['address']['street_address']['checked'] : 'checked';
        $street_address_ischecked          = $street_address_checkbox_value ? esc_attr( $street_address_checkbox_value ) : '';
        $street_address_label              = sprintf( '%s[%d][address][street_address][label]', self::$input_name, $field_id );
        $street_address_label_value        = isset( $values['address']['street_address']['label'] )? $values['address']['street_address']['label'] : __( 'Address Line 1', 'wpuf' );
        $street_address_value_name         = sprintf( '%s[%d][address][street_address][value]', self::$input_name, $field_id );
        $street_address_value_default      = isset( $values['address']['street_address']['value'] )? $values['address']['street_address']['value'] : '';
        $street_address_placeholder_name   = sprintf( '%s[%d][address][street_address][placeholder]', self::$input_name, $field_id );
        $street_address_placeholder_value  = isset( $values['address']['street_address']['placeholder'] )? $values['address']['street_address']['placeholder'] : '';
        $street_address_field_type         = sprintf( '%s[%d][address][street_address][type]', self::$input_name, $field_id );
        $street_address_field_type_value   = 'text';
        $street_address_req                = sprintf( '%s[%d][address][street_address][required]', self::$input_name, $field_id );
        $street_address_req_value          = isset( $values['address']['street_address']['required'] )? $values['address']['street_address']['required'] : 'checked';

        //street address 2
        $street_address2_name              = sprintf( '%s[%d][address][street_address2]', self::$input_name, $field_id );
        $street_address2_checkbox_name     = sprintf( '%s[%d][address][street_address2][checked]', self::$input_name, $field_id );
        $street_address2_checkbox_value    = isset( $values['address']['street_address2']['checked'] )? $values['address']['street_address2']['checked'] : 'checked';
        $street_address2_ischecked         = $street_address2_checkbox_value ? esc_attr( $street_address2_checkbox_value ) : '';
        $street_address2_label             = sprintf( '%s[%d][address][street_address2][label]', self::$input_name, $field_id );
        $street_address2_label_value       = isset( $values['address']['street_address2']['label'] )? $values['address']['street_address2']['label'] : __( 'Address Line 2', 'wpuf' );
        $street_address2_value_name        = sprintf( '%s[%d][address][street_address2][value]', self::$input_name, $field_id );
        $street_address2_value_default     = isset( $values['address']['street_address2']['value'] )? $values['address']['street_address2']['value'] : '';
        $street_address2_placeholder_name  = sprintf( '%s[%d][address][street_address2][placeholder]', self::$input_name, $field_id );
        $street_address2_placeholder_value = isset( $values['address']['street_address2']['placeholder'] )? $values['address']['street_address2']['placeholder'] : '';
        $street_address2_field_type        = sprintf( '%s[%d][address][street_address2][type]', self::$input_name, $field_id );
        $street_address2_field_type_value  = 'text';
        $street_address2_req               = sprintf( '%s[%d][address][street_address2][required]', self::$input_name, $field_id );
        $street_address2_req_value         = isset( $values['address']['street_address2']['required'] )? $values['address']['street_address2']['required'] : '';
        //city name

        $city_name                         = sprintf( '%s[%d][address][city_name]', self::$input_name, $field_id );
        $city_checkbox_name                = sprintf( '%s[%d][address][city_name][checked]', self::$input_name, $field_id );
        $city_checkbox_value               = isset( $values['address']['city_name']['checked'] )? $values['address']['city_name']['checked'] : 'checked';
        $city_name_ischecked               = $city_checkbox_value ? esc_attr( $city_checkbox_value ) : '';
        $city_label                        = sprintf( '%s[%d][address][city_name][label]', self::$input_name, $field_id );
        $city_label_value                  = isset( $values['address']['city_name']['label'] )? $values['address']['city_name']['label'] : __( 'City', 'wpuf' );
        $city_value_name                   = sprintf( '%s[%d][address][city_name][value]', self::$input_name, $field_id );
        $city_value_default                = isset( $values['address']['city_name']['value'] )? $values['address']['city_name']['value'] : '';
        $city_placeholder_name             = sprintf( '%s[%d][address][city_name][placeholder]', self::$input_name, $field_id );
        $city_placeholder_value            = isset( $values['address']['city_name']['placeholder'] )? $values['address']['city_name']['placeholder'] : '';
        $city_field_type                   = sprintf( '%s[%d][address][city_name][type]', self::$input_name, $field_id );
        $city_field_type_value             = 'text';
        $city_req                          = sprintf( '%s[%d][address][city_name][required]', self::$input_name, $field_id );
        $city_req_value                    = isset( $values['address']['city_name']['required'] )? $values['address']['city_name']['required'] : 'checked';

        //state name
        $state_name                        = sprintf( '%s[%d][address][state]', self::$input_name, $field_id );
        $state_checkbox_name               = sprintf( '%s[%d][address][state][checked]', self::$input_name, $field_id );
        $state_checkbox_value              = isset( $values['address']['state']['checked'] )? $values['address']['state']['checked'] : 'checked';
        $state_ischecked                   = $state_checkbox_value ? esc_attr( $state_checkbox_value ) : '';
        $state_label                       = sprintf( '%s[%d][address][state][label]', self::$input_name, $field_id );
        $state_label_value                 = isset( $values['address']['state']['label'] )? $values['address']['state']['label'] : __( 'State', 'wpuf' );
        $state_value_name                  = sprintf( '%s[%d][address][state][value]', self::$input_name, $field_id );
        $state_value_default               = isset( $values['address']['state']['value'] )? $values['address']['state']['value'] : '';
        $state_placeholder_name            = sprintf( '%s[%d][address][state][placeholder]', self::$input_name, $field_id );
        $state_placeholder_value           = isset( $values['address']['state']['placeholder'] )? $values['address']['state']['placeholder'] : '';
        $state_field_type                  = sprintf( '%s[%d][address][state][type]', self::$input_name, $field_id );
        $state_field_type_value            = 'text';
        $state_req                         = sprintf( '%s[%d][address][state][required]', self::$input_name, $field_id );
        $state_req_value                   = isset( $values['address']['state']['required'] )? $values['address']['state']['required'] : 'checked';

        //zip name
        $zip_name                          = sprintf( '%s[%d][address][zip]', self::$input_name, $field_id );
        $zip_checkbox_name                 = sprintf( '%s[%d][address][zip][checked]', self::$input_name, $field_id );
        $zip_checkbox_value                = isset( $values['address']['zip']['checked'] )? $values['address']['zip']['checked'] : 'checked';
        $zip_ischecked                     = $zip_checkbox_value ? esc_attr( $zip_checkbox_value ) : '';
        $zip_label                         = sprintf( '%s[%d][address][zip][label]', self::$input_name, $field_id );
        $zip_label_value                   = isset( $values['address']['zip']['label'] )? $values['address']['zip']['label'] : __( 'Zip Code', 'wpuf' );
        $zip_value_name                    = sprintf( '%s[%d][address][zip][value]', self::$input_name, $field_id );
        $zip_value_default                 = isset( $values['address']['zip']['value'] )? $values['address']['zip']['value'] : '';
        $zip_placeholder_name              = sprintf( '%s[%d][address][zip][placeholder]', self::$input_name, $field_id );
        $zip_placeholder_value             = isset( $values['address']['zip']['placeholder'] )? $values['address']['zip']['placeholder'] : '';
        $zip_field_type                    = sprintf( '%s[%d][address][zip][type]', self::$input_name, $field_id );
        $zip_field_type_value              = 'text';
        $zip_req                           = sprintf( '%s[%d][address][zip][required]', self::$input_name, $field_id );
        $zip_req_value                     = isset( $values['address']['zip']['required'] )? $values['address']['zip']['required'] : 'checked';

        //country names
        $county_select_name                = sprintf( '%s[%d][address][country_select]', self::$input_name, $field_id );
        $county_select_checkbox_name       = sprintf( '%s[%d][address][country_select][checked]', self::$input_name, $field_id );
        $county_select_checkbox_value      = isset( $values['address']['country_select']['checked'] )? $values['address']['country_select']['checked'] : 'checked';
        $county_select_ischecked           = $county_select_checkbox_value ? esc_attr( $county_select_checkbox_value ) : '';
        $county_select_label               = sprintf( '%s[%d][address][country_select][label]', self::$input_name, $field_id );
        $county_select_label_value         = isset( $values['address']['country_select']['label'] )? $values['address']['country_select']['label'] : __( 'Country', 'wpuf' );
        $county_select_value_name          = sprintf( '%s[%d][address][country_select][value]', self::$input_name, $field_id );
        $county_select_value_default       = isset( $values['address']['country_select']['value'] )? $values['address']['country_select']['value'] : '';
        $county_select_placeholder_name    = sprintf( '%s[%d][address][country_select][placeholder]', self::$input_name, $field_id );
        $county_select_placeholder_value   = isset( $values['address']['country_select']['placeholder'] )? $values['address']['country_select']['placeholder'] : '';
        $county_select_field_type          = sprintf( '%s[%d][address][country_select][type]', self::$input_name, $field_id );
        $county_select_field_type_value    = 'select';
        $county_select_req                 = sprintf( '%s[%d][address][country_select][required]', self::$input_name, $field_id );
        $county_select_req_value           = isset( $values['address']['country_select']['required'] )? $values['address']['country_select']['required'] : 'checked';
        $hide_country_list_name            = sprintf( '%s[%d][address][country_select][country_select_hide_list][]', self::$input_name, $field_id );
        $hide_country_list_value           = isset( $values['address']['country_select']['country_select_hide_list'] )? $values['address']['country_select']['country_select_hide_list'] : '';
        $show_country_list_name            = sprintf( '%s[%d][address][country_select][country_select_show_list][]', self::$input_name, $field_id );
        $show_country_list_value           = isset( $values['address']['country_select']['country_select_show_list'] )? $values['address']['country_select']['country_select_show_list'] : '';
        $country_list_visibility_opt_name  = sprintf( '%s[%d][address][country_select][country_list_visibility_opt_name]', self::$input_name, $field_id );
        $country_list_visibility_opt_value = isset( $values['address']['country_select']['country_list_visibility_opt_name'] )? $values['address']['country_select']['country_list_visibility_opt_name'] : '';
        ?>
        <li class="custom-field text_field">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'address' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'address_field' ); ?>

            <div class="wpuf-form-holder wpuf-address">
                <?php self::common( $field_id, '', true, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Address Description', 'wpuf' ); ?></label>
                    <textarea name="<?php echo $address_desc_name; ?>"><?php echo $address_desc_value; ?></textarea>
                </div>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Address Field(s)', 'wpuf' ); ?></label>

                    <table class="address-table">
                        <thead>
                            <tr>
                                <th width="45%"><?php _e( 'Fields', 'wpuf' ); ?></th>
                                <th width="10%"><?php _e( 'Required?', 'wpuf' ); ?></th>
                                <th width="15%"><?php _e( 'Label', 'wpuf' ); ?></th>
                                <th width="15%"><?php _e( 'Default Value', 'wpuf' ); ?></th>
                                <th width="15%"><?php _e( 'Placeholder', 'wpuf' ); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $street_address_checkbox_name; ?>" value="checked" <?php echo $street_address_ischecked; ?> />
                                        <?php _e( 'Address Line 1', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $street_address_field_type; ?>" value="<?php echo $street_address_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $street_address_req; ?>" value="checked" <?php echo $street_address_req_value; ?> /></td>
                                <td><input type="text" name="<?php echo $street_address_label; ?>" value="<?php echo $street_address_label_value; ?>"  /></td>
                                <td><input type="text" name="<?php echo $street_address_value_name; ?>" value="<?php echo $street_address_value_default; ?>"  /></td>
                                <td><input type="text" name="<?php echo $street_address_placeholder_name; ?>" value="<?php echo $street_address_placeholder_value; ?>"  /></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $street_address2_checkbox_name; ?>" value="checked" <?php echo $street_address2_ischecked; ?> />
                                        <?php _e( 'Address Line 2', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $street_address2_field_type; ?>" value="<?php echo $street_address2_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $street_address2_req; ?>" value="checked" <?php echo $street_address2_req_value; ?> /></td>
                                <td>
                                    <input type="text" name="<?php echo $street_address2_label; ?>" value="<?php echo $street_address2_label_value; ?>"  />
                                </td>
                                <td><input type="text" name="<?php echo $street_address2_value_name; ?>" value="<?php echo $street_address2_value_default; ?>"  /></td>
                                <td><input type="text" name="<?php echo $street_address2_placeholder_name; ?>" value="<?php echo $street_address2_placeholder_value; ?>"  /></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $city_checkbox_name; ?>" value="checked" <?php echo $city_name_ischecked; ?> />
                                        <?php _e( 'City Name', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $city_field_type; ?>" value="<?php echo $city_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $city_req; ?>" value="checked" <?php echo $city_req_value; ?> /></td>
                                <td>
                                    <input type="text" name="<?php echo $city_label; ?>" value="<?php echo $city_label_value; ?>"  />
                                </td>
                                <td><input type="text" name="<?php echo $city_value_name; ?>" value="<?php echo $city_value_default; ?>"  /></td>
                                <td><input type="text" name="<?php echo $city_placeholder_name; ?>" value="<?php echo $city_placeholder_value; ?>"  /></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $state_checkbox_name; ?>" value="checked" <?php echo $state_ischecked; ?> />
                                        <?php _e( 'State/Region', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $state_field_type; ?>" value="<?php echo $state_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $state_req; ?>" value="checked" <?php echo $state_req_value; ?> /></td>
                                <td>
                                    <input type="text" name="<?php echo $state_label; ?>" value="<?php echo $state_label_value; ?>"  />
                                </td>
                                <td><input type="text" name="<?php echo $state_value_name; ?>" value="<?php echo $state_value_default; ?>"  /></td>
                                <td><input type="text" name="<?php echo $state_placeholder_name; ?>" value="<?php echo $state_placeholder_value; ?>"  /></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $zip_checkbox_name; ?>" value="checked" <?php echo $zip_ischecked; ?> />
                                        <?php _e( 'Zip/Postal Code', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $zip_field_type; ?>" value="<?php echo $zip_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $zip_req; ?>" value="checked" <?php echo $zip_req_value; ?> /></td>
                                <td><input type="text" name="<?php echo $zip_label; ?>" value="<?php echo $zip_label_value; ?>"  />
                                </td>
                                <td><input type="text" name="<?php echo $zip_value_name; ?>" value="<?php echo $zip_value_default; ?>"  /></td>
                                <td><input type="text" name="<?php echo $zip_placeholder_name; ?>" value="<?php echo $zip_placeholder_value; ?>"  /></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo $county_select_checkbox_name; ?>" value="checked" <?php echo $county_select_ischecked; ?> />
                                        <?php _e( 'Country', 'wpuf' ); ?>
                                        <input type="hidden" name="<?php echo $county_select_field_type; ?>" value="<?php echo $county_select_field_type_value; ?>"  />
                                    </label>
                                </td>
                                <td><input type="checkbox" name="<?php echo $county_select_req; ?>" value="checked" <?php echo $county_select_req_value; ?> /></td>
                                <td><input type="text" name="<?php echo $county_select_label; ?>" value="<?php echo $county_select_label_value; ?>"  /></td>
                                <td><input type="text" name="<?php echo $county_select_value_name; ?>" value="<?php echo $county_select_value_default; ?>"  /></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php
                        $param = array(
                            'names_to_hide' => array(
                                'name'  => $hide_country_list_name,
                                'value' => $hide_country_list_value
                            ),
                            'names_to_show' => array(
                                'name'  => $show_country_list_name ,
                                'value' => $show_country_list_value
                            ),
                            'option_to_chose' => array(
                                'name' => $country_list_visibility_opt_name,
                                'value' => $country_list_visibility_opt_value
                            )
                        );
                        self::render_drop_down_portion($param);
                    ?>
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }*/


    /**
     * Render Section start in case of multistep form
     *
     * @param $field_id
     * @param $label
     * @param $values
     *
     */
    /*public static function step_start( $field_id, $label, $values = array() ) {
        $title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $title_value = $values ? esc_attr( $values['label'] ) : 'Section';

        $step_start_name               = sprintf( '%s[%d][step_start]', self::$input_name, $field_id );
        $step_start_prev_button_name      = sprintf( '%s[%d][step_start][prev_button_text]', self::$input_name, $field_id );
        $step_start_prev_button_value     = isset( $values['step_start']['prev_button_text'] )? $values['step_start']['prev_button_text'] : 'Previous';

        $step_start_next_button_name      = sprintf( '%s[%d][step_start][next_button_text]', self::$input_name, $field_id );
        $step_start_next_button_value     = isset( $values['step_start']['next_button_text'] )? $values['step_start']['next_button_text'] : 'Next';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'step_start' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'step_start' ); ?>

            <div class="wpuf-form-holder">
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Section Name', 'wpuf' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Title', 'wpuf' ); ?>" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Previous Button Text', 'wpuf' ); ?></label>
                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Previous Button Text', 'wpuf' ); ?>" name="<?php echo $step_start_prev_button_name; ?>" value="<?php echo esc_attr( $step_start_prev_button_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Next Button Text', 'wpuf' ); ?></label>
                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Next Button Text', 'wpuf' ); ?>" name="<?php echo $step_start_next_button_name; ?>" value="<?php echo esc_attr( $step_start_next_button_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }*/


    /**
     * [country_list_field description]
     *
     * @param  int  $field_id
     * @param  string  $label
     * @param  array   $values
     *
     * @since 2.2.7
     *
     * @return void
     */
    /*public static function country_list_field( $field_id, $label, $values = array() ) {
        $country_list_name       = sprintf( '%s[%d][country_list]', self::$input_name, $field_id );
        $country_list_value      = isset( $values['country_list'] )? $values['country_list'] : '';

        $first_name              = sprintf( '%s[%d][country_list][name]', self::$input_name, $field_id );
        $first_value             = isset($values['country_list']['name']) ? $values['country_list']['name'] : ' - select -';
        $help                    = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'wpuf' ) );
        $hide_country_list_name  = sprintf( '%s[%d][country_list][country_select_hide_list][]', self::$input_name, $field_id );
        $hide_country_list_value = isset( $values['country_list']['country_select_hide_list'] )? $values['country_list']['country_select_hide_list'] : '';
        $show_country_list_name  = sprintf( '%s[%d][country_list][country_select_show_list][]', self::$input_name, $field_id );
        $show_country_list_value = isset( $values['country_list']['country_select_show_list'] )? $values['country_list']['country_select_show_list'] : '';
        $country_list_visibility_opt_name  = sprintf( '%s[%d][country_list][country_list_visibility_opt_name]', self::$input_name, $field_id );
        $country_list_visibility_opt_value = isset( $values['country_list']['country_list_visibility_opt_name'] )? $values['country_list']['country_list_visibility_opt_name'] : '';
        ?>
        <li class="custom-field dropdown_field wpuf-conditional">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'country_list' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'country_list_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Select Text', 'wpuf' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .wpuf-form-rows -->

               <?php
                $param = array(
                     'names_to_hide' => array(
                         'name' => $hide_country_list_name,
                         'value' => $hide_country_list_value
                     ),
                    'names_to_show' => array(
                        'name' => $show_country_list_name,
                        'value' => $show_country_list_value
                    ),
                    'option_to_chose' => array(
                        'name' => $country_list_visibility_opt_name,
                        'value' => $country_list_visibility_opt_value
                    )
                );
                self::render_drop_down_portion($param);
               ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }*/

    public static function render_drop_down_portion( $param = array( 'names_to_hide' => array( 'name' => '', 'value' => '' ),'names_to_show' => array( 'name' => '', 'value' => '' ),'option_to_chose' => array('name' => '', 'value' => '' ) ) ) {
        empty( $param['option_to_chose']['value'] ) ? ( $param['option_to_chose']['value'] = 'all' ) : '';

        ?>
        <div class="wpuf-form-rows">
            <label><input type="radio" name="<?php echo $param['option_to_chose']['name']  ?>" value="<?php echo _e('all','wpuf'); ?>" <?php echo ( ( $param['option_to_chose']['value'] == 'all' )?'checked':'' ); ?> /><?php _e( 'Show All', 'wpuf' ); ?></label>
        </div>
        <div class="wpuf-form-rows">
            <label><input type="radio" name="<?php echo $param['option_to_chose']['name']  ?>" value="<?php echo _e('hide','wpuf'); ?>" <?php echo ( ( $param['option_to_chose']['value'] == 'hide' )?'checked':'' ); ?>  /><?php _e( 'Hide These Countries', 'wpuf' ); ?></label>
            <select name="<?php echo $param['names_to_hide']['name'];?>" class="wpuf-country_to_hide" multiple data-placeholder="<?php esc_attr_e( 'Chose Country to hide from List', 'wpuf' ); ?>"></select>
        </div>

        <div class="wpuf-form-rows">
            <label><input type="radio" name="<?php echo $param['option_to_chose']['name']  ?>" value="<?php echo _e('show','wpuf'); ?>" <?php echo ( ( $param['option_to_chose']['value'] == 'show' )?'checked':'' ); ?>  /><?php _e( 'Show These Countries', 'wpuf' ); ?></label>
            <select name="<?php echo $param['names_to_show']['name'];?>" class="wpuf-country_to_hide" multiple data-placeholder="<?php esc_attr_e( 'Add Country to List', 'wpuf' ); ?>"></select>
        </div>

        <script>
            (function($){
                $(document).ready(function(){
                    var hide_field_name = '<?php echo $param['names_to_hide']['name'];?>';
                    var hide_field_value = JSON.parse('<?php echo json_encode($param['names_to_hide']['value']);?>');
                    var show_field_name = '<?php echo $param['names_to_show']['name'];?>';
                    var show_field_value = JSON.parse('<?php echo json_encode($param['names_to_show']['value']);?>');
                    var countries = <?php echo file_get_contents(WPUF_ASSET_URI . '/js/countries.json');?>;
                    var hide_field_option_string = '';
                    var show_field_option_string = '';

                    for(country in countries){
                        hide_field_option_string = hide_field_option_string + '<option value="'+ countries[country].code +'" '+ (( $.inArray(countries[country].code,hide_field_value) != -1 )?'selected':'') +'>'+ countries[country].name +'</option>';
                        show_field_option_string = show_field_option_string + '<option value="'+ countries[country].code +'" '+ (( $.inArray(countries[country].code,show_field_value) != -1 )?'selected':'') +'>'+ countries[country].name +'</option>';
                    }

                    jQuery('select[name="'+ hide_field_name +'"]').html(hide_field_option_string);
                    jQuery('select[name="'+ show_field_name +'"]').html(show_field_option_string);
                    jQuery('select[name="'+ hide_field_name +'"],select[name="'+ show_field_name +'"]').chosen({allow_single_deselect:true});
                })

            }(jQuery))

        </script>
        <?php
    }

}
