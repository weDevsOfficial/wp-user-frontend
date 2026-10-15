/**
 * Confirmation dialog, the one WPUF admin confirm / alert (replaces Swal,
 * window.confirm and the settings message modal), on plugin-ui's AlertDialog
 * (focus trap, Escape) with develop's Vue look, measured on develop:
 *
 * - variant "alert" (default): the builder's SweetAlert2 popup. 512px wide,
 *   5px corners, an 80px icon on top (trash for "danger", warning circle
 *   otherwise, or `media`), 30px semibold title, 18px gray message, centered
 *   buttons (Cancel white with a gray border, the action red or green), a 40%
 *   black backdrop. `showClose` adds the corner ×.
 * - variant "panel": the subscriptions Popup.vue. Left aligned title and
 *   message, corner close button, the action then Cancel on the right.
 * - variant "card": the forms list "AI Provider Not Configured" modal. 576px
 *   card, 110px icon (`media`), 24px title, 18px gray text, large buttons.
 *
 * The action has focus (`focusCancel` focuses Cancel); Escape, the overlay
 * and × call `onDismiss` (default `onCancel`). Content dialogs (upsells,
 * notices) use `children`, `hideTitle`, `extraActions` and a wider `className`.
 *
 * useConfirm() gives the Swal-style flow inside a component:
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
    AlertDialogTitle,
    cn,
} from '@wedevs/plugin-ui';
import { useCallback, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { useBoot } from '../hooks';

// Develop's popup icons (assets/images).
const ICONS = {
    delete: '/images/delete-icon-rounded.svg',
    warning: '/images/warning-circle.svg',
    oops: '/images/oops.svg',
};

/**
 * Corner close button: SweetAlert's × or Popup.vue's x-mark.
 *
 * @param {Object}   props
 * @param {string}   props.variant alert|panel
 * @param {Function} props.onClick Close.
 */
function CloseButton( { variant, onClick } ) {
    if ( 'panel' === variant ) {
        return (
            <button type="button" onClick={ onClick } aria-label={ __( 'Close', 'wp-user-frontend' ) } className="absolute right-4 top-4 cursor-pointer rounded-md border-0 bg-white p-0 text-gray-400 hover:text-gray-500">
                <svg className="size-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        );
    }

    return (
        <button type="button" onClick={ onClick } aria-label={ __( 'Close', 'wp-user-frontend' ) } className="absolute right-0 top-0 flex size-12 cursor-pointer items-center justify-center border-0 bg-transparent p-0 text-[40px] leading-none text-[#ccc] hover:text-[#f27474]">
            &times;
        </button>
    );
}

// Class sets per develop look. Sizes of the alert go through CSS variables
// (`width` / `padding` props): plugin-ui's own size classes are overridden
// with arbitrary values tailwind-merge recognises.
const LOOKS = {
    // FlyHR dialog look with our colours: 10px radius, 24px padding, 24px bold
    // title, 16px muted message, 40px actions.
    alert: {
        content: 'flex w-[var(--wpuf-dialog-w)] max-w-[calc(100%-2rem)] flex-col items-center gap-0 rounded-[10px] bg-white p-6 text-center shadow-lg ring-0 sm:max-w-[calc(100%-2rem)] data-[size=default]:sm:max-w-[calc(100%-2rem)]',
        media: 'mb-4 flex justify-center',
        title: 'm-0 text-2xl font-bold leading-tight tracking-tight text-gray-900',
        message: 'm-0 mt-2 text-base text-gray-500',
        footer: 'mt-6 flex flex-wrap items-center justify-center gap-3',
        cancel: 'h-10 rounded-md border border-solid border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50',
        confirm: 'h-10 rounded-md border-0 px-5 text-sm font-medium text-white shadow-sm',
        danger: 'bg-red-600 hover:bg-red-700',
        primary: 'bg-primary hover:bg-primaryHover',
    },
    panel: {
        content: 'block w-full max-w-lg gap-0 rounded-[10px] bg-white p-6 text-left shadow-lg ring-0 sm:max-w-lg',
        title: 'm-0 ml-4 text-lg font-semibold leading-7 text-gray-900',
        message: 'm-0 ml-4 mt-1 text-sm text-gray-500',
        footer: 'mt-6 flex flex-row-reverse gap-3',
        cancel: 'h-10 rounded-md border border-solid border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50',
        confirm: 'h-10 rounded-md border-0 px-5 text-sm font-medium text-white shadow-sm',
        danger: 'bg-red-600 hover:bg-red-700',
        primary: 'bg-primary hover:bg-primaryHover',
    },
    card: {
        content: 'flex w-[576px] max-w-[calc(100%-2.5rem)] flex-col items-center gap-0 rounded-[10px] bg-white p-6 text-center shadow-lg ring-0 sm:max-w-[calc(100%-2.5rem)] data-[size=default]:sm:max-w-[calc(100%-2.5rem)]',
        media: 'mb-6 flex justify-center',
        title: 'm-0 mb-2 text-2xl font-bold leading-tight tracking-tight text-gray-900',
        message: 'm-0 mb-6 text-base text-gray-500',
        footer: 'flex justify-center gap-3',
        cancel: 'h-10 rounded-md border border-solid border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50',
        confirm: 'h-10 rounded-md border-0 px-5 text-sm font-medium text-white shadow-sm',
        danger: 'bg-red-600 hover:bg-red-700',
        primary: 'bg-primary hover:bg-primaryHover',
    },
};

/**
 * @param {Object}   props
 * @param {boolean}  props.open            Open.
 * @param {*}        props.title           Title.
 * @param {*}        [props.message]       Message.
 * @param {string}   [props.variant]       alert|panel|card
 * @param {string}   [props.icon]          alert icon: delete|warning|oops|none (default by tone).
 * @param {*}        [props.media]         Image or icon shown instead of `icon`.
 * @param {*}        [props.children]      Extra content below the message.
 * @param {*}        [props.extraActions]  Extra buttons / links before Cancel.
 * @param {boolean}  [props.hideTitle]     Title for screen readers only (content dialogs).
 * @param {boolean}  [props.showClose]     Corner close button.
 * @param {string}   [props.confirmText]   Action label (default "Yes").
 * @param {string}   [props.cancelText]    Cancel label (default "Cancel"); false hides it (alert).
 * @param {string}   [props.tone]          danger (red action) | primary | warning (green action)
 * @param {boolean}  [props.busy]          Action running.
 * @param {boolean}  [props.focusCancel]   Focus Cancel instead of the action (the safe choice).
 * @param {string}   [props.className]     Extra classes on the dialog.
 * @param {string}   [props.width]         alert width (CSS length, default 512px).
 * @param {string}   [props.padding]       alert padding (CSS value, default "0 0 20px").
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
    padding,
    onConfirm,
    onCancel,
    onDismiss,
} ) {
    const assetUrl = useBoot().assetUrl || ( window.wpuf_admin_script || {} ).asset_url || '';
    const look = LOOKS[ variant ] || LOOKS.alert;
    const danger = 'danger' === tone;
    const panel = 'panel' === variant;
    const dismiss = onDismiss || onCancel;
    const iconName = icon || ( danger ? 'delete' : 'warning' );
    let image = media;

    if ( ! image && 'alert' === variant && ICONS[ iconName ] && assetUrl ) {
        image = <img src={ assetUrl + ICONS[ iconName ] } alt="" className={ 'oops' === iconName ? 'h-20 w-auto' : 'size-14' } />;
    }

    const confirmButton = (
        <AlertDialogAction
            autoFocus={ ! focusCancel }
            disabled={ busy }
            aria-busy={ busy || undefined }
            onClick={ ( event ) => {
                event.preventDefault();
                onConfirm?.();
            } }
            className={ cn( look.confirm, danger ? look.danger : look.primary ) }
        >
            { confirmText || __( 'Yes', 'wp-user-frontend' ) }
        </AlertDialogAction>
    );

    const cancelButton = false !== cancelText && (
        <AlertDialogCancel
            autoFocus={ focusCancel }
            disabled={ busy }
            onClick={ ( event ) => {
                event.preventDefault();
                onCancel?.();
            } }
            className={ look.cancel }
        >
            { cancelText || __( 'Cancel', 'wp-user-frontend' ) }
        </AlertDialogCancel>
    );

    return (
        <AlertDialog open={ open } onOpenChange={ ( next ) => ( next || busy ? undefined : dismiss?.() ) }>
            <AlertDialogContent
                data-wpuf-dialog={ variant }
                style={ 'alert' === variant ? { '--wpuf-dialog-w': width || '512px', '--wpuf-dialog-p': padding || '0 0 20px' } : undefined }
                className={ cn( look.content, className ) }
            >
                { ( showClose || panel ) && <CloseButton variant={ variant } onClick={ () => dismiss?.() } /> }
                { image && look.media && <div className={ look.media }>{ image }</div> }
                <AlertDialogTitle className={ hideTitle ? 'sr-only' : look.title }>{ title }</AlertDialogTitle>
                { message && <AlertDialogDescription className={ look.message }>{ message }</AlertDialogDescription> }
                { children }
                { /* Popup.vue lists the action first in a reversed row; the others read left to right. */ }
                <div className={ look.footer }>
                    { panel ? <>{ confirmButton }{ cancelButton }{ extraActions }</> : <>{ extraActions }{ cancelButton }{ confirmButton }</> }
                </div>
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
