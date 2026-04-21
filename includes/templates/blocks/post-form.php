<?php
// DESCRIPTION: Frontend template for the wpuf/post-form block.
// Outputs a scoped <style> block and renders the post form via the [wpuf_form] shortcode.

/**
 * Available variables passed from PostForm::render():
 *
 * @var string $block_id       Unique block instance ID for CSS scoping.
 * @var array  $block_config   Sanitised block attributes for styling.
 * @var int    $form_id        Post form ID.
 * @var string $label_position 'above'|'left'|'right'|'hidden'.
 * @var string $wrapper_attrs  Extra HTML attributes for the wrapper div (from get_block_wrapper_attributes()).
 */

$scope = '#' . esc_attr( $block_id );

// Shared selector keys referenced by both the main rules array and label_position overrides.
// Scoped via .wpuf-form to beat theme specificity on the radio/checkbox option labels.
$label_selector = "{$scope} .wpuf-label, {$scope} .wpuf-form label.wpuf-checkbox-block, {$scope} .wpuf-form label.wpuf-radio-block";

// Build per-selector CSS rules from block_config
$css_rules = [
    $label_selector => [
        'font-family' => $block_config['label_font_family'],
        'font-size'   => $block_config['label_font_size'],
        'font-weight' => $block_config['label_font_weight'],
        'font-style'  => $block_config['label_font_style'],
        'color'       => $block_config['label_color'],
    ],
    "{$scope} .wpuf-form label.wpuf-checkbox-block input,
     {$scope} .wpuf-form label.wpuf-radio-block input" => [
        'width'  => $block_config['label_font_size'],
        'height' => $block_config['label_font_size'],
    ],
    "{$scope} .wpuf-form-sub-label, {$scope} .wpuf-help, {$scope} .wpuf-help-text" => [
        'font-family' => $block_config['help_text_font_family'],
        'font-size'   => $block_config['help_text_font_size'],
        'font-weight' => $block_config['help_text_font_weight'],
        'font-style'  => $block_config['help_text_font_style'],
        'color'       => $block_config['help_text_color'],
    ],
    "{$scope} .wpuf-form input[type='text'],
     {$scope} .wpuf-form input[type='email'],
     {$scope} .wpuf-form input[type='password'],
     {$scope} .wpuf-form input[type='url'],
     {$scope} .wpuf-form input[type='number'],
     {$scope} .wpuf-form textarea" => [
        'border-top-width'     => $block_config['field_border']['top']['width'],
        'border-top-style'     => $block_config['field_border']['top']['style'],
        'border-top-color'     => $block_config['field_border']['top']['color'],
        'border-right-width'   => $block_config['field_border']['right']['width'],
        'border-right-style'   => $block_config['field_border']['right']['style'],
        'border-right-color'   => $block_config['field_border']['right']['color'],
        'border-bottom-width'  => $block_config['field_border']['bottom']['width'],
        'border-bottom-style'  => $block_config['field_border']['bottom']['style'],
        'border-bottom-color'  => $block_config['field_border']['bottom']['color'],
        'border-left-width'    => $block_config['field_border']['left']['width'],
        'border-left-style'    => $block_config['field_border']['left']['style'],
        'border-left-color'    => $block_config['field_border']['left']['color'],
        'border-radius'    => $block_config['field_border_radius'],
        'padding'          => $block_config['field_padding_v'] . ' ' . $block_config['field_padding_h'],
        'background-color' => $block_config['field_background_color'],
        'color'            => $block_config['field_text_color'],
        'font-family'      => $block_config['field_font_family'],
        'font-size'        => $block_config['field_font_size'],
        'font-weight'      => $block_config['field_font_weight'],
        'font-style'       => $block_config['field_font_style'],
        'box-shadow'       => 'none',
    ],
    "{$scope} .wpuf-form select" => [
        'border-top-width'     => $block_config['field_border']['top']['width'],
        'border-top-style'     => $block_config['field_border']['top']['style'],
        'border-top-color'     => $block_config['field_border']['top']['color'],
        'border-right-width'   => $block_config['field_border']['right']['width'],
        'border-right-style'   => $block_config['field_border']['right']['style'],
        'border-right-color'   => $block_config['field_border']['right']['color'],
        'border-bottom-width'  => $block_config['field_border']['bottom']['width'],
        'border-bottom-style'  => $block_config['field_border']['bottom']['style'],
        'border-bottom-color'  => $block_config['field_border']['bottom']['color'],
        'border-left-width'    => $block_config['field_border']['left']['width'],
        'border-left-style'    => $block_config['field_border']['left']['style'],
        'border-left-color'    => $block_config['field_border']['left']['color'],
        'border-radius'    => $block_config['field_border_radius'],
        'background-color' => $block_config['field_background_color'],
        'color'            => $block_config['field_text_color'],
        'font-family'      => $block_config['field_font_family'],
        'font-size'        => $block_config['field_font_size'],
        'font-weight'      => $block_config['field_font_weight'],
        'font-style'       => $block_config['field_font_style'],
        'box-shadow'       => 'none',
    ],
    "{$scope} .wpuf-form input::placeholder,
     {$scope} .wpuf-form textarea::placeholder" => [
        'color' => $block_config['field_placeholder_color'],
    ],
    "{$scope} .wpuf-form select[multiple]" => [
        'background-image' => 'none',
        'padding-right'    => $block_config['field_padding_h'],
    ],
    "{$scope} .wpuf-form input:focus,
     {$scope} .wpuf-form select:focus,
     {$scope} .wpuf-form textarea:focus" => [
        'border-color' => $block_config['field_focus_border_color'],
        'outline'      => 'none',
    ],
    "{$scope} .wpuf-form li" => [
        'margin-bottom' => $block_config['field_margin_bottom'],
    ],
    "{$scope} .wpuf-form input[type='submit'],
     {$scope} .wpuf-form button[type='submit']" => [
        'font-size'        => $block_config['button_font_size'],
        'font-family'      => $block_config['button_font_family'],
        'font-weight'      => $block_config['button_font_weight'],
        'font-style'       => $block_config['button_font_style'],
        'border-width'     => $block_config['button_border_width'],
        'border-style'     => '0px' !== $block_config['button_border_width'] ? 'solid' : 'none',
        'border-color'     => $block_config['button_border_color'],
        'border-radius'    => $block_config['button_border_radius'],
        'padding'          => $block_config['button_padding_v'] . ' ' . $block_config['button_padding_h'],
        'background-color' => '' !== $block_config['button_background_color'] ? $block_config['button_background_color'] . ' !important' : '',
        'color'            => '' !== $block_config['button_text_color'] ? $block_config['button_text_color'] . ' !important' : '',
        'box-shadow'       => 'none !important',
        'text-shadow'      => 'none !important',
        'cursor'           => 'pointer',
    ],
    "{$scope} .wpuf-form a.file-selector" => [
        'font-size'        => $block_config['upload_btn_font_size'],
        'font-family'      => $block_config['upload_btn_font_family'],
        'font-weight'      => $block_config['upload_btn_font_weight'],
        'font-style'       => $block_config['upload_btn_font_style'],
        'border-width'     => $block_config['upload_btn_border_width'],
        'border-style'     => '0px' !== $block_config['upload_btn_border_width'] ? 'solid' : 'none',
        'border-color'     => $block_config['upload_btn_border_color'],
        'border-radius'    => $block_config['upload_btn_border_radius'],
        'padding'          => $block_config['upload_btn_padding_v'] . ' ' . $block_config['upload_btn_padding_h'],
        'background-color' => '' !== $block_config['upload_btn_background_color'] ? $block_config['upload_btn_background_color'] . ' !important' : '',
        'color'            => '' !== $block_config['upload_btn_text_color'] ? $block_config['upload_btn_text_color'] . ' !important' : '',
        'box-shadow'       => 'none !important',
        'text-shadow'      => 'none !important',
        'text-decoration'  => 'none',
        'cursor'           => 'pointer',
        'display'          => 'block',
        'width'            => '93.5%',
    ],
    "{$scope} .wpuf-el.featured_image a.file-selector,
     {$scope} .wpuf-el.image_upload a.file-selector" => [
        'background-color' => '' !== $block_config['upload_btn_background_color'] ? $block_config['upload_btn_background_color'] . ' !important' : '#fafafa !important',
        'color'            => '' !== $block_config['upload_btn_text_color'] ? $block_config['upload_btn_text_color'] . ' !important' : '#50575e !important',
        'border-color'     => '' !== $block_config['upload_btn_border_color'] ? $block_config['upload_btn_border_color'] : '#dddddd',
    ],
    "{$scope} .wpuf-error"                           => [ 'color' => $block_config['error_message_color'] ],
    "{$scope} .wpuf-success, {$scope} .wpuf-message" => [ 'color' => $block_config['success_message_color'] ],
];

// Label position overrides
switch ( $label_position ) {
    case 'left':
        $css_rules[ "{$scope} .wpuf-form li" ] = array_merge(
            $css_rules[ "{$scope} .wpuf-form li" ],
            [
                'display'               => 'grid',
                'grid-template-columns' => '30% 1fr',
                'align-items'           => 'center',
                'gap'                   => '8px',
            ]
        );
        break;
    case 'right':
        $css_rules[ "{$scope} .wpuf-form li" ] = array_merge(
            $css_rules[ "{$scope} .wpuf-form li" ],
            [
                'display'               => 'grid',
                'grid-template-columns' => '1fr 30%',
                'align-items'           => 'center',
                'gap'                   => '8px',
            ]
        );
        $css_rules[ $label_selector ] = array_merge(
            $css_rules[ $label_selector ],
            [ 'grid-column' => '2', 'grid-row' => '1', 'text-align' => 'left' ]
        );
        $css_rules[ "{$scope} .wpuf-fields" ] = [ 'grid-column' => '1', 'grid-row' => '1' ];
        break;
    case 'hidden':
        // Hide only the field's own label wrapper — NOT label.wpuf-checkbox-block /
        // label.wpuf-radio-block, which wrap the actual option inputs + text.
        $css_rules[ "{$scope} .wpuf-label" ] = [
            'position'    => 'absolute',
            'width'       => '1px',
            'height'      => '1px',
            'padding'     => '0',
            'margin'      => '-1px',
            'overflow'    => 'hidden',
            'clip'        => 'rect(0,0,0,0)',
            'white-space' => 'nowrap',
            'border'      => '0',
        ];
        break;
}

// Build scoped CSS string — skip empty values
$style_output = '';
foreach ( $css_rules as $selector => $properties ) {
    $declarations = '';
    foreach ( $properties as $prop => $value ) {
        if ( '' !== $value && null !== $value ) {
            $declarations .= esc_attr( $prop ) . ':' . esc_attr( $value ) . ';';
        }
    }
    if ( $declarations ) {
        $style_output .= $selector . '{' . $declarations . '}';
    }
}

$upload_icon_svg   = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='#50575e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4'/><polyline points='17 8 12 3 7 8'/><line x1='12' y1='3' x2='12' y2='15'/></svg>";
$upload_icon_url   = 'data:image/svg+xml;utf8,' . rawurlencode( $upload_icon_svg );
$upload_btn_scope  = $scope . ' .wpuf-el.featured_image a.file-selector, ' . $scope . ' .wpuf-el.image_upload a.file-selector';

$style_output .= $upload_btn_scope . '{padding-left:34px;background-image:url("' . $upload_icon_url . '");background-repeat:no-repeat;background-position:10px center;background-size:18px 18px;}';
$style_output .= $scope . ' .wpuf-el.post_content .wp-editor-wrap{width:96.5%;}';
$style_output .= $scope . ' .wpuf-el.dropdown select, ' . $scope . ' .wpuf-el.multi_select select{width:96.5%;}';
?>

<?php if ( $style_output ) : ?>
<style id="<?php echo esc_attr( $block_id ); ?>-styles"><?php echo $style_output; // Dynamic rules escaped per-property; static block is plain-ASCII CSS with URL-encoded SVG ?></style>
<?php endif; ?>

<div id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

    <?php
    // When rendering inside the block editor (via ServerSideRender REST request),
    // skip gating so the admin can see the form preview.
    $is_editor_preview = defined( 'REST_REQUEST' ) && REST_REQUEST;
    ?>

    <?php if ( $form_id > 0 ) : ?>
        <?php
        $shortcode_output = do_shortcode( '[wpuf_form id="' . intval( $form_id ) . '"]' );

        if ( $is_editor_preview ) {
            // Strip inline scripts so multistep/conditional JS does not execute
            // inside ServerSideRender and break the block editor.
            $shortcode_output = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $shortcode_output );
        }

        echo $shortcode_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    <?php else : ?>
        <p class="wpuf-post-form-block__empty">
            <?php esc_html_e( 'Please select a post form to display.', 'wp-user-frontend' ); ?>
        </p>
    <?php endif; ?>

</div>
