<?php
$label_position = isset( $form_settings['label_position'] ) ? $form_settings['label_position'] : 'left';
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
</table>
