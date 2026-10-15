/**
 * The one-time welcome, FlyHR's WelcomeScreen in User Frontend's green: a
 * full-screen hello the first time each admin opens the admin app
 * (Admin\Screens\Welcome hands the flag over once). The logo rises in and
 * floats on a soft glow, the headline arrives word by word, the lines
 * follow, the three panels rise in turn. Esc or "Let's go" closes it.
 *
 * It sits over whatever route opened, whose stylesheet is not this one, so
 * it is styled inline (its keyframes come with it).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { tokens } from '@wpuf/utilities';

/** How long the welcome takes to fade away. */
const EXIT_MS = 280;
const STAGGER = 55;
const GREEN = '#059669';

const KEYFRAMES = `
@keyframes wpuf-intro-in { from { opacity: 0; } }
@keyframes wpuf-intro-out { to { opacity: 0; transform: translateY( -8px ) scale( 1.01 ); } }
@keyframes wpuf-intro-reveal { from { opacity: 0; transform: translate3d( 0, var( --y, .75rem ), 0 ) scale( var( --s, 1 ) ); filter: blur( var( --b, 6px ) ); } to { opacity: 1; transform: none; filter: none; } }
@keyframes wpuf-intro-float { 0%, 100% { transform: translateY( 0 ); } 50% { transform: translateY( -8px ); } }
@keyframes wpuf-intro-halo { 0%, 100% { opacity: .55; transform: translate( -50%, -50% ) scale( .9 ); } 50% { opacity: 1; transform: translate( -50%, -50% ) scale( 1.1 ); } }
@keyframes wpuf-intro-drift-a { 0%, 100% { transform: translate3d( 0, 0, 0 ) scale( 1 ); opacity: .9; } 25% { transform: translate3d( 9rem, 5rem, 0 ) scale( 1.15 ); opacity: 1; } 50% { transform: translate3d( 16rem, -1rem, 0 ) scale( .95 ); opacity: .75; } 75% { transform: translate3d( 5rem, -5rem, 0 ) scale( 1.1 ); opacity: 1; } }
@keyframes wpuf-intro-drift-b { 0%, 100% { transform: translate3d( 0, 0, 0 ) scale( 1.05 ); opacity: .85; } 30% { transform: translate3d( -12rem, -6rem, 0 ) scale( .95 ); opacity: 1; } 60% { transform: translate3d( -4rem, -12rem, 0 ) scale( 1.2 ); opacity: .7; } 80% { transform: translate3d( 4rem, -4rem, 0 ) scale( 1 ); opacity: .95; } }
@keyframes wpuf-intro-drift-c { 0%, 100% { transform: translate3d( -50%, -50%, 0 ) scale( .9 ); opacity: .5; } 35% { transform: translate3d( calc( -50% + 10rem ), calc( -50% - 4rem ), 0 ) scale( 1.15 ); opacity: .85; } 70% { transform: translate3d( calc( -50% - 10rem ), calc( -50% + 5rem ), 0 ) scale( 1 ); opacity: .6; } }
.wpuf-intro-button:hover { background: #10b981 !important; }
.wpuf-intro-button:focus-visible { outline: 2px solid rgba( 5, 150, 105, .4 ); outline-offset: 2px; }
.wpuf-intro-link:hover, .wpuf-intro-link:focus { color: #047857 !important; }
@media ( prefers-reduced-motion: reduce ) { .wpuf-intro, .wpuf-intro * { animation: none !important; } }
`;

const reduced = () => !! window.matchMedia?.( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Inline style of an element arriving after `delay`.
 *
 * @param {number}  delay
 * @param {boolean} panel A card (rises further).
 *
 * @return {Object} Style.
 */
const arrive = ( delay, panel = false ) => ( {
    animation: `wpuf-intro-reveal 700ms cubic-bezier( .16, 1, .3, 1 ) ${ delay }ms both`,
    ...( panel ? { '--y': '2rem', '--s': 0.97, '--b': '4px' } : {} ),
} );

const ICONS = {
    post: <path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3zM13.5 6.5l3 3" />,
    users: <><circle cx="9" cy="8" r="3.5" /><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6" /></>,
    card: <><rect x="2.5" y="5" width="19" height="14" rx="2" /><path d="M2.5 10h19M6.5 15h4" /></>,
};

/**
 * @param {Object}   props
 * @param {Object}   props.data   { name, logo, onboarding }.
 * @param {Function} props.onDone Called once the welcome has faded out.
 */
export default function WelcomeIntro( { data, onDone } ) {
    const [ leaving, setLeaving ] = useState( false );
    const button = useRef( null );
    const title = __( 'Welcome to WP User Frontend', 'wp-user-frontend' );
    const titleAt = 260;
    const linesAt = titleAt + Math.max( 330, title.split( ' ' ).length * STAGGER + 110 );

    const features = [
        { icon: 'post', title: __( 'Posting from the frontend', 'wp-user-frontend' ), text: __( 'Forms for posts, guest posts and any post type, without wp-admin.', 'wp-user-frontend' ) },
        { icon: 'users', title: __( 'Sign-ups that look like your site', 'wp-user-frontend' ), text: __( 'Login, registration and account pages in your own theme.', 'wp-user-frontend' ) },
        { icon: 'card', title: __( 'Memberships and payments', 'wp-user-frontend' ), text: __( 'Sell subscription packs, charge per post and lock content.', 'wp-user-frontend' ) },
    ];

    const close = () => {
        if ( leaving ) {
            return;
        }

        setLeaving( true );
        window.setTimeout( onDone, reduced() ? 0 : EXIT_MS );
    };

    useEffect( () => {
        const timer = window.setTimeout( () => button.current?.focus(), linesAt );
        const previous = document.body.style.overflow;
        const onKey = ( event ) => {
            if ( 'Escape' === event.key ) {
                close();
            }
        };

        document.body.style.overflow = 'hidden';
        window.addEventListener( 'keydown', onKey );

        return () => {
            window.clearTimeout( timer );
            document.body.style.overflow = previous;
            window.removeEventListener( 'keydown', onKey );
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [] );

    const glow = ( extra ) => ( { position: 'absolute', pointerEvents: 'none', borderRadius: 9999, filter: 'blur(64px)', willChange: 'transform, opacity', ...extra } );
    const words = title.split( ' ' );
    const ctaAt = linesAt + 120 + features.length * 90 + 60;

    return (
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="wpuf-intro-title"
            className="wpuf-intro"
            style={ {
                position: 'fixed',
                inset: 0,
                zIndex: 100000,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                overflowY: 'auto',
                overflowX: 'hidden',
                padding: '40px 16px',
                background: '#ffffff',
                color: '#111827',
                // The admin app's font (Plus Jakarta Sans), like every other screen.
                fontFamily: tokens.fontSans,
                animation: leaving ? `wpuf-intro-out ${ EXIT_MS }ms cubic-bezier( .4, 0, 1, 1 ) both` : 'wpuf-intro-in 240ms cubic-bezier( .16, 1, .3, 1 ) both',
            } }
        >
            <style>{ KEYFRAMES }</style>
            <span aria-hidden="true" style={ glow( { left: -128, top: -128, width: 480, height: 480, background: 'rgba(16,185,129,.2)', animation: 'wpuf-intro-drift-a 16s ease-in-out infinite' } ) } />
            <span aria-hidden="true" style={ glow( { right: -96, bottom: -160, width: 544, height: 544, background: 'rgba(5,150,105,.15)', animation: 'wpuf-intro-drift-b 19s ease-in-out infinite' } ) } />
            <span aria-hidden="true" style={ glow( { left: '50%', top: '50%', width: 384, height: 384, background: 'rgba(52,211,153,.12)', animation: 'wpuf-intro-drift-c 23s ease-in-out infinite' } ) } />

            <div style={ { position: 'relative', width: '100%', maxWidth: 768, margin: '0 auto', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center' } }>
                { data.logo && (
                    <div style={ { position: 'relative', marginBottom: 32, ...arrive( 0, true ) } }>
                        <span aria-hidden="true" style={ { position: 'absolute', left: '50%', top: '50%', width: 112, height: 112, borderRadius: 9999, background: 'rgba(16,185,129,.2)', filter: 'blur(40px)', animation: 'wpuf-intro-halo 3s ease-in-out infinite' } } />
                        <img src={ data.logo } alt="" style={ { position: 'relative', height: 44, width: 'auto', animation: 'wpuf-intro-float 3s ease-in-out infinite' } } />
                    </div>
                ) }

                { data.name && (
                    <div style={ { marginBottom: 12, fontSize: 14, fontWeight: 500, color: GREEN, ...arrive( 140 ) } }>
                        { /* translators: %s: the user's first name. */ }
                        { sprintf( __( 'Hi %s', 'wp-user-frontend' ), data.name ) }
                    </div>
                ) }

                <h1 id="wpuf-intro-title" style={ { margin: 0, fontSize: 36, lineHeight: 1.2, fontWeight: 700, letterSpacing: '-0.02em', color: '#111827' } }>
                    { words.map( ( word, index ) => (
                        <span key={ `${ index }-${ word }` }>
                            <span style={ { display: 'inline-block', overflow: 'hidden', paddingBottom: 4, verticalAlign: 'bottom' } }>
                                <span style={ { display: 'inline-block', ...arrive( titleAt + index * STAGGER ) } }>{ word }</span>
                            </span>
                            { index < words.length - 1 ? ' ' : null }
                        </span>
                    ) ) }
                </h1>

                <p style={ { margin: '16px 0 0', maxWidth: 576, fontSize: 16, lineHeight: 1.6, color: '#6b7280', ...arrive( linesAt ) } }>
                    { __( 'Frontend posting, sign-ups, member dashboards and payments, all run from your own site.', 'wp-user-frontend' ) }
                </p>

                <div style={ { marginTop: 40, width: '100%', display: 'grid', gap: 16, gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))' } }>
                    { features.map( ( feature, index ) => (
                        <div key={ feature.icon } style={ { border: '1px solid #e5e7eb', borderRadius: 10, background: '#ffffff', padding: 20, textAlign: 'left', boxShadow: '0 1px 2px rgba(0,0,0,.05)', ...arrive( linesAt + 120 + index * 90, true ) } }>
                            <span style={ { marginBottom: 12, display: 'inline-flex', width: 36, height: 36, alignItems: 'center', justifyContent: 'center', borderRadius: 8, background: '#ecfdf5', color: GREEN } }>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{ ICONS[ feature.icon ] }</svg>
                            </span>
                            <p style={ { margin: 0, fontSize: 14, fontWeight: 600, color: '#111827' } }>{ feature.title }</p>
                            <p style={ { margin: '4px 0 0', fontSize: 14, lineHeight: 1.5, color: '#6b7280' } }>{ feature.text }</p>
                        </div>
                    ) ) }
                </div>

                <div style={ { marginTop: 40, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12, ...arrive( ctaAt ) } }>
                    <button
                        ref={ button }
                        type="button"
                        onClick={ close }
                        className="wpuf-intro-button"
                        style={ { display: 'inline-flex', alignItems: 'center', gap: 8, height: 44, padding: '0 24px', border: 0, borderRadius: 6, background: GREEN, color: '#ffffff', fontSize: 15, fontWeight: 600, cursor: 'pointer' } }
                    >
                        { __( 'Let’s go', 'wp-user-frontend' ) }
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </button>
                    { data.onboarding && (
                        <a href={ data.onboarding } className="wpuf-intro-link" style={ { fontSize: 14, fontWeight: 500, color: GREEN, textDecoration: 'none', boxShadow: 'none' } }>
                            { __( 'Or run the setup wizard', 'wp-user-frontend' ) }
                        </a>
                    ) }
                    <span style={ { fontSize: 12, color: '#6b7280' } }>{ __( 'Press Esc to skip', 'wp-user-frontend' ) }</span>
                </div>
            </div>
        </div>
    );
}
