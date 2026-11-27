/**
 * The card every WPUF admin toast renders through, ported from FlyHR's
 * `shared/toast/ToastCard.tsx` (WP Project Manager's design): a bordered
 * 10px card whose whole body drains left to right as the timer runs, a type
 * icon, a 14px title with an optional line under it, and action / cancel
 * buttons. Inline styles: the Toaster renders in a body portal, where the
 * screens' scoped utility classes do not reach. The title keeps sonner's
 * `data-title` (and the line `data-description`) so tests find it as before.
 *
 * @since WPUF_SINCE
 */
import { AlertTriangle, CircleCheck, CircleX, Info, Loader2, X } from 'lucide-react';
import { __ } from '@wordpress/i18n';

/** Icon + accent per toast type (literals: a toast reads the same over anything). */
const TYPES = {
    success: { Icon: CircleCheck, color: '#16a34a' },
    error: { Icon: CircleX, color: '#dc2626' },
    warning: { Icon: AlertTriangle, color: '#d97706' },
    info: { Icon: Info, color: '#2563eb' },
    message: { Icon: Info, color: '#6b7280' },
    loading: { Icon: Loader2, color: '#6b7280' },
};

const S = {
    card: { position: 'relative', width: 356, maxWidth: 'calc(100vw - 2rem)', overflow: 'hidden', borderRadius: 10, border: '1px solid #e5e7eb', background: '#fff', boxShadow: '0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1)', fontFamily: '"Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif', boxSizing: 'border-box' },
    fill: { position: 'absolute', inset: 0, transformOrigin: 'left', opacity: 0.16 },
    row: { position: 'relative', zIndex: 1, display: 'flex', alignItems: 'flex-start', gap: 10, padding: '12px 16px' },
    icon: { position: 'relative', marginTop: 1, flexShrink: 0, display: 'inline-flex' },
    body: { minWidth: 0, flex: 1 },
    title: { margin: 0, fontSize: 14, fontWeight: 500, lineHeight: 1.375, color: '#111827', overflowWrap: 'anywhere' },
    desc: { margin: '2px 0 0', fontSize: 13, lineHeight: 1.375, color: '#6b7280', overflowWrap: 'anywhere' },
    actions: { marginTop: 8, display: 'flex', alignItems: 'center', gap: 8 },
    action: { height: 28, padding: '0 10px', border: 0, borderRadius: 6, background: 'var(--color-primary, #059669)', color: '#fff', fontSize: 13, fontWeight: 500, cursor: 'pointer' },
    cancel: { height: 28, padding: '0 10px', border: 0, borderRadius: 6, background: 'transparent', color: '#6b7280', fontSize: 13, fontWeight: 500, cursor: 'pointer' },
    close: { margin: '-2px -4px 0 0', flexShrink: 0, display: 'inline-flex', padding: 4, border: 0, borderRadius: 4, background: 'transparent', color: '#9ca3af', cursor: 'pointer' },
    track: { position: 'relative', zIndex: 1, height: 4, width: '100%', background: '#f3f4f6' },
};

/**
 * Toast card.
 *
 * @since WPUF_SINCE
 *
 * @param {Object}   props
 * @param {string}   [props.type]        success|error|warning|info|message|loading.
 * @param {*}        [props.title]       Title line.
 * @param {*}        [props.description] Line under the title.
 * @param {*}        [props.icon]        Icon in place of the type icon.
 * @param {Object}   [props.action]      { label, onClick }.
 * @param {Object}   [props.cancel]      { label, onClick }.
 * @param {number}   [props.duration]    Timer (ms), drawn as the draining body.
 * @param {number}   [props.progress]    0-100 for a determinate bar instead.
 * @param {boolean}  [props.closeButton] Show the close button.
 * @param {Function} [props.onDismiss]   Close the toast.
 *
 * @return {JSX.Element} Card.
 */
export default function ToastCard( { type = 'info', title, description, icon, action, cancel, duration = 4000, progress = null, closeButton = true, onDismiss } ) {
    const conf = TYPES[ type ] || TYPES.info;
    const Icon = conf.Icon;
    const isLoading = 'loading' === type;
    const hasProgress = null !== progress && Number.isFinite( progress );
    const showFill = ! isLoading && Number.isFinite( duration ) && ! hasProgress;

    const run = ( fn ) => () => {
        fn();
        if ( onDismiss ) {
            onDismiss();
        }
    };

    return (
        <div style={ S.card } data-wpuf-toast={ type }>
            { showFill ? (
                <div style={ { ...S.fill, background: conf.color, animation: `wpuf-toast-progress ${ duration }ms linear forwards` } } aria-hidden="true" />
            ) : null }

            <div style={ S.row }>
                <span style={ S.icon }>
                    { undefined !== icon && null !== icon ? icon : (
                        <Icon size={ 20 } style={ { color: conf.color, animation: isLoading ? 'wpuf-toast-spin 1s linear infinite' : undefined } } aria-hidden="true" />
                    ) }
                </span>

                <div style={ S.body }>
                    { null !== title && undefined !== title ? <div style={ S.title } data-title="">{ title }</div> : null }
                    { null !== description && undefined !== description ? <div style={ S.desc } data-description="">{ description }</div> : null }

                    { action || cancel ? (
                        <div style={ S.actions }>
                            { action ? <button type="button" style={ S.action } onClick={ run( action.onClick ) }>{ action.label }</button> : null }
                            { cancel ? <button type="button" style={ S.cancel } onClick={ run( cancel.onClick ) }>{ cancel.label }</button> : null }
                        </div>
                    ) : null }
                </div>

                { closeButton && onDismiss ? (
                    <button type="button" style={ S.close } onClick={ onDismiss } aria-label={ __( 'Close', 'wp-user-frontend' ) }>
                        <X size={ 14 } aria-hidden="true" />
                    </button>
                ) : null }
            </div>

            { hasProgress ? (
                <div style={ S.track }>
                    <div style={ { height: '100%', width: `${ Math.max( 0, Math.min( 100, progress ) ) }%`, background: conf.color, transition: 'width .2s ease-out' } } />
                </div>
            ) : null }
        </div>
    );
}
