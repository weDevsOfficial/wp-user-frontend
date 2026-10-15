/**
 * The small building blocks of the frontend apps: our own components over
 * plain scoped CSS (frontend-react-architecture.md 2.4), native elements,
 * `wpuf-` BEM classes, tokens on `.wpuf-frontend`.
 *
 * @since WPUF_SINCE
 */
import { forwardRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const cx = ( ...parts ) => parts.filter( Boolean ).join( ' ' );

export { cx };

/**
 * @param {Object} props
 * @param {string} [props.variant] primary | secondary | danger | ghost | link
 * @param {string} [props.size]    md | sm
 * @param {boolean} [props.busy]   Shows the spinner and disables the button.
 * @param {string}  [props.href]   Renders a link with the button look.
 */
export const Button = forwardRef( function Button( { variant = 'primary', size = 'md', busy = false, className = '', children, type = 'button', disabled, href, ...rest }, ref ) {
    const classes = cx( 'wpuf-btn', `wpuf-btn--${ variant }`, 'sm' === size && 'wpuf-btn--sm', busy && 'is-busy', className );

    // A link with the button look (Pay Now, Browse plans); a disabled or busy one is a button.
    if ( href && ! disabled && ! busy ) {
        return (
            <a ref={ ref } href={ href } className={ classes } { ...rest }>
                { children }
            </a>
        );
    }

    return (
        <button
            ref={ ref }
            type={ type }
            className={ classes }
            disabled={ disabled || busy }
            aria-busy={ busy || undefined }
            { ...rest }
        >
            { busy && <span className="wpuf-spinner" aria-hidden="true" /> }
            { children }
        </button>
    );
} );

/**
 * @param {Object} props
 * @param {string} [props.kind]      success | error | info | warning
 * @param {Function} [props.onClose] Dismiss button when given.
 */
export function Notice( { kind = 'info', children, onClose, className = '', ...rest } ) {
    return (
        <div className={ cx( 'wpuf-notice', `wpuf-notice--${ kind }`, className ) } role={ 'error' === kind ? 'alert' : 'status' } aria-live={ 'error' === kind ? 'assertive' : 'polite' } { ...rest }>
            <div className="wpuf-notice__body">{ children }</div>
            { onClose && (
                <button type="button" className="wpuf-notice__close" onClick={ onClose } aria-label={ __( 'Dismiss', 'wp-user-frontend' ) }>
                    &times;
                </button>
            ) }
        </div>
    );
}

/**
 * @param {Object} props
 * @param {string} [props.tone] gray | green | amber | red | blue | black
 */
export function Pill( { tone = 'gray', children, className = '' } ) {
    return <span className={ cx( 'wpuf-pill', `wpuf-pill--${ tone }`, className ) }>{ children }</span>;
}

/**
 * @param {Object} props
 * @param {string} [props.url]      Image URL, else initials.
 * @param {string} [props.initials] Fallback text.
 * @param {string} [props.alt]
 * @param {number} [props.size]     Pixels.
 */
export function Avatar( { url = '', initials = '', alt = '', size = 48, className = '' } ) {
    const style = { width: size, height: size, fontSize: Math.max( 12, Math.round( size / 2.5 ) ) };

    return url ? (
        <img className={ cx( 'wpuf-avatar', className ) } src={ url } alt={ alt } width={ size } height={ size } style={ style } />
    ) : (
        <span className={ cx( 'wpuf-avatar wpuf-avatar--initials', className ) } style={ style } aria-label={ alt || undefined } role={ alt ? 'img' : undefined }>
            { initials }
        </span>
    );
}

/** A grey block that pulses; width / height as CSS values. */
export function Skeleton( { width = '100%', height = 14, radius, className = '', style = {} } ) {
    return <span className={ cx( 'wpuf-skeleton', className ) } style={ { width, height, borderRadius: radius, ...style } } aria-hidden="true" />;
}

/** N lines of text. */
export function SkeletonLines( { lines = 3, className = '' } ) {
    return (
        <div className={ cx( 'wpuf-skeleton-lines', className ) } aria-hidden="true">
            { Array.from( { length: lines } ).map( ( _, index ) => (
                <Skeleton key={ index } width={ index === lines - 1 ? '60%' : '100%' } />
            ) ) }
        </div>
    );
}

/** The shape of a form while its schema loads. */
export function SkeletonFormRows( { rows = 4 } ) {
    return (
        <ul className="wpuf-form wpuf-skeleton-form" aria-hidden="true">
            { Array.from( { length: rows } ).map( ( _, index ) => (
                <li key={ index } className="wpuf-el wpuf-skeleton-row">
                    <div className="wpuf-label"><Skeleton width="40%" /></div>
                    <div className="wpuf-fields"><Skeleton height={ 38 } radius={ 8 } /></div>
                </li>
            ) ) }
            <li className="wpuf-submit"><Skeleton width={ 120 } height={ 40 } radius={ 8 } /></li>
        </ul>
    );
}

/** Table rows while a list loads. */
export function SkeletonRows( { rows = 5, cols = 4 } ) {
    return (
        <div className="wpuf-skeleton-rows" aria-hidden="true">
            { Array.from( { length: rows } ).map( ( _, row ) => (
                <div key={ row } className="wpuf-skeleton-rows__row">
                    { Array.from( { length: cols } ).map( ( __unused, col ) => (
                        <Skeleton key={ col } width={ 0 === col ? '40%' : '15%' } />
                    ) ) }
                </div>
            ) ) }
        </div>
    );
}

/** A loading spinner with an accessible label. */
export function Spinner( { label = __( 'Loading…', 'wp-user-frontend' ), className = '' } ) {
    return (
        <span className={ cx( 'wpuf-spinner-wrap', className ) } role="status">
            <span className="wpuf-spinner" aria-hidden="true" />
            <span className="wpuf-sr-only">{ label }</span>
        </span>
    );
}
