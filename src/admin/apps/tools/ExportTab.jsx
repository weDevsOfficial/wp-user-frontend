/**
 * Export tab: post forms and registration forms, all or picked ones, saved
 * as the same JSON file the classic page downloads.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, EmptyState, MultiSelect, Radio, Skeleton, notify } from '@wpuf/components';
import { Download, FileText, UserPlus } from 'lucide-react';

import { exportForms } from './api';
import { store } from './store';
import ToolCard from './ToolCard';

const SECTIONS = [
    { type: 'wpuf_forms', icon: FileText, title: __( 'Post Forms', 'wp-user-frontend' ), empty: __( 'Sorry you have no form to export', 'wp-user-frontend' ) },
    { type: 'wpuf_profile', icon: UserPlus, title: __( 'Registration Forms', 'wp-user-frontend' ), empty: __( 'Sorry you have no registration form to export', 'wp-user-frontend' ) },
];

/**
 * Save JSON as a file.
 *
 * @param {string} filename File name.
 * @param {*}      content  JSON value.
 */
const download = ( filename, content ) => {
    // Slashes escaped like wp_json_encode() (develop's export): the import refuses
    // a file holding `<script>…</script>`, which a Custom HTML field may carry.
    const json = JSON.stringify( content ).replace( /\//g, '\\/' );
    const url = window.URL.createObjectURL( new window.Blob( [ json ], { type: 'application/json' } ) );
    const link = document.createElement( 'a' );

    link.href = url;
    link.download = filename;
    document.body.appendChild( link );
    link.click();
    link.remove();
    window.URL.revokeObjectURL( url );
};

/**
 * @param {Object} props
 * @param {Object} props.section One of SECTIONS.
 */
function ExportSection( { section } ) {
    const forms = useSelect( ( select ) => select( store ).getForms( section.type ), [ section.type ] );
    const { loadForms } = useDispatch( store );
    const [ mode, setMode ] = useState( 'all' );
    const [ picked, setPicked ] = useState( [] );
    const [ busy, setBusy ] = useState( false );

    useEffect( () => {
        loadForms( section.type ).then( ( error ) => error && notify( error.message, 'error' ) );
    }, [ section.type, loadForms ] );

    const run = async () => {
        if ( 'selected' === mode && ! picked.length ) {
            notify( __( 'Please select some form for exporting', 'wp-user-frontend' ), 'error' );

            return;
        }

        setBusy( true );

        try {
            const result = await exportForms( section.type, 'selected' === mode ? picked.map( Number ) : [] );

            download( result.filename, result.forms );
        } catch ( error ) {
            notify( error.message, 'error' );
        } finally {
            setBusy( false );
        }
    };

    return (
        <ToolCard icon={ section.icon } title={ section.title }>
            { null === forms && <Skeleton lines={ 3 } /> }
            { forms && ! forms.length && <EmptyState size="card" icon={ section.icon } title={ section.empty } className="py-6" /> }
            { forms && forms.length > 0 && (
                <>
                    <Radio
                        name={ `wpuf-export-${ section.type }` }
                        value={ mode }
                        onChange={ setMode }
                        options={ [
                            { value: 'all', label: __( 'All', 'wp-user-frontend' ) },
                            { value: 'selected', label: __( 'Select individual', 'wp-user-frontend' ) },
                        ] }
                    />
                    { 'selected' === mode && (
                        <MultiSelect
                            className="mt-3"
                            value={ picked }
                            onChange={ setPicked }
                            placeholder={ __( 'Choose forms', 'wp-user-frontend' ) }
                            options={ forms.map( ( form ) => ( { value: String( form.id ), label: form.title } ) ) }
                        />
                    ) }
                    <Button className="mt-4" busy={ busy } onClick={ run }>
                        <Download size={ 16 } aria-hidden="true" />
                        { __( 'Export', 'wp-user-frontend' ) }
                    </Button>
                </>
            ) }
        </ToolCard>
    );
}

export default function ExportTab() {
    return (
        <div className="grid items-start gap-4 xl:grid-cols-2">
            { SECTIONS.map( ( section ) => <ExportSection key={ section.type } section={ section } /> ) }
        </div>
    );
}
