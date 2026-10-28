/**
 * Text and block entrances of the setup wizard and the welcome screens,
 * FlyHR's (features/onboarding/Reveal): a headline arrives word by word, each
 * word blurring in and rising out of its own clipped box, 55ms apart; the
 * lines under it follow softly; the step's panel settles last. Runs on mount
 * (`wpuf-reveal` in tools/admin-css/src/onboarding.css); reduced motion
 * shows everything at rest.
 *
 * @since WPUF_SINCE
 */
import { Fragment } from '@wordpress/element';

/** The stagger between words, as FlyHR uses. */
const WORD_STAGGER_MS = 55;

/**
 * One element that blurs in and rises into place after `delay`.
 *
 * @param {Object} props
 * @param {*}      props.children
 * @param {string} [props.arrival] soft (lines of text) or panel (a card).
 * @param {number} [props.delay]   Milliseconds before it starts.
 * @param {string} [props.as]      Tag.
 * @param {string} [props.className]
 */
export function Reveal( { children, arrival = 'soft', delay = 0, as: Tag = 'div', className = '', ...props } ) {
    return (
        <Tag className={ `wpuf-reveal wpuf-reveal-${ arrival } ${ className }` } style={ { animationDelay: `${ delay }ms` } } { ...props }>
            { children }
        </Tag>
    );
}

/**
 * A headline that arrives word by word. The spaces sit outside the clipped
 * boxes, so the sentence still copies and reads as one.
 *
 * @param {Object} props
 * @param {string} props.text
 * @param {string} [props.as]
 * @param {string} [props.className]
 * @param {number} [props.delay]
 */
export function RevealWords( { text, as: Tag = 'h1', className = '', delay = 0, ...props } ) {
    const words = String( text ).split( ' ' );

    return (
        <Tag className={ className } { ...props }>
            { words.map( ( word, index ) => (
                <Fragment key={ `${ index }-${ word }` }>
                    <span className="inline-block overflow-hidden pb-1 align-bottom">
                        <Reveal as="span" delay={ delay + index * WORD_STAGGER_MS } className="inline-block">
                            { word }
                        </Reveal>
                    </span>
                    { index < words.length - 1 ? ' ' : null }
                </Fragment>
            ) ) }
        </Tag>
    );
}

/**
 * When the lines under a headline should start: just after its last word.
 *
 * @param {string} text  Headline.
 * @param {number} delay Headline delay.
 *
 * @return {number} Milliseconds.
 */
export function afterWords( text, delay = 0 ) {
    return delay + Math.max( 330, String( text ).split( ' ' ).length * WORD_STAGGER_MS + 110 );
}
