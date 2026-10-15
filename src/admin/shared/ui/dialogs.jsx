/**
 * Dialogs opened from code outside a component (builder helpers, hooks, Pro):
 * the React replacement for `Swal.fire()`. One DialogHost per page renders
 * them (WpufProviders, next to the Toaster); dialogs opened before it mounts
 * wait for it.
 *
 *   if ( await dialogs.confirm( { title, message, confirmText } ) ) { ... }
 *   await dialogs.alert( { title, message } );
 *   const value = await dialogs.open( ( { close } ) => <ConfirmDialog open ... /> );
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import ConfirmDialog from './ConfirmDialog';

let items = [];
let listener = null;
let nextId = 1;

const emit = () => listener?.( items );

const add = ( item ) => new Promise( ( resolve ) => {
    items = [ ...items, { ...item, id: nextId++, resolve } ];
    emit();
} );

const settle = ( id, value ) => {
    const item = items.find( ( entry ) => entry.id === id );

    items = items.filter( ( entry ) => entry.id !== id );
    emit();
    item?.resolve( value );
};

const dialogs = {
    /**
     * @param {Object} options ConfirmDialog props (title, message, media, confirmText, cancelText, tone).
     *
     * @return {Promise<boolean>} Confirmed.
     */
    confirm: ( options ) => add( { type: 'confirm', options: options || {} } ),

    /**
     * An alert: one button (default "OK").
     *
     * @param {Object} options ConfirmDialog props.
     *
     * @return {Promise<boolean>} True when the button was used, false on Escape / overlay.
     */
    alert: ( options ) => add( { type: 'confirm', options: { tone: 'primary', confirmText: undefined, ...( options || {} ), cancelText: false, alert: true } } ),

    /**
     * The "Oops..." alert (the builder's refused drops and single-instance
     * fields, the AI builder's failed generation): warning icon, title,
     * corner close, OK. `options` overrides any ConfirmDialog prop.
     *
     * @since WPUF_SINCE
     *
     * @param {*}      message   Message.
     * @param {Object} [options] ConfirmDialog props (confirmText, children, className...).
     *
     * @return {Promise<boolean>} Settles on close.
     */
    oops: ( message, options ) => add( {
        type: 'confirm',
        options: {
            tone: 'primary',
            title: __( 'Oops...', 'wp-user-frontend' ),
            message,
            icon: 'oops',
            showClose: true,
            confirmText: __( 'OK', 'wp-user-frontend' ),
            ...( options || {} ),
            cancelText: false,
            alert: true,
        },
    } ),

    /**
     * A custom dialog: `render( { close } )` returns the dialog element, built on
     * the shared Modal or ConfirmDialog with `open`; `close( value )` resolves.
     *
     * @param {Function} render ( { close } ) => element.
     *
     * @return {Promise<*>} The value passed to close().
     */
    open: ( render ) => add( { type: 'custom', render } ),
};

export default dialogs;

/**
 * Renders the open dialogs, the first on top of the queue.
 */
export function DialogHost() {
    const [ list, setList ] = useState( items );

    useEffect( () => {
        listener = setList;
        setList( items );

        return () => {
            if ( listener === setList ) {
                listener = null;
            }
        };
    }, [] );

    const current = list[ 0 ];

    if ( ! current ) {
        return null;
    }

    if ( 'custom' === current.type ) {
        return current.render( { close: ( value ) => settle( current.id, value ) } );
    }

    const { alert, ...options } = current.options;

    return (
        <ConfirmDialog
            key={ current.id }
            { ...options }
            confirmText={ options.confirmText || ( alert ? __( 'OK', 'wp-user-frontend' ) : undefined ) }
            open
            onConfirm={ () => settle( current.id, true ) }
            onCancel={ () => settle( current.id, false ) }
        />
    );
}
