<?php
/**
 * Account → Security tab
 *
 * Loops over every active 2FA method and renders each one through the
 * `wpuf_2fa_security_card_html` filter. The default consumer ships the
 * canonical TOTP markup; Pro and third parties hook the same filter to
 * provide their own card markup, keyed on `method_id`.
 *
 * Escaping contract: the filter receives **structured scalars** (no
 * HTML) and consumers return a complete HTML string. The template
 * passes that string through `wp_kses_post()` at the boundary. There
 * is no "default HTML in, modified HTML out" path — that's how XSS
 * lands. See docs/2fa-extending.md.
 *
 * Locals:
 * @var array                                                       $sections        All registered tabs.
 * @var string                                                      $current_section Active tab slug.
 * @var int                                                         $user_id         Current user ID.
 * @var \WeDevs\Wpuf\TwoFactor\Method_Interface[]                   $methods         Active methods, keyed by id.
 * @var \WeDevs\Wpuf\TwoFactor\User_Storage                         $storage         For enrollment timestamp lookups.
 * @var string                                                      $nonce           Nonce for AJAX endpoints.
 * @var string                                                      $ajax_url        admin-ajax.php URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Kses allowed-list for 2FA card markup.
 *
 * `wp_kses_post()` is post-content shaped — it strips <input>, <button>,
 * <form>, <label>, and most form-related attributes. Cards need
 * interactive form HTML, so we declare exactly what's allowed and let
 * `wp_kses()` enforce it. Filterable for Pro to extend (e.g. add
 * `<select>` for a method-picker dropdown) without forking the template.
 */
$allowed_html = apply_filters(
    'wpuf_2fa_security_card_allowed_html',
    [
        'div'    => [
            'class'          => true,
            'data-method-id' => true,
            'data-2fa-state' => true,
        ],
        'h4'     => [ 'class' => true ],
        'p'      => [ 'class' => true ],
        'span'   => [ 'class' => true ],
        'svg'    => [
            'class'   => true,
            'xmlns'   => true,
            'viewBox' => true,
            'width'   => true,
            'height'  => true,
            'fill'    => true,
        ],
        'path'   => [
            'd'                => true,
            'fill'             => true,
            'stroke'           => true,
            'stroke-width'     => true,
            'stroke-linecap'   => true,
            'stroke-linejoin'  => true,
        ],
        'g'      => [ 'fill' => true ],
        'rect'   => [
            'width'  => true,
            'height' => true,
            'x'      => true,
            'y'      => true,
            'fill'   => true,
        ],
        'label'  => [
            'class' => true,
            'for'   => true,
        ],
        'input'  => [
            'type'         => true,
            'class'        => true,
            'name'         => true,
            'id'           => true,
            'value'        => true,
            'maxlength'    => true,
            'inputmode'    => true,
            'autocomplete' => true,
            'pattern'      => true,
            'readonly'     => true,
            'placeholder'  => true,
            'required'     => true,
        ],
        'button' => [
            'type'           => true,
            'class'          => true,
            'data-method-id' => true,
            'data-confirm'   => true,
        ],
    ]
);
?>
<div class="wpuf-space-y-6" id="wpuf-2fa-security"
    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
    data-nonce="<?php echo esc_attr( $nonce ); ?>">

    <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6">
        <h3 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-2">
            <?php esc_html_e( 'Two-Factor Authentication', 'wp-user-frontend' ); ?>
        </h3>
        <p class="wpuf-text-gray-600 wpuf-mb-0">
            <?php esc_html_e( 'Add a second verification step to your account. After entering your password you will need to enter a code from one of the methods below.', 'wp-user-frontend' ); ?>
        </p>
        <p class="wpuf-text-gray-500 wpuf-text-sm wpuf-mt-3 wpuf-mb-0">
            <?php esc_html_e( 'Two-factor authentication protects logins through this site\'s login form. Administrators logging in via /wp-admin/ are not challenged.', 'wp-user-frontend' ); ?>
        </p>
    </div>

    <?php
    foreach ( $methods as $method ) :
        $method_id   = $method->get_id();
        $is_enrolled = $method->is_enrolled( $user_id );
        $enrolled_at = $is_enrolled ? $storage->get_method_enrolled_at( $user_id, $method_id ) : null;

        $card_data = [
            'method_id'         => $method_id,
            'label'             => $method->get_label(),
            'description'       => $method->get_description(),
            'is_enrolled'       => $is_enrolled,
            'destination_label' => $is_enrolled ? $method->get_destination_label( $user_id ) : '',
            'enrolled_at'       => $enrolled_at,
            'enrolled_at_human' => $enrolled_at ? wp_date( get_option( 'date_format' ), $enrolled_at ) : '',
        ];

        $html = apply_filters( 'wpuf_2fa_security_card_html', '', $card_data, $user_id );

        if ( ! is_string( $html ) || $html === '' ) {
            continue;
        }

        echo wp_kses( $html, $allowed_html );
    endforeach;
    ?>
</div>
