<?php

namespace WeDevs\Wpuf\TwoFactor;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders an otpauth:// URI as an inline SVG QR code
 *
 * Server-side so the secret never leaves the site to a third-party QR
 * service. Returns an SVG string ready to embed inline.
 *
 * @since WPUF_SINCE
 */
class QR_Renderer {

    public const SIZE_PX = 220;

    /**
     * @param string $otpauth_uri
     *
     * @return string SVG markup, or empty string on failure.
     */
    public function render_svg( $otpauth_uri ) {
        if ( ! class_exists( Writer::class ) ) {
            return '';
        }

        try {
            $renderer = new ImageRenderer(
                new RendererStyle( self::SIZE_PX ),
                new SvgImageBackEnd()
            );

            $writer = new Writer( $renderer );

            return $writer->writeString( $otpauth_uri );
        } catch ( \Throwable $e ) {
            return '';
        }
    }
}
