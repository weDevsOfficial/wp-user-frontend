/**
 * A short burst of confetti for a finished setup run: paper in the plugin's
 * colours with every third piece the User Frontend icon as a round chip,
 * falling across the screen for four seconds. Skipped under reduced motion.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';

const LIFE = 4000;
const COLORS = [ '#059669', '#34d399', '#6d28d9', '#f59e0b', '#0ea5e9', '#ec4899' ];

/**
 * @param {Object} props
 * @param {string} [props.icon] Image of the round chips.
 */
export default function Confetti( { icon } ) {
    const canvas = useRef( null );
    const [ done, setDone ] = useState( () => !! window.matchMedia?.( '(prefers-reduced-motion: reduce)' ).matches );

    useEffect( () => {
        const element = canvas.current;
        const ctx = element?.getContext?.( '2d' );

        if ( done || ! ctx ) {
            return undefined;
        }

        const image = new window.Image();
        const pieces = [];
        let start = null;
        let frameId = 0;
        let ready = false;

        const resize = () => {
            element.width = window.innerWidth;
            element.height = window.innerHeight;
        };

        const build = () => {
            const count = Math.min( 120, Math.round( window.innerWidth / 10 ) );

            for ( let i = 0; i < count; i++ ) {
                const chip = 0 === i % 3;

                pieces.push( {
                    chip,
                    color: COLORS[ Math.floor( Math.random() * COLORS.length ) ],
                    x: Math.random() * window.innerWidth,
                    y: -40 - Math.random() * window.innerHeight * 0.6,
                    size: chip ? 16 + Math.random() * 16 : 6 + Math.random() * 7,
                    ratio: 0.4 + Math.random() * 0.6,
                    vx: -1.2 + Math.random() * 2.4,
                    vy: 2 + Math.random() * 3.5,
                    rot: Math.random() * Math.PI * 2,
                    vr: -0.12 + Math.random() * 0.24,
                    sway: Math.random() * Math.PI * 2,
                } );
            }
        };

        const frame = ( now ) => {
            if ( ! start ) {
                start = now;
            }

            const elapsed = now - start;

            ctx.clearRect( 0, 0, element.width, element.height );
            ctx.globalAlpha = elapsed > LIFE - 900 ? Math.max( 0, ( LIFE - elapsed ) / 900 ) : 1;

            pieces.forEach( ( p ) => {
                p.sway += 0.03;
                p.x += p.vx + Math.sin( p.sway ) * 0.7;
                p.y += p.vy;
                p.rot += p.vr;

                ctx.save();
                ctx.translate( p.x, p.y );
                ctx.rotate( p.rot );

                if ( p.chip && ready ) {
                    ctx.beginPath();
                    ctx.arc( 0, 0, p.size / 2, 0, Math.PI * 2 );
                    ctx.closePath();
                    ctx.clip();
                    ctx.fillStyle = '#ffffff';
                    ctx.fill();
                    ctx.drawImage( image, -p.size / 2, -p.size / 2, p.size, p.size );
                } else {
                    ctx.fillStyle = p.color;
                    ctx.fillRect( -p.size / 2, -( p.size * p.ratio ) / 2, p.size, p.size * p.ratio );
                }

                ctx.restore();
            } );

            if ( elapsed < LIFE ) {
                frameId = window.requestAnimationFrame( frame );
            } else {
                setDone( true );
            }
        };

        const run = () => {
            resize();
            build();
            window.addEventListener( 'resize', resize );
            frameId = window.requestAnimationFrame( frame );
        };

        // Without the icon there is still confetti, paper only.
        image.onload = () => {
            ready = true;
            run();
        };
        image.onerror = run;
        image.src = icon || '';

        return () => {
            window.cancelAnimationFrame( frameId );
            window.removeEventListener( 'resize', resize );
        };
    }, [ done, icon ] );

    if ( done ) {
        return null;
    }

    return <canvas ref={ canvas } aria-hidden="true" className="wpuf-onboarding-confetti pointer-events-none fixed inset-0 z-[100000]" />;
}
