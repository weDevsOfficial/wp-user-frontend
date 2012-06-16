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

/**
 * Build custom field form for add posting form
 *
 * @global type $wpdb
 * @param type $position
 */
function wpuf_build_custom_field_form( $position = 'top', $edit = false, $post_id = 0 ) {
    global $wpdb;

    //check, if custom field is enabled
    $enabled = wpuf_get_option( 'enable_custom_field' );
    //var_dump( $enabled );
    if ( $enabled != 'on' ) {
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
