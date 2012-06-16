<?php

function wpuf_subscription_admin() {
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2><?php _e( 'WP User Frontend', 'wpuf' ) ?>: <?php _e( 'Subscription Manager', 'wpuf' ) ?></h2>
        <?php
        switch ($action) {
            case 'edit':
                wpuf_subscription_admin_edit();
                break;

            default:
                wpuf_subscription_admin_index();
                break;
        }
        ?>
    </div>
    <?php
}

function wpuf_subscription_admin_index() {
    global $wpdb, $subscription_options;

    //save options changes
    if ( isset( $_POST['wpuf_sub_opts_submit'] ) ) {
        check_admin_referer( 'wpuf_sub_settings', 'wpuf_sub_settings' );

        //var_dump($_POST);
        //do some minimal validation
        $error = false;

        if ( $_POST['name'] == '' ) {
            $error = 'Please enter pack name';
        } else if ( $_POST['description'] == '' ) {
            $error = 'Please enter pack details';
        } else if ( $_POST['cost'] == '' ) {
            $error = 'Please enter pack cost';
        } else if ( $_POST['duration'] == '' ) {
            $error = 'Please enter pack duration';
        } else if ( $_POST['count'] == '' ) {
            $error = 'Please enter post count';
        }

        if ( !$error ) { //no errors
            //whatever, insert the values
            $data = array(
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'count' => intval( $_POST['count'] ),
                'duration' => intval( $_POST['duration'] ),
                'cost' => floatval( $_POST['cost'] ),
                'created' => current_time( 'mysql' )
            );
            //var_dump( $data );
            $result = $wpdb->insert( $wpdb->prefix . 'wpuf_subscription', $data );

            //if row inserted
            if ( $result ) {
                echo '<div class="updated"><p><strong>Field added</strong></p></div>';
            } else {
                echo '<div class="error"><p><strong>Something went wrong</strong></p></div>';
            }
        } else {
            echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
        }
    }
    ?>

    <form action="" method="post" style="margin-top: 20px;">

        <?php wp_nonce_field( 'wpuf_sub_settings', 'wpuf_sub_settings' ); ?>

        <table class="widefat meta" style="width: 850px">
            <thead>
                <tr>
                    <th scope="col" colspan="2" style="font-size: 14px;"><?php _e( 'Subscription Pack Details', 'wpuf' ) ?></th>
                </tr>
            </thead>

            <tbody>
                <tr valign="top">
                    <td scope="row" class="label"><label for="field"><?php _e( 'Pack Name', 'wpuf' ) ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="name" value="" name="name" />
                        <span class="description"><?php _e( 'subscription pack name', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="label"><?php _e( 'Pack Description', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="label" value="" name="description" />
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="help"><?php _e( 'Pack Cost', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="" name="cost" />
                        <span class="description"><?php _e( 'price/cost of the pack', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="required"><?php _e( 'Pack validity', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="" name="duration" />
                        <span class="description"><?php _e( 'How many days this pack will remain valid? Enter <strong>0</strong> for unlimited.', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="region"><?php _e( 'Number of Posts', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="" name="count" />
                        <span class="description"><?php _e( 'How many posts the user can list with this pack? Enter <strong>0</strong> for unlimited.', 'wpuf' ); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <input name="wpuf_sub_opts_submit" type="submit" class="button-primary" value="<?php _e( 'Add Package', 'wpuf' ) ?>" style="margin-top: 10px;" />

    </form>

    <h2>Subscription Packs</h2>

    <?php
    //delete service
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
        check_admin_referer( 'wpuf_del' );
        $wpdb->query( "DELETE FROM `{$wpdb->prefix}wpuf_subscription` WHERE `id`={$_REQUEST['id']};" );
        echo '<div class="updated fade" id="message"><p><strong>Pack Deleted.</strong></p></div>';
    }
    ?>

    <table class="widefat meta" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Cost</th>
                <th scope="col">Validity</th>
                <th scope="col">Post Count</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <?php
        $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_subscription ORDER BY `created` DESC", OBJECT );
        if ( $wpdb->num_rows > 0 ) {
            $count = 0;
            foreach ($fields as $row) {
                //var_dump( $row );
                ?>
                <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                    <td><?php echo stripslashes( htmlspecialchars( $row->name ) ); ?></td>
                    <td><?php echo stripslashes( htmlspecialchars( $row->description ) ); ?></td>
                    <td><?php echo $row->cost; ?> <?php echo get_option( 'wpuf_sub_currency' ); ?></td>
                    <td><?php echo ( $row->duration == 0 ) ? 'Unlimited' : $row->duration . ' days'; ?></td>
                    <td><?php echo ( $row->count == 0 ) ? 'Unlimited' : $row->count; ?></td>
                    <td>
                        <a href="admin.php?page=wpuf_subscription&action=edit&id=<?php echo $row->id; ?>"><img src="<?php echo plugins_url( 'wp-user-frontend/images/edit.png' ); ?>"</a>
                        <a href="<?php echo wp_nonce_url( "admin.php?page=wpuf_subscription&action=del&id=" . $row->id, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this pack?');"><img src="<?php echo plugins_url( 'wp-user-frontend/images/del.png' ); ?>"</a>
                    </td>

                </tr>
                <?php $count++;
            } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">Nothing Found</td>
            </tr>
        <?php } ?>

    </table>

    <?php
}

function wpuf_subscription_admin_edit() {
    global $wpdb;

    $id = intval( $_GET['id'] );

    if ( isset( $_POST['wpuf_sub_opts_submit'] ) ) {
        check_admin_referer( 'wpuf_sub_settings', 'wpuf_sub_settings' );

        //var_dump($_POST);
        //do some minimal validation
        $error = false;

        if ( $_POST['name'] == '' ) {
            $error = 'Please enter pack name';
        } else if ( $_POST['description'] == '' ) {
            $error = 'Please enter pack details';
        } else if ( $_POST['cost'] == '' ) {
            $error = 'Please enter pack cost';
        } else if ( $_POST['duration'] == '' ) {
            $error = 'Please enter pack duration';
        } else if ( $_POST['count'] == '' ) {
            $error = 'Please enter post count';
        }

        if ( !$error ) { //no errors
            //whatever, insert the values
            $data = array(
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'count' => intval( $_POST['count'] ),
                'duration' => intval( $_POST['duration'] ),
                'cost' => floatval( $_POST['cost'] )
            );
            //var_dump( $data );
            $result = $wpdb->update( $wpdb->prefix . 'wpuf_subscription', $data, array('id' => $id), array('%s', '%s', '%d', '%d', '%f'), array('%d') );

            //if row inserted
            if ( $result ) {
                echo '<div class="updated"><p><strong>Field updated</strong></p></div>';
            } else {
                echo '<div class="error"><p><strong>Something went wrong</strong></p></div>';
            }
        } else {
            echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
        }
    }

    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wpuf_subscription WHERE `id`=$id", OBJECT );
    ?>

    <form action="" method="post" style="margin-top: 20px;">

        <?php wp_nonce_field( 'wpuf_sub_settings', 'wpuf_sub_settings' ); ?>

        <table class="widefat meta" style="width: 850px">
            <thead>
                <tr>
                    <th scope="col" colspan="2" style="font-size: 14px;"><?php _e( 'Subscription Pack Details', 'wpuf' ) ?></th>
                </tr>
            </thead>

            <tbody>
                <tr valign="top">
                    <td scope="row" class="label"><label for="field"><?php _e( 'Pack Name', 'wpuf' ) ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="name" value="<?php echo esc_attr( $row->name ); ?>" name="name" />
                        <span class="description"><?php _e( 'subscription pack name', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="label"><?php _e( 'Pack Description', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="label" value="<?php echo esc_attr( $row->description ); ?>" name="description" />
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="help"><?php _e( 'Pack Cost', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="<?php echo esc_attr( $row->cost ); ?>" name="cost" />
                        <span class="description"><?php _e( 'price/cost of the pack', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="required"><?php _e( 'Pack validity', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="<?php echo esc_attr( $row->duration ); ?>" name="duration" />
                        <span class="description"><?php _e( 'How many days this pack will remain valid? Enter <strong>0</strong> for unlimited.', 'wpuf' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="label"><label for="region"><?php _e( 'Number of Posts', 'wpuf' ); ?></label></td>
                    <td>
                        <input type="text" size="25" style="" id="help" value="<?php echo esc_attr( $row->count ); ?>" name="count" />
                        <span class="description"><?php _e( 'How many posts the user can list with this pack? Enter <strong>0</strong> for unlimited.', 'wpuf' ); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <input name="wpuf_sub_opts_submit" type="submit" class="button-primary" value="<?php _e( 'Add Package', 'wpuf' ) ?>" style="margin-top: 10px;" />

    </form>

    <?php
}

