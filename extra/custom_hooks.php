<?php
$wpuf_enable_post_date = get_option( 'wpuf_enable_post_date' );
$wpuf_enable_post_expiry = get_option( 'wpuf_enable_post_expiry' );

function wpuf_add_post_publish_date() {
    $timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
    $month = date_i18n('m');
    $month_array = array(
        '01' => 'Jan',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Apr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Aug',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dec'
    );
    ?>
    <li>
        <label for="timestamp-wrap">
            <?php _e( 'Publish Time:', 'wpuf' ); ?> <span class="required">*</span>
        </label>
        <div class="timestamp-wrap">
            <select name="mm">
                <?php
                foreach( $month_array as $key => $val ) {
                    $selected = ( $key == $month ) ? ' selected="selected"' : '';
                    echo '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
                }
                ?>
            </select>
            <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n('d'); ?>" name="jj">,
            <input type="text" autocomplete="off" tabindex="4" maxlength="4" size="4" value="<?php echo date_i18n('Y'); ?>" name="aa">
            @ <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n('G'); ?>" name="hh">
            : <input type="text" autocomplete="off" tabindex="4" maxlength="2" size="2" value="<?php echo date_i18n('i'); ?>" name="mn">
        </div>
        <div class="clear"></div>
        <p class="description"></p>
    </li>
    <?php
}
if( $wpuf_enable_post_date == 'yes' ) {
    add_action( 'wpuf_add_post_form_after_description', 'wpuf_add_post_publish_date', 9 );
}

function wpuf_add_post_end_date() {
    ?>
    <li>
        <label for="timestamp-wrap">
            <?php _e( 'Expiration Time:', 'wpuf' ); ?><span class="required">*</span>
        </label>
        <select name="expiration-date">
            <?php for( $i = 2; $i <= 72; $i++ ) {
                if( $i%2 != 0 ) continue;
                echo '<option value="' . $i . '">' . $i . '</option>';
            }
            ?>
        </select>
        <div class="clear"></div>
        <p class="description"><?php _e( 'Post expiration time in hour after publishing.', 'wpuf' ); ?></p>
    </li>
    <?php
}

if( $wpuf_enable_post_expiry == 'yes' ) {
    add_action( 'wpuf_add_post_form_after_description', 'wpuf_add_post_end_date', 10 );
}

function wpuf_hooks_validation( $errors ) {
    $month = $_POST['mm'];
    $day = $_POST['jj'];
    $year = $_POST['aa'];
    $hour = $_POST['hh'];
    $min = $_POST['mn'];

    if( ! checkdate( $month, $day, $year ) ) {
        $errors[] = "Invalid date";
    }
    //var_dump( $_POST ); die();

    return $errors;
}

if( $wpuf_enable_post_date == 'yes' ) {
    add_filter('wpuf_add_post_validation', 'wpuf_hooks_validation', 10, 1);
}

function wpuf_hooks_add_post_args( $postdata ) {
    global $wpuf_enable_post_expiry;

    //if post expirator is activated, set the date-time
    if( $wpuf_enable_post_expiry == 'yes' ) {
        $month = $_POST['mm'];
        $day = $_POST['jj'];
        $year = $_POST['aa'];
        $hour = $_POST['hh'];
        $min = $_POST['mn'];

        $post_date = mktime($hour, $min, 59, $month, $day, $year);
        $postdata['post_date'] = date('Y-m-d H:i:s', $post_date);
    }

    //var_dump( $postdata ); die();
    return $postdata;
}

//add_filter('wpuf_add_post_args', 'wpuf_hooks_add_post_args', 9, 1);

function wpuf_hooks_add_meta( $post_id ) {
    if( !empty( $_POST['expiration-date'] ) ) {
        $post = get_post( $post_id );
        $post_date = strtotime( $post->post_date );
        $expiration = (int) $_POST['expiration-date'];
        $expiration = $post_date + ($expiration*60*60);

        add_post_meta( $post_id, 'expiration-date', $expiration, true );
    }
}
add_action( 'wpuf_add_post_after_insert', 'wpuf_hooks_add_meta', 10, 1 );


/**
 * Send payment received mail
 */
function wpuf_sub_payment_mail() {
    $headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ) . ">" . "\r\n\\";
    $subject = sprintf( __( '[%s] Payment Received', 'wpuf' ), get_bloginfo( 'name' ) );
    $msg = sprintf( __( 'New payment received at %s', 'wpuf' ), get_bloginfo( 'name' ) );

    $receiver = get_bloginfo( 'admin_email' );
    wp_mail( $receiver, $subject, $msg, $headers );
}
add_action( 'wpuf_payment_received', 'wpuf_sub_payment_mail' );
