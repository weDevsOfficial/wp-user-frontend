<?php
/**
 * If the user isn't logged in, redirect
 * to the login page
 *
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_auth_redirect_login() {
    $user = wp_get_current_user();

    if ( $user->id == 0 ) {
        nocache_headers();
        wp_redirect(get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

/**
 * Utility function for debugging
 *
 * @since version 0.1
 * @author Tareq Hasan
 */
if (!function_exists('d')) {
    function d($param) {
        echo "<pre>";
        print_r($param);
        echo "</pre>";
    }
}

/**
 * Format the post status for user dashboard
 *
 * @param string $status
 * @since version 0.1
 * @author Tareq Hasan
 */
function wpuf_show_post_status($status) {

    if ($status == 'publish') {

        $title = __('Live', 'your-gig');
        $fontcolor = '#33CC33';

    } else if ($status == 'draft') {

        $title = __('Offline', 'your-gig');
        $fontcolor = '#bbbbbb';

    } else if ($status == 'pending') {

        $title = __('Awaiting Approval', 'your-gig');
        $fontcolor = '#C00202';
    }

    echo '<span style="color:' . $fontcolor . ';">'. $title . '</span>';
}


function wpuf_post_form_style() {
    ?>
<style type="text/css">
    .wpuf-post-form{
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .wpuf-post-form li{
        margin: 5px 0;
        padding: 0;
    }

    .wpuf-post-form label {
        float: left;
        font-weight: bold;
        height: 20px;
        margin: 0;
        min-width: 130px;
        padding: 0 10px 0 0;
        font-size: 12px;
    }

    .wpuf-post-form input, .wpuf-post-form textarea, .wpuf-post-form select{
        margin: 0;
        padding: 5px;
        border: 1px solid #ccc;
        font-size: 12px;
    }
    .wpuf-post-form input[type=text]{
        width: 50%;
    }

    .wpuf-post-form textarea{
        width: 70%;
    }

    .wpuf-post-form input[type=submit], .wpuf-submit{
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        color: #333;
        padding: 5px 10px;
        border: 1px solid #ccc;
        text-shadow: 0 1px 0 #FFFFFF;
        background: #eeeeee; /* old browsers */
        background: -moz-linear-gradient(top, #eeeeee 0%, #cccccc 100%); /* firefox */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#eeeeee), color-stop(100%,#cccccc)); /* webkit */
    }

    .clear{
        clear:both;
        height:0;
        font-size: 1px;
        line-height: 0px;
    }

    .success {
        background-color: #DFF2BF;
        border: 1px solid #BCDF7D;
        color: #4F8A10;
        padding: 10px;
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 10px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        text-shadow: 0 1px 0 #FFFFFF;
    }

    .error {
        margin: 0 10px 10px 10px;
        padding: 10px;
        color: #D8000C;
        background-color: #FFBABA;
        border: solid 1px #dd3c10;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        font-weight: bold;
        text-shadow: 0 1px 0 #FFFFFF;
    }

    #content .wpuf-profile table, .wpuf-profile table{
        border: none;
    }

    #content .wpuf-profile th, #content .wpuf-profile td, .wpuf-profile th, .wpuf-profile td{
        vertical-align: top;
        border-top: 1px solid #eee;
        border: none;
    }

    #content .wpuf-profile input, .wpuf-profile input{
        margin: 0;
    }

    .wpuf-profile h3, #content .wpuf-profile h3{
        margin: 0;
    }

    .wpuf-profile table, #content .wpuf-profile table{
        margin: 0 0 5px 0;
    }
</style>
    <?php
}

/**
 * Format error message
 *
 * @param array $error_msg
 * @return string
 */
function wpuf_error_msg($error_msg) {
    $msg_string = '';
    foreach ($error_msg as $value) {
        if(!empty($value)) {
            $msg_string = $msg_string . '<div class="error">' . $msg_string = $value.'</div>';
        }
    }
    return $msg_string;
}

// for the price field to make only numbers, periods, and commas
function wpuf_clean_tags($string) {
    $string = preg_replace('/\s*,\s*/', ',', rtrim(trim($string), ' ,'));
    return $string;
}

/**
 * Validates any integer variable and sanitize
 *
 * @param int $int
 * @return intger
 */
function wpuf_is_valid_int($int) {
    $int = isset($int) ? intval($int) : 0;
    return $int;
}

function wpuf_notify_post_mail() {
    $user = get_userdata($user_id);

    $headers = "From: " . get_bloginfo('name') . " <" . get_bloginfo('admin_email') . ">" . "\r\n\\";
    $subject = '[' . get_bloginfo('name') . '] New Post Submission';
    $msg = 'There is a new post submitted in "'.get_bloginfo('name').'". Visit '.admin_url('edit.php').' to take action';
    //$receiver = $user->user_email;
    $receiver = get_bloginfo('admin_email');
    wp_mail($receiver, $subject, $msg, $headers);
    //var_dump($headers, $subject, $msg, $receiver);
}
