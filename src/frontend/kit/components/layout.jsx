/**
 * The FlyHR profile layout pieces (frontend-react-architecture.md 2.1), as
 * our own components: Card with a title and divider, DetailList of label
 * and value rows, StatCard with a tinted icon tile, RailItem (active =
 * primary pill), Pagination.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, cx } from './primitives';
import ServerHtml from './ServerHtml';
import { ClipboardList, Lock } from '../icons';

export function Card( { title, actions, children, className = '', as: Tag = 'section', ...rest } ) {
    return (
        <Tag className={ cx( 'wpuf-card', className ) } { ...rest }>
            { ( title || actions ) && (
                <header className="wpuf-card__head">
                    { title && <h2 className="wpuf-card__title">{ title }</h2> }
                    { actions && <div className="wpuf-card__actions">{ actions }</div> }
                </header>
            ) }
            <div className="wpuf-card__body">{ children }</div>
        </Tag>
    );
}

/** A `dl` grid of rows; rows without a value are not rendered (the FlyHR rule). */
export function DetailList( { items = [], columns = 3, className = '' } ) {
    const rows = items.filter( ( item ) => item && '' !== item.value && null !== item.value && undefined !== item.value && ( ! Array.isArray( item.value ) || item.value.length ) );

    if ( ! rows.length ) {
        return <p className="wpuf-muted">{ __( 'Nothing to show yet.', 'wp-user-frontend' ) }</p>;
    }

    return (
        <dl className={ cx( 'wpuf-details', `wpuf-details--${ columns }`, className ) }>
            { rows.map( ( item ) => (
                <div key={ item.label } className="wpuf-details__item">
                    { item.icon && <span className="wpuf-details__icon" aria-hidden="true">{ item.icon }</span> }
                    <div className="wpuf-details__text">
                        <dt>{ item.label }</dt>
                        <dd>{ Array.isArray( item.value ) ? item.value.join( ', ' ) : item.value }</dd>
                    </div>
                </div>
            ) ) }
        </dl>
    );
}

/**
 * @param {Object} props
 * @param {*}      props.icon  A rendered icon element.
 * @param {string} props.label
 * @param {*}      props.value
 * @param {string} [props.sub]
 * @param {string} [props.tone] gray | green | violet | amber | blue
 * @param {string} [props.href] Makes the card a link.
 */
export function StatCard( { icon, label, value, sub, tone = 'gray', href, onClick } ) {
    const Tag = href ? 'a' : ( onClick ? 'button' : 'div' );
    const props = href ? { href } : ( onClick ? { type: 'button', onClick } : {} );

    return (
        <Tag className={ cx( 'wpuf-stat', `wpuf-stat--${ tone }` ) } { ...props }>
            <span className="wpuf-stat__icon" aria-hidden="true">{ icon }</span>
            <span className="wpuf-stat__text">
                <span className="wpuf-stat__value">{ value }</span>
                <span className="wpuf-stat__label">{ sub ? `${ label } · ${ sub }` : label }</span>
            </span>
        </Tag>
    );
}

/** One row of the left rail: a real link (the URL is the contract), intercepted by the app. */
export function RailItem( { icon, label, href, active = false, onSelect, badge } ) {
    return (
        <li>
            <a
                href={ href }
                className={ cx( 'wpuf-account-nav-item', active && 'active' ) }
                aria-current={ active ? 'page' : undefined }
                onClick={ ( event ) => {
                    if ( onSelect && ! event.metaKey && ! event.ctrlKey && ! event.shiftKey && 0 === event.button ) {
                        event.preventDefault();
                        onSelect();
                    }
                } }
            >
                { icon && <span className="wpuf-account-nav-item__icon" aria-hidden="true">{ icon }</span> }
                <span className="wpuf-account-nav-item__label">{ label }</span>
                { badge ? <span className="wpuf-account-nav-item__badge">{ badge }</span> : null }
            </a>
        </li>
    );
}

export function Pagination( { page, pages, onChange, hrefFor } ) {
    if ( pages <= 1 ) {
        return null;
    }

    const numbers = [];

    for ( let n = Math.max( 1, page - 2 ); n <= Math.min( pages, page + 2 ); n++ ) {
        numbers.push( n );
    }

    const link = ( n, label, className = '' ) => (
        <a
            key={ typeof label === 'string' ? label : n }
            href={ hrefFor ? hrefFor( n ) : '#' }
            className={ cx( 'wpuf-pagination__link', n === page && 'is-current', className ) }
            aria-current={ n === page ? 'page' : undefined }
            onClick={ ( event ) => {
                event.preventDefault();
                onChange( n );
            } }
        >
            { label }
        </a>
    );

    return (
        /* translators: %1$d: current page, %2$d: total pages */
        <nav className="wpuf-pagination" aria-label={ sprintf( __( 'Page %1$d of %2$d', 'wp-user-frontend' ), page, pages ) }>
            { page > 1 && link( page - 1, __( 'Previous', 'wp-user-frontend' ), 'wpuf-pagination__prev' ) }
            { numbers.map( ( n ) => link( n, String( n ) ) ) }
            { page < pages && link( page + 1, __( 'Next', 'wp-user-frontend' ), 'wpuf-pagination__next' ) }
        </nav>
    );
}

/**
 * A form that cannot be filled in right now (no fields yet, closed, login
 * needed, limit reached): a centred card with a tinted icon, a title, the
 * server's message and an optional button, instead of a bare notice.
 *
 * @param {Object} props
 * @param {Object} props.state Schema state: reason, title, message (HTML from the server), action { label, url }
 */
export function StateCard( { state = {} } ) {
    const Icon = 'empty' === state.reason ? ClipboardList : Lock;

    return (
        <div className={ cx( 'wpuf-state-card', state.reason && `wpuf-state-card--${ state.reason }` ) } role="status">
            <span className="wpuf-state-card__icon" aria-hidden="true"><Icon size={ 22 } /></span>
            { state.title && <h3 className="wpuf-state-card__title">{ state.title }</h3> }
            { state.message && <ServerHtml html={ state.message } className="wpuf-state-card__text" /> }
            { state.action?.url && (
                <Button href={ state.action.url }>{ state.action.label }</Button>
            ) }
        </div>
    );
}
