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
     * Drop Down portion
     * @param array $param
     */
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
