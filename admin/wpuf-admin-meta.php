<?php

function wpuf_custom_fields() {
    $action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';

    switch ($action) {
        case 'edit':
            wpuf_custom_fields_edit();
            break;

        default:
            wpuf_custom_fields_main();
            break;
    }
}

function wpuf_custom_fields_main() {
    global $custom_fields, $wpdb;
    ?>

    <div class="wrap wpuf-admin">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>Custom Field Options</h2>

        <?php
        if ( isset( $_POST['wpuf_add_custom'] ) ) {
            check_admin_referer( 'wpuf_add', 'wpuf_add' );

            //do some minimal validation
            $error = false;

            if ( $_POST['field'] == '' ) {
                $error = 'Please enter field name';
            } else if ( $_POST['label'] == '' ) {
                $error = 'Please enter label name';
            }

            if ( !$error ) { //no errors
                //whatever, insert the values
                $data = array(
                    'field' => 'cf_' . $_POST['field'],
                    'label' => $_POST['label'],
                    'desc' => $_POST['help'],
                    'required' => $_POST['required'],
                    'region' => $_POST['region'],
                    'order' => $_POST['order'],
                    'type' => $_POST['type'],
                    'values' => $_POST['field_values'],
                );
                //var_dump($data);

                $result = $wpdb->insert( $wpdb->prefix . 'wpuf_customfields', $data, array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s') );

                //if row inserted
                if ( $result ) {
                    echo '<div class="updated"><p><strong>Field added</strong></p></div>';
                } else {
                    echo '<div class="error"><p><strong>Something went wrong</strong></p></div>';
                }
            } else { //we got some error
                echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
            }
        }
        ?>

        <form action="" method="post" style="margin-top: 20px;">
            <?php wp_nonce_field( 'wpuf_add', 'wpuf_add' ); ?>
            <table class="widefat meta" style="width: 850px">
                <thead>
                    <tr>
                        <th scope="col" colspan="2" style="font-size: 14px;">Add New Custom Field</th>
                    </tr>
                </thead>

                <?php wpuf_build_form( $custom_fields ); ?>

                <tr valign="top" id="wpuf_field_values_row" style="display: none;">
                    <td scope="row" class="label"><label for="wpuf_field_values">Values</label></td>
                    <td>
                        <textarea name="field_values" id="wpuf_field_values" cols="30"></textarea>
                        <span class="description"><br>This will be used as option fields. Please separate values with comma</span>
                    </td>
                </tr>
            </table>

            <input name="wpuf_add_custom" type="submit" class="button-primary" value="<?php _e( 'Add Field' ) ?>" style="margin-top: 10px;" />

        </form>

        <h2>Custom Fields</h2>

        <?php
        //delete service
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
            check_admin_referer( 'wpuf_del' );
            $wpdb->query( "DELETE FROM `{$wpdb->prefix}wpuf_customfields` WHERE `id`={$_REQUEST['id']};" );
            echo '<div class="updated fade" id="message"><p><strong>Field Deleted.</strong></p></div>';
        }
        ?>

        <table class="widefat meta" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Type</th>
                    <th scope="col">Description</th>
                    <th scope="col">Position</th>
                    <th scope="col">Order</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <?php
            $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_customfields ORDER BY `region` DESC", OBJECT );
            if ( $wpdb->num_rows > 0 ):

                $type = array('text' => 'Text Box', 'textarea' => 'Text Area', 'select' => 'Dropdown');
                $position = array('top' => 'Top', 'description' => 'Before Description', 'tag' => 'After Description', 'bottom' => 'Bottom');

                $count = 0;
                foreach ($fields as $row):
                    //var_dump( $row );
                    if ( !wpuf_starts_with( $row->field, 'cf_' ) ) {
                        continue;
                    }
                    ?>
                    <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                        <td><?php echo stripslashes( $row->label ); ?></td>
                        <td><?php echo $type[$row->type]; ?></td>
                        <td><?php echo stripslashes( $row->desc ); ?></td>
                        <td><?php echo $position[$row->region]; ?></td>
                        <td><?php echo $row->order; ?></td>
                        <td>
                            <a href="admin.php?page=wpuf_custom_fields&action=edit&id=<?php echo $row->id; ?>"><img src="<?php echo plugins_url( 'wp-user-frontend/images/edit.png' ); ?>"</a>
                            <a href="<?php echo wp_nonce_url( "admin.php?page=wpuf_custom_fields&action=del&id=" . $row->id, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this field?');"><img src="<?php echo plugins_url( 'wp-user-frontend/images/del.png' ); ?>"</a>
                        </td>

                    </tr>
                    <?php $count++;
                endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nothing Found</td>
                </tr>
            <?php endif; ?>

        </table>

    </div>

    <?php
}

function wpuf_custom_fields_edit() {
    global $wpdb, $custom_fields;

    $id = intval( $_GET['id'] );
    ?>

    <div class="wrap">

        <?php
        //update the fields
        if ( isset( $_POST['wpuf_edit_custom'] ) ) {

            check_admin_referer( 'wpuf_edit', 'wpuf_edit' );

            $error = false;

            if ( $_POST['field'] == '' ) {
                $error = 'Please enter field name';
            } else if ( $_POST['label'] == '' ) {
                $error = 'Please enter label name';
            }

            if ( !$error ) { //no errors
                //whatever, insert the values
                if ( !wpuf_starts_with( $_POST['field'], 'cf_' ) ) {
                    $_POST['field'] = 'cf_' . $_POST['field'];
                }

                $data = array(
                    'field' => $_POST['field'],
                    'label' => $_POST['label'],
                    'desc' => $_POST['help'],
                    'required' => $_POST['required'],
                    'region' => $_POST['region'],
                    'order' => $_POST['order'],
                    'type' => $_POST['type'],
                    'values' => $_POST['field_values'],
                );
                //var_dump($data);

                $result = $wpdb->update( $wpdb->prefix . 'wpuf_customfields', $data, array('id' => $id), array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'), array('%d') );

                //if row inserted
                if ( $result ) {
                    echo '<div class="updated"><p><strong>Field Updated</strong></p></div>';
                } else {
                    echo "<div class='error'><p><strong>Something went wrong or you didn't changed anything</strong></p></div>";
                }
            } else { //we got some error
                echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
            }
        } //finished updating
        //now show it
        $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wpuf_customfields WHERE `id`=$id", OBJECT );

        $values = array(
            "field" => "$row->field",
            "label" => "$row->label",
            "help" => "$row->desc",
            "required" => "$row->required",
            "region" => "$row->region",
            "order" => "$row->order",
            "type" => "$row->type",
        );
        ?>


        <?php if ( $row ) { ?>
            <form action="" method="post" style="margin-top: 20px;">
                <?php wp_nonce_field( 'wpuf_edit', 'wpuf_edit' ); ?>
                <table class="widefat meta" style="width: 850px">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2" style="font-size: 14px;">Edit Custom Field</th>
                        </tr>
                    </thead>

                    <?php wpuf_build_form( $custom_fields, $values, false ); ?>

                    <tr valign="top" id="wpuf_field_values_row" style="display: none;">
                        <td scope="row" class="label"><label for="wpuf_field_values">Values</label></td>
                        <td>
                            <textarea name="field_values" id="wpuf_field_values" cols="30"><?php echo $row->values; ?></textarea>
                            <span class="description"><br>This will be used as option fields. Please separate values with comma</span>
                        </td>
                    </tr>
                </table>

                <input name="wpuf_edit_custom" type="submit" class="button-primary" value="<?php _e( 'Update Field' ) ?>" style="margin-top: 10px;" />

            </form>
        <?php } else { ?>
            <h2>Nothing found</h2>
        <?php } ?>

    </div>

    <?php
}