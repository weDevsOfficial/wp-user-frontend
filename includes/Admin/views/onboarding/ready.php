<?php
/**
 * Onboarding: ready
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$checklist  = $this->get_checklist();

// Set only by the redirect from the previous step's Continue button.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$celebrate  = ! empty( $_GET['celebrate'] );
$progress   = $this->get_progress();
$revisiting = in_array( 'ready', $progress['completed'], true );
$share      = $revisiting ? wpuf_get_option( 'share_wpuf_essentials', 'wpuf_general', 'off' ) : 'on';

if ( $this->wants( 'post_form' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-post-forms' );
    $cta_label = __( 'Open my post forms', 'wp-user-frontend' );
} elseif ( $this->wants( 'registration' ) && wpuf_is_pro_active() ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-profile-forms' );
    $cta_label = __( 'Open my registration forms', 'wp-user-frontend' );
} elseif ( $this->wants( 'registration' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-settings#wpuf_profile' );
    $cta_label = __( 'Open login &amp; registration settings', 'wp-user-frontend' );
} elseif ( $this->wants( 'user_directory' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf_userlisting' );
    $cta_label = __( 'Open my user directories', 'wp-user-frontend' );
} else {
    $cta_url   = admin_url( 'admin.php?page=wp-user-frontend' );
    $cta_label = __( 'Go to User Frontend', 'wp-user-frontend' );
}
?>
<form method="post">
<h2><?php esc_html_e( 'Your frontend is ready', 'wp-user-frontend' ); ?></h2>
<p class="wpuf-onboarding-subtitle">
    <?php esc_html_e( 'Anything still grey is worth a look. Each row opens the screen that handles it.', 'wp-user-frontend' ); ?>
</p>

<ul class="wpuf-onboarding-checklist">
    <?php foreach ( $checklist as $item ) : ?>
        <li class="<?php echo $item['done'] ? 'is-done' : ''; ?>">
            <span class="wpuf-check">
                <svg width="9" height="7" viewBox="0 0 9 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="wpuf-check-label"><?php echo esc_html( $item['label'] ); ?></span>
            <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['link'] ); ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="wpuf-onboarding-note">
    <?php
    printf(
        // translators: %s is a link to the Tools page
        esc_html__( 'Run this setup again any time from %s.', 'wp-user-frontend' ),
        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf_tools&tab=tools' ) ) . '">' . esc_html__( 'User Frontend › Tools', 'wp-user-frontend' ) . '</a>'
    );
    ?>
</div>

<div class="wpuf-onboarding-field">
    <label class="wpuf-onboarding-toggle is-switch">
        <input type="checkbox" name="share_essentials" value="1" <?php checked( 'on', $share ); ?> />
        <span class="wpuf-toggle-text">
            <strong><?php esc_html_e( 'Share diagnostic data', 'wp-user-frontend' ); ?></strong>
            <span><?php esc_html_e( 'Versions, site name and your email — it tells us what to fix first. Never your members\' data.', 'wp-user-frontend' ); ?></span>
        </span>
    </label>
</div>

<?php wp_nonce_field( 'wpuf-onboarding' ); ?>
<input type="hidden" name="redirect_to" id="wpuf-onboarding-redirect" value="<?php echo esc_url( $cta_url ); ?>" />

<div class="wpuf-onboarding-footer">
    <div class="wpuf-onboarding-footer-inner">
        <div class="wpuf-onboarding-footer-left">
            <button type="submit" name="wpuf_onboarding_save" value="1" class="wpuf-onboarding-btn-white" data-redirect="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-settings' ) ); ?>">
                <?php esc_html_e( 'Go to full settings', 'wp-user-frontend' ); ?>
            </button>
        </div>
        <div class="wpuf-onboarding-footer-right">
            <button type="submit" name="wpuf_onboarding_save" value="1" class="wpuf-onboarding-btn-primary" data-redirect="<?php echo esc_url( $cta_url ); ?>">
                <?php echo esc_html( $cta_label ); ?>
            </button>
        </div>
    </div>
</div>
</form>

<script>
( function () {
    var redirect = document.getElementById( 'wpuf-onboarding-redirect' );

    document.querySelectorAll( '.wpuf-onboarding-footer button[data-redirect]' ).forEach( function ( button ) {
        button.addEventListener( 'click', function () {
            redirect.value = button.getAttribute( 'data-redirect' );
        } );
    } );
} )();
</script>

<?php if ( $celebrate ) : ?>
<canvas id="wpuf-onboarding-confetti" aria-hidden="true"></canvas>

<script>
( function () {
    var canvas = document.getElementById( 'wpuf-onboarding-confetti' );

    if ( ! canvas || ! canvas.getContext ) {
        return;
    }

    // Respect people who asked the OS for less movement.
    if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
        canvas.remove();
        return;
    }

    var ctx    = canvas.getContext( '2d' );
    var icon   = new Image();
    var pieces = [];
    var start  = null;
    var LIFE   = 4000;

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    var COLORS = [ '#059669', '#34d399', '#6d28d9', '#f59e0b', '#0ea5e9', '#ec4899' ];

    function build() {
        var count = Math.min( 120, Math.round( window.innerWidth / 10 ) );

        for ( var i = 0; i < count; i++ ) {
            // Every third piece is the plugin icon, the rest is paper.
            var isIcon = i % 3 === 0;

            pieces.push( {
                icon: isIcon,
                color: COLORS[ Math.floor( Math.random() * COLORS.length ) ],
                x: Math.random() * window.innerWidth,
                y: -40 - ( Math.random() * window.innerHeight * 0.6 ),
                size: isIcon ? 16 + Math.random() * 16 : 6 + Math.random() * 7,
                ratio: 0.4 + Math.random() * 0.6,
                vx: -1.2 + Math.random() * 2.4,
                vy: 2 + Math.random() * 3.5,
                rot: Math.random() * Math.PI * 2,
                vr: -0.12 + Math.random() * 0.24,
                sway: Math.random() * Math.PI * 2
            } );
        }
    }

    function frame( now ) {
        if ( ! start ) {
            start = now;
        }

        var elapsed = now - start;
        var fade    = elapsed > LIFE - 900 ? Math.max( 0, ( LIFE - elapsed ) / 900 ) : 1;

        ctx.clearRect( 0, 0, canvas.width, canvas.height );
        ctx.globalAlpha = fade;

        pieces.forEach( function ( p ) {
            p.sway += 0.03;
            p.x    += p.vx + Math.sin( p.sway ) * 0.7;
            p.y    += p.vy;
            p.rot  += p.vr;

            ctx.save();
            ctx.translate( p.x, p.y );
            ctx.rotate( p.rot );

            if ( p.icon ) {
                // Clip to a circle so the icon falls as a round chip.
                ctx.beginPath();
                ctx.arc( 0, 0, p.size / 2, 0, Math.PI * 2 );
                ctx.closePath();
                ctx.clip();

                ctx.fillStyle = '#ffffff';
                ctx.fill();
                ctx.drawImage( icon, -p.size / 2, -p.size / 2, p.size, p.size );
            } else {
                ctx.fillStyle = p.color;
                ctx.fillRect( -p.size / 2, -( p.size * p.ratio ) / 2, p.size, p.size * p.ratio );
            }

            ctx.restore();
        } );

        if ( elapsed < LIFE ) {
            window.requestAnimationFrame( frame );
        } else {
            canvas.remove();
        }
    }

    icon.onload = function () {
        resize();
        build();
        window.addEventListener( 'resize', resize );
        window.requestAnimationFrame( frame );
    };

    icon.src = '<?php echo esc_url( WPUF_ASSET_URI . '/images/icon-128x128.png' ); ?>';
} )();
</script>
<?php endif; ?>
