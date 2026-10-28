/**
 * A read-only value the admin copies into another service (redirect URIs,
 * webhook URLs): the value in a read-only box and a Copy button.
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextInput, notify } from '@wpuf/components';
import { Check, Copy } from 'lucide-react';
import { CONTROL_SIZE } from '../controlSize';

/**
 * @param {Object} props
 * @param {string} props.value Value.
 * @param {string} [props.id]  Input id.
 */
export default function CopyValue( { value, id } ) {
    const [ copied, setCopied ] = useState( false );

    const copy = () => {
        const done = () => {
            setCopied( true );
            setTimeout( () => setCopied( false ), 2000 );
        };

        if ( window.navigator.clipboard && window.isSecureContext ) {
            window.navigator.clipboard.writeText( value ).then( done, () => notify( __( 'Could not copy. Please copy it manually.', 'wp-user-frontend' ), 'error' ) );
            return;
        }

        // Plain http admin: select the text so Cmd/Ctrl+C copies it.
        const input = id ? document.getElementById( id ) : null;

        if ( input ) {
            input.select();
        }

        notify( __( 'Press Ctrl+C (Cmd+C) to copy the selected text.', 'wp-user-frontend' ), 'info' );
    };

    return (
        <div data-wpuf-ui="" className="flex items-center gap-2">
            <TextInput id={ id } className={ CONTROL_SIZE + ' bg-gray-50 text-gray-600' } value={ value } readOnly onChange={ () => {} } onFocus={ ( event ) => event.target.select() } />
            <Button size="sm" variant="secondary" onClick={ copy } aria-label={ __( 'Copy', 'wp-user-frontend' ) }>
                { copied ? <Check size={ 14 } aria-hidden="true" /> : <Copy size={ 14 } aria-hidden="true" /> }
                { copied ? __( 'Copied', 'wp-user-frontend' ) : __( 'Copy', 'wp-user-frontend' ) }
            </Button>
        </div>
    );
}
