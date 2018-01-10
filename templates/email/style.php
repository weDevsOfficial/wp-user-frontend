<?php
/**
 * Email Styles
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$settings = get_option( 'wpuf_mails' );

$bg              = $settings['background_color'];
$body            = $settings['body_background_color'];
$base            = $settings['base_color'];
$text            = $settings['body_text_color'];

if ( isset( $settings['header_image'] ) && !empty( $settings['header_image'] ) ) {
    $header_image = $settings['header_image'];
}
// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
#wrapper {
    margin: 0;
    padding: 60px 0 0 0;
    width: 100%;
    -webkit-text-size-adjust: none !important;
    background-color: <?php echo esc_attr( $bg ); ?>;
    background-repeat: no-repeat;
    background-position: bottom;
}

.button {
    background-color:#4CAF50;
    border-radius:3px;
    color:#ffffff;
    display:inline-block;
    font-family:sans-serif;
    font-size:13px;
    font-weight:bold;
    line-height: 150%;
    text-align:center;
    text-decoration:none;
    -webkit-text-size-adjust:none;
    padding: 8px 20px;
}

.button.sm {
    padding: 5px 10px;
}

.button.green {
    background-color: #4CAF50;
}

.button.orange {
    background-color: #FF9800;
}

.button.blue {
    background-color: #2196F3;
}

#template_container {
    background-color: <?php echo esc_attr( $body ); ?>;
}

#template_header {
    color: #444;
    border-bottom: 0;
    font-weight: bold;
    line-height: 100%;
    vertical-align: middle;
    border-radius: 5px;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_footer td {
    padding: 0;
}

#template_footer #credit {
    border:0;
    font-family: Arial;
    font-size:14px;
    text-align:center;
    padding: 20px 0;
    color: <?php echo esc_attr( $text ); ?>;
}

#body_content {
    background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
    padding: 20px 20px;
}

#body_content table td td {
    padding: 12px;
}

#body_content table td th {
    padding: 12px;
}

#body_content p {
    margin: 0 0 20px;
}

#body_content_inner {
    color: <?php echo esc_attr( $text ); ?>;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 14px;
    line-height: 170%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
    color: <?php echo esc_attr( $text ); ?>;
    border: none;
}

.text {
    color: <?php echo esc_attr( $text ); ?>;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#body_content_inner tr.field-label th {
    padding: 6px 12px;
    background-color: #f8f8f8;
}

#body_content_inner tr.field-value td {
    padding: 6px 12px 12px 12px;
}

.link {
    color: <?php echo esc_attr( $base ); ?>;
}

#header_wrapper {
    padding: 60px 0 30px 0;
    display: block;
    background-color: <?php echo esc_attr( $base ); ?>;
    border-radius: 5px 5px 0 0;
}

#header_image{
    width: 100px;
    height: 100px;
    margin: 0 auto;
    margin-bottom: 40px;
}

h1 {
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 24px;
    color: #fff;
    font-weight: 300;
    line-height: 22px;
    margin: 0;
    text-align: <?php echo is_rtl() ? 'right' : 'center'; ?>;
    -webkit-font-smoothing: antialiased;
}

h2 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 18px;
    font-weight: bold;
    line-height: 130%;
    margin: .5em 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
    line-height: 130%;
    margin: .5em 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#body_content_inner h1 {
    margin: 0 0 .5em 0;
}

#body_content_inner p + h1,
#body_content_inner p + h2,
#body_content_inner p + h3,
#body_content_inner p + h4 {
    margin-top: 2em;
}

a {
    color: <?php echo esc_attr( $base ); ?>;
    font-weight: normal;
    text-decoration: underline;
}

img {
    border: none;
    display: inline;
    font-size: 14px;
    font-weight: bold;
    height: auto;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    text-transform: capitalize;
}
