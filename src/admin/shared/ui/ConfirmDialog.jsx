/**
 * Confirmation dialog, the one WPUF admin confirm / alert, on plugin-ui's
 * AlertDialog in its own design: header (media, title, description),
 * optional body, footer with plugin-ui buttons (Cancel outline, the action
 * destructive for "danger", default otherwise). Focus trap and Escape come
 * from plugin-ui.
 *
 * - `variant` (alert | panel | card) is kept for callers; every variant uses
 *   the same plugin-ui look ("card" centres the header).
 * - `icon` picks the media: delete (trash, red), warning (amber), oops
 *   (amber), info (primary), pro (crown, primary), none; `media` replaces it.
 * - Content dialogs (upsells, field notices) pass `children`, `hideTitle`,
 *   `extraActions` and a `width` (max width, e.g. "40rem").
 *
 * The action has focus (`focusCancel` focuses Cancel); Escape, the overlay
 * and × call `onDismiss` (default `onCancel`).
 *
 * useConfirm() gives the promise flow inside a component:
 *   const [ confirm, dialog ] = useConfirm();
 *   if ( await confirm( { title, message } ) ) { ... }   // render { dialog }
 * Outside components use `dialogs.confirm()` / `dialogs.alert()` (./dialogs).
 */
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    cn,
} from '@wedevs/plugin-ui';
import { useCallback, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CircleAlert, Crown, Info, Trash2, TriangleAlert, X } from 'lucide-react';

// Media icon and its tint per `icon`.
const ICONS = {
    delete: { Icon: Trash2, tint: 'bg-red-50 text-red-600' },
    warning: { Icon: TriangleAlert, tint: 'bg-amber-50 text-amber-600' },
    oops: { Icon: CircleAlert, tint: 'bg-amber-50 text-amber-600' },
    info: { Icon: Info, tint: 'bg-emerald-50 text-emerald-600' },
    pro: { Icon: Crown, tint: 'bg-emerald-50 text-emerald-600' },
};

/**
 * @param {Object}   props
 * @param {boolean}  props.open            Open.
 * @param {*}        props.title           Title.
 * @param {*}        [props.message]       Message.
 * @param {string}   [props.variant]       alert|panel|card (card centres the header).
 * @param {string}   [props.icon]          delete|warning|oops|info|pro|none (default by tone).
 * @param {*}        [props.media]         Image or icon shown instead of `icon`.
 * @param {*}        [props.children]      Extra content below the message.
 * @param {*}        [props.extraActions]  Extra buttons / links before Cancel.
 * @param {boolean}  [props.hideTitle]     Title for screen readers only (content dialogs).
 * @param {boolean}  [props.showClose]     Corner close button.
 * @param {string}   [props.confirmText]   Action label (default "Yes").
 * @param {string}   [props.cancelText]    Cancel label (default "Cancel"); false hides it (alert).
 * @param {string}   [props.tone]          danger (destructive action) | primary | warning
 * @param {boolean}  [props.busy]          Action running.
 * @param {boolean}  [props.focusCancel]   Focus Cancel instead of the action (the safe choice).
 * @param {string}   [props.className]     Extra classes on the dialog.
 * @param {string}   [props.width]         Max width (CSS length) for content dialogs.
 * @param {Function} [props.onConfirm]     Action.
 * @param {Function} [props.onCancel]      Cancel button.
 * @param {Function} [props.onDismiss]     Escape / overlay / close button (default onCancel).
 */
export default function ConfirmDialog( {
    open,
    title,
    message,
    variant = 'alert',
    icon,
    media,
    children,
    extraActions,
    hideTitle = false,
    showClose = false,
    confirmText,
    cancelText,
    tone = 'danger',
    busy = false,
    focusCancel = false,
    className,
    width,
    onConfirm,
    onCancel,
    onDismiss,
} ) {
    const danger = 'danger' === tone;
    const dismiss = onDismiss || onCancel;
    const iconName = icon || ( danger ? 'delete' : 'warning' );
    const known = ICONS[ iconName ];
    let image = media;

    if ( ! image && known && ! hideTitle ) {
        const { Icon, tint } = known;

        image = (
            <span className={ cn( 'flex size-10 items-center justify-center rounded-full', tint ) }>
                <Icon className="size-5" aria-hidden="true" />
            </span>
        );
    }

    return (
        <AlertDialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : dismiss?.() ) }>
            <AlertDialogContent
                data-wpuf-dialog={ variant }
                style={ width ? { '--wpuf-dialog-w': width } : undefined }
                className={ cn(
                    'bg-white',
                    width && 'w-[var(--wpuf-dialog-w)] max-w-[calc(100%-2rem)] sm:max-w-[calc(100%-2rem)] data-[size=default]:max-w-[calc(100%-2rem)] data-[size=default]:sm:max-w-[calc(100%-2rem)]',
                    className
                ) }
            >
                { ( showClose || 'panel' === variant ) && (
                    <button
                        type="button"
                        onClick={ () => dismiss?.() }
                        aria-label={ __( 'Close', 'wp-user-frontend' ) }
                        className="absolute right-3 top-3 flex size-8 cursor-pointer items-center justify-center rounded-md border-0 bg-transparent p-0 text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <X className="size-4" aria-hidden="true" />
                    </button>
                ) }
                { /* Icon beside the text (card: above it, centred); plugin-ui's media grid leaves a wide gap. */ }
                <AlertDialogHeader
                    className={ cn(
                        'flex gap-4 place-items-start',
                        'card' === variant ? 'flex-col items-center text-center sm:items-center sm:text-center' : 'flex-row items-start text-left sm:text-left'
                    ) }
                >
                    { image && <div className="shrink-0">{ image }</div> }
                    <div className={ cn( 'grid min-w-0 flex-1 gap-1.5', 'card' === variant && 'justify-items-center' ) }>
                        <AlertDialogTitle className={ hideTitle ? 'sr-only' : undefined }>{ title }</AlertDialogTitle>
                        { /* Server markup in messages: no WordPress admin paragraph margins or sizes. */ }
                        { message && <AlertDialogDescription className="[&_p]:m-0 [&_p]:text-[length:inherit] [&_p]:leading-[inherit] [&_p+p]:mt-2">{ message }</AlertDialogDescription> }
                    </div>
                </AlertDialogHeader>
                { children }
                <AlertDialogFooter>
                    { extraActions }
                    { false !== cancelText && (
                        <AlertDialogCancel
                            variant="outline"
                            autoFocus={ focusCancel }
                            disabled={ busy }
                            onClick={ ( event ) => {
                                event.preventDefault();
                                onCancel?.();
                            } }
                        >
                            { cancelText || __( 'Cancel', 'wp-user-frontend' ) }
                        </AlertDialogCancel>
                    ) }
                    <AlertDialogAction
                        variant={ danger ? 'destructive' : 'default' }
                        autoFocus={ ! focusCancel }
                        disabled={ busy }
                        aria-busy={ busy || undefined }
                        onClick={ ( event ) => {
                            event.preventDefault();
                            onConfirm?.();
                        } }
                    >
                        { confirmText || __( 'Yes', 'wp-user-frontend' ) }
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

/**
 * Promise-based confirm, like `Swal.fire( ... ).then( r => r.isConfirmed )`.
 *
 * @return {[Function, *]} [ confirm( options ) => Promise<boolean>, dialog element to render ]
 */
export function useConfirm() {
    const [ options, setOptions ] = useState( null );
    const resolver = useRef( null );

    const confirm = useCallback( ( next ) => new Promise( ( resolve ) => {
        resolver.current = resolve;
        setOptions( next || {} );
    } ), [] );

    const settle = ( value ) => {
        resolver.current?.( value );
        resolver.current = null;
        setOptions( null );
    };

    const dialog = (
        <ConfirmDialog
            { ...( options || {} ) }
            open={ null !== options }
            onConfirm={ () => settle( true ) }
            onCancel={ () => settle( false ) }
        />
    );

    return [ confirm, dialog ];
}
