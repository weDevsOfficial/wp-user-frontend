<?php
$label_position = isset( $form_settings['label_position'] ) ? $form_settings['label_position'] : 'left';
$form_layout    = isset( $form_settings['form_layout'] ) ? $form_settings['form_layout'] : 'layout1';
?>

<table class="form-table">

    <tr class="wpuf-label-position">
        <th><?php _e( 'Label Position', 'wpuf' ); ?></th>
        <td>
            <select name="wpuf_settings[label_position]">
                <?php
                $positions = array(
                    'above'  => __( 'Above Element', 'wpuf' ),
                    'left'   => __( 'Left of Element', 'wpuf' ),
                    'right'  => __( 'Right of Element', 'wpuf' ),
                    'hidden' => __( 'Hidden', 'wpuf' ),
                );

                foreach ($positions as $to => $label) {
                    printf('<option value="%s"%s>%s</option>', $to, selected( $label_position, $to, false ), $label );
                }
                ?>
            </select>

            <p class="description">
                <?php _e( 'Where the labels of the form should display', 'wpuf' ) ?>
            </p>
        </td>
    </tr>

    <?php if( class_exists( 'WP_User_Frontend_Pro' ) ) : ?>
        <tr class="wpuf-form-layouts">
            <th><?php _e( 'Form Style', 'wpuf' ); ?></th>
            <td>
                <ul>
                    <?php
                        $layouts = array(
                            'layout1' => WPUF_PRO_ASSET_URI . '/images/forms/layout1.png',
                            'layout2' => WPUF_PRO_ASSET_URI . '/images/forms/layout2.png',
                            'layout3' => WPUF_PRO_ASSET_URI . '/images/forms/layout3.png'
                        );

                        foreach ( $layouts as $key => $image ) {
                            $class = '';

                            if ( $key == $form_layout ) {
                                $class = 'active';
                            }

                            $output  = '<li class="' . $class . '">';
                            $output .= '<input type="radio" name="wpuf_settings[form_layout]" value="' . $key . '" ' . checked( $form_layout, $key, false ). '>';
                            $output .= '<img src="' . $image . '" alt="">';
                            $output .= '</li>';

                            echo $output;
                        }
                    ?>
                </ul>
            </td>
        </tr>
    <?php endif; ?>

</table>
