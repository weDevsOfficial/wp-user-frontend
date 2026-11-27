/**
 * Import tab: pick or drop a JSON export file; the forms in it are created
 * (`POST wpuf/v1/admin/tools/import`, the classic upload's checks). The file
 * is not added to the Media Library.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, notify } from '@wpuf/components';
import { FileJson, Upload, X } from 'lucide-react';

import { importFile } from './api';

export default function ImportTab() {
    const input = useRef( null );
    const [ file, setFile ] = useState( null );
    const [ over, setOver ] = useState( false );
    const [ busy, setBusy ] = useState( false );

    const pick = ( picked ) => {
        if ( ! picked ) {
            return;
        }

        if ( ! /\.json$/i.test( picked.name ) ) {
            notify( __( 'Provided file is not a JSON file.', 'wp-user-frontend' ), 'error' );

            return;
        }

        setFile( picked );
    };

    const clear = () => {
        setFile( null );

        if ( input.current ) {
            input.current.value = '';
        }
    };

    const submit = async () => {
        setBusy( true );

        try {
            const result = await importFile( file );

            notify( result.message );
            clear();
        } catch ( error ) {
            notify( error.message || __( 'Could not import forms.', 'wp-user-frontend' ), 'error' );
        } finally {
            setBusy( false );
        }
    };

    return (
        <div className="max-w-2xl">
            <h2 className="m-0 text-base font-semibold text-gray-900">{ __( 'Import forms', 'wp-user-frontend' ) }</h2>
            <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Upload a JSON file exported from User Frontend (Tools > Export) to add its forms to this site.', 'wp-user-frontend' ) }</p>

            <label
                htmlFor="wpuf-import-file"
                onDragOver={ ( event ) => {
                    event.preventDefault();
                    setOver( true );
                } }
                onDragLeave={ () => setOver( false ) }
                onDrop={ ( event ) => {
                    event.preventDefault();
                    setOver( false );
                    pick( event.dataTransfer.files?.[ 0 ] );
                } }
                className={ `mt-4 flex cursor-pointer flex-col items-center rounded-[10px] border-2 border-dashed px-6 py-10 text-center ${ over ? 'border-primary bg-emerald-50' : 'border-gray-300 bg-gray-50 hover:border-gray-400' }` }
            >
                <span className="inline-flex size-12 items-center justify-center rounded-full bg-white text-primary shadow-sm" aria-hidden="true">
                    <Upload size={ 22 } strokeWidth={ 1.75 } />
                </span>
                <span className="mt-3 text-sm font-medium text-gray-900">{ __( 'Drop a JSON file here or click to choose', 'wp-user-frontend' ) }</span>
                <span className="mt-1 text-xs text-gray-500">{ __( 'Only .json export files', 'wp-user-frontend' ) }</span>
                <input ref={ input } id="wpuf-import-file" type="file" accept=".json,application/json" className="sr-only" onChange={ ( event ) => pick( event.target.files?.[ 0 ] ) } />
            </label>

            { file && (
                <div className="mt-4 flex items-center gap-3 rounded-lg border border-solid border-gray-200 px-4 py-3">
                    <FileJson size={ 20 } className="shrink-0 text-gray-500" aria-hidden="true" />
                    <span className="min-w-0 flex-1 truncate text-sm text-gray-900">{ file.name }</span>
                    <Button variant="icon" onClick={ clear } aria-label={ __( 'Remove file', 'wp-user-frontend' ) } disabled={ busy }>
                        <X size={ 16 } aria-hidden="true" />
                    </Button>
                </div>
            ) }

            <Button className="mt-4" busy={ busy } disabled={ ! file } onClick={ submit }>
                { busy ? __( 'Importing JSON File', 'wp-user-frontend' ) : __( 'Import Forms', 'wp-user-frontend' ) }
            </Button>
        </div>
    );
}
