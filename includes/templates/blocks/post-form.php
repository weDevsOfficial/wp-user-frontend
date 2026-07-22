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

// When rendering inside the block editor (via ServerSideRender REST request),
// skip gating so the admin can see the form preview.
$is_editor_preview = defined( 'REST_REQUEST' ) && REST_REQUEST;

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

// Multistep progressbar color overrides.
// Block attributes take priority; fall back to form settings; then defaults.
$is_multistep  = false;
$ms_active_bg  = '';
$ms_active_txt = '';
$ms_inactive   = '';

if ( $form_id > 0 ) {
    $form_settings_meta = get_post_meta( $form_id, 'wpuf_form_settings', true );
    $is_multistep       = ! empty( $form_settings_meta['enable_multistep'] )
        && in_array( $form_settings_meta['enable_multistep'], [ 'on', 'yes' ], true );

    if ( $is_multistep ) {
        $ms_active_bg  = $block_config['ms_active_bg_color'] ?: ( $form_settings_meta['ms_active_bgcolor'] ?? '#00a0d2' );
        $ms_active_txt = $block_config['ms_active_text_color'] ?: ( $form_settings_meta['ms_ac_txt_color'] ?? '#ffffff' );
        $ms_inactive   = $block_config['ms_inactive_bg_color'] ?: ( $form_settings_meta['ms_bgcolor'] ?? '#e4e4e4' );

        // Step-by-step style: inactive step background + arrow
        $css_rules["{$scope} .wpuf-multistep-progressbar ul.wpuf-step-wizard li"] = [
            'background-color' => $ms_inactive . ' !important',
            'background'       => $ms_inactive . ' !important',
        ];
        $css_rules["{$scope} .wpuf-multistep-progressbar ul.wpuf-step-wizard li::after"] = [
            'border-left-color' => $ms_inactive . ' !important',
        ];

        // Step-by-step style: active step background, text + arrow
        $css_rules["{$scope} .wpuf-multistep-progressbar ul.wpuf-step-wizard li.active-step"] = [
            'background-color' => $ms_active_bg . ' !important',
            'color'            => $ms_active_txt . ' !important',
        ];
        $css_rules["{$scope} .wpuf-multistep-progressbar ul.wpuf-step-wizard li.active-step::after"] = [
            'border-left-color' => $ms_active_bg . ' !important',
        ];

        // Progressive style: bar track (inactive), filled portion (active), percentage text.
        // Only apply background on .wpuf-multistep-progressbar for progressive type;
        // step-by-step uses circles, not a filled track.
        //
        // Skip in editor preview — the preview injects its own static bar with an
        // inline-styled track, so painting the outer wrapper would show through the
        // preview's inner track and create a wrong-colored band.
        $ms_progressbar_type = $form_settings_meta['multistep_progressbar_type'] ?? 'progressive';

        if ( 'progressive' === $ms_progressbar_type && ! $is_editor_preview ) {
            $css_rules["{$scope} .wpuf-multistep-progressbar"] = [
                'background-color' => $ms_inactive . ' !important',
                'background'       => $ms_inactive . ' !important',
            ];
        }

        $css_rules["{$scope} .wpuf-multistep-progressbar .ui-widget-header"] = [
            'background' => $ms_active_bg . ' !important',
        ];
        $css_rules["{$scope} .wpuf-multistep-progressbar .wpuf-progress-percentage"] = [
            'color' => $ms_active_txt . ' !important',
        ];

        // Prev / Next button styles
        $css_rules["{$scope} .wpuf-form .wpuf-multistep-prev-btn,
         {$scope} .wpuf-form .wpuf-multistep-next-btn"] = [
            'font-size'        => $block_config['ms_btn_font_size'],
            'font-family'      => $block_config['ms_btn_font_family'],
            'font-weight'      => $block_config['ms_btn_font_weight'],
            'font-style'       => $block_config['ms_btn_font_style'],
            'border-width'     => $block_config['ms_btn_border_width'],
            'border-style'     => '0px' !== $block_config['ms_btn_border_width'] ? 'solid' : 'none',
            'border-color'     => $block_config['ms_btn_border_color'],
            'border-radius'    => $block_config['ms_btn_border_radius'],
            'padding'          => $block_config['ms_btn_padding_v'] . ' ' . $block_config['ms_btn_padding_h'],
            'background-color' => '' !== $block_config['ms_btn_background_color'] ? $block_config['ms_btn_background_color'] . ' !important' : '',
            'color'            => '' !== $block_config['ms_btn_text_color'] ? $block_config['ms_btn_text_color'] . ' !important' : '',
            'box-shadow'       => 'none !important',
            'text-shadow'      => 'none !important',
            'cursor'           => 'pointer',
        ];
    }
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

    <?php if ( $form_id > 0 ) : ?>
        <?php
        $shortcode_output = do_shortcode( '[wpuf_form id="' . intval( $form_id ) . '"]' );

        if ( $is_editor_preview ) {
            // Strip inline scripts so multistep/conditional JS does not execute
            // inside ServerSideRender and break the block editor.
            $shortcode_output = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $shortcode_output );

            // In the editor preview, <style> blocks from the SSR response do not
            // apply to the rendered content. Apply multistep colors as inline
            // styles directly on the progressbar element instead.
            // $is_multistep, $ms_active_bg, $ms_active_txt, $ms_inactive are
            // already resolved in the $css_rules section above.
            if ( $is_multistep ) {
                // Extract step count and progressbar type from shortcode output
                $fieldset_count = substr_count( $shortcode_output, 'wpuf-multistep-fieldset' );
                $total_steps    = $fieldset_count + 1;

                preg_match( '/name="wpuf_multistep_type"\s+value="([^"]*)"/', $shortcode_output, $type_match );
                $progressbar_type = $type_match[1] ?? 'progressive';

                // Extract step labels from <legend> tags
                preg_match_all( '/<legend>(.*?)<\/legend>/is', $shortcode_output, $legend_matches );
                $step_labels = array_map( 'trim', $legend_matches[1] ?? [] );
                array_unshift( $step_labels, __( 'Step 1', 'wp-user-frontend' ) );

                // Build static preview HTML for the progressbar
                if ( 'step_by_step' === $progressbar_type ) {
                    // Outer wrapper with a horizontal line running through the center of the circles
                    $steps_html  = '<div style="position: relative; margin: 20px 0 40px; padding: 0 16px;">';
                    // Connector line — neutral grey, vertically centered at circle midpoint
                    $steps_html .= '<div style="position: absolute; top: 30%; left: 48px; right: 48px; height: 2px; background: #ccc;"></div>';
                    $steps_html .= '<ul style="margin: 0; padding: 0; list-style: none; display: flex; justify-content: space-between; position: relative;">';

                    for ( $i = 0; $i < $total_steps; $i++ ) {
                        $is_active     = ( 0 === $i );
                        $label         = $step_labels[ $i ] ?? sprintf( /* translators: %d: step number */ __( 'Step %d', 'wp-user-frontend' ), $i + 1 );
                        $circle_bg     = $is_active ? $ms_active_bg : $ms_inactive;
                        $circle_border = $is_active ? $ms_active_bg : '#ccc';
                        $circle_color  = $is_active ? $ms_active_txt : '#666';
                        $label_color   = $is_active ? $ms_active_bg : '#666';

                        $steps_html .= '<li style="display: flex; flex-direction: column; align-items: center;">';

                        // Circle
                        $steps_html .= sprintf(
                            '<span style="width: 34px; height: 34px; border-radius: 50%%; background: %s; border: 2px solid %s; color: %s; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; position: relative; z-index: 1;">%d</span>',
                            esc_attr( $circle_bg ),
                            esc_attr( $circle_border ),
                            esc_attr( $circle_color ),
                            $i + 1
                        );

                        // Label
                        $steps_html .= sprintf(
                            '<span style="font-size: 12px; margin-top: 6px; color: %s; text-align: center;">%s</span>',
                            esc_attr( $label_color ),
                            esc_html( $label )
                        );

                        $steps_html .= '</li>';
                    }

                    $steps_html .= '</ul>';
                    $steps_html .= '</div>';
                } else {
                    // Progressive style
                    $progress_pct = round( ( 1 / $total_steps ) * 100 );
                    $steps_html   = '<div style="margin-bottom: 30px;">';
                    $steps_html  .= sprintf(
                        '<div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #666;">'
                        . '<span>%s</span><span>%d%%</span></div>',
                        /* translators: %d: total number of steps */
                        esc_html( sprintf( __( 'Step 1 of %d', 'wp-user-frontend' ), $total_steps ) ),
                        $progress_pct
                    );
                    $steps_html .= sprintf(
                        '<div style="height: 20px; background: %s; border-radius: 3px; overflow: hidden; border: 1px solid #eee;">'
                        . '<div style="height: 100%%; width: %d%%; background: %s; border-radius: 3px;"></div>'
                        . '</div>',
                        esc_attr( $ms_inactive ),
                        $progress_pct,
                        esc_attr( $ms_active_bg )
                    );
                    $steps_html .= '</div>';
                }

                // Replace the empty progressbar div with the static preview
                $shortcode_output = str_replace(
                    '<div class="wpuf-multistep-progressbar"></div>',
                    '<div class="wpuf-multistep-progressbar">' . $steps_html . '</div>',
                    $shortcode_output
                );

                // Hide legends and non-first fieldsets; keep nav buttons visible
                // but disabled so the preview shows them without JS interaction.
                // Uses inline styles since <style> blocks don't apply in SSR responses.
                $shortcode_output = preg_replace(
                    '/<legend>(.*?)<\/legend>/is',
                    '<legend style="display:none;"></legend>',
                    $shortcode_output
                );
                $shortcode_output = preg_replace(
                    '/<button\s+class="wpuf-multistep-(prev|next)-btn([^"]*)"/',
                    '<button disabled class="wpuf-multistep-$1-btn$2"',
                    $shortcode_output
                );

                // Show only the first fieldset, hide the rest
                $fieldset_index   = 0;
                $shortcode_output = preg_replace_callback(
                    '/<fieldset class="wpuf-multistep-fieldset">/',
                    function () use ( &$fieldset_index ) {
                        $style = ( $fieldset_index > 0 ) ? 'display:none;' : 'display:block;';
                        $fieldset_index++;
                        return '<fieldset class="wpuf-multistep-fieldset" style="' . $style . '">';
                    },
                    $shortcode_output
                );
            }
        }

        echo $shortcode_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    <?php else : ?>
        <p class="wpuf-post-form-block__empty">
            <?php esc_html_e( 'Please select a post form to display.', 'wp-user-frontend' ); ?>
        </p>
    <?php endif; ?>

</div>
