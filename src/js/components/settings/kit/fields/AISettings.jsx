import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../../stores-react/settings/constants';
import { stripTags } from '../../utils';
import RadioCardsField from './RadioCardsField';
import SelectDropdown from './SelectDropdown';
import TextField from './TextField';
import NumberField from './NumberField';
import HtmlField from './HtmlField';

/**
 * AI configuration panel with cross-field reactivity (the legacy screen did this
 * with jQuery). Changing the provider re-filters the model list and swaps the
 * API-key field to that provider's stored key.
 *
 * Per-provider model lists + keys arrive in `extra.ai`
 * ({ models_by_provider, keys }). Provider/model/temperature save as normal
 * `wpuf_ai` fields; each provider's key persists from `extra.ai.keys` via
 * `wpuf_ai_react_persist_keys` — so switching providers never drops another
 * provider's saved key.
 */

export default function AISettings( { fields, renderField } ) {
    const { values, ai } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            values: store.getSectionValues( 'wpuf_ai' ),
            ai: store.getExtraValue( 'ai' ) || {},
        };
    }, [] );

    const { setValue, setExtraValue } = useDispatch( STORE_NAME );

    const byName = ( n ) => fields.find( ( f ) => f.name === n );
    const handled = [ 'ai_provider', 'api_key_current', 'ai_model', 'temperature' ];

    const provider = values.ai_provider || ( byName( 'ai_provider' ) || {} ).default || 'openai';
    const modelsByProvider = ai.models_by_provider || {};
    const models = modelsByProvider[ provider ] || {};
    const keys = ai.keys || {};

    const providerField = byName( 'ai_provider' );
    const modelField = byName( 'ai_model' );
    const tempField = byName( 'temperature' );

    const setProvider = ( val ) => {
        setValue( 'wpuf_ai', 'ai_provider', val );
        // Re-point the model to the new provider's list when the current one
        // doesn't belong to it.
        const next = modelsByProvider[ val ] || {};
        const keysArr = Object.keys( next );
        if ( keysArr.length && ! next[ values.ai_model ] ) {
            setValue( 'wpuf_ai', 'ai_model', keysArr[ 0 ] );
        }
    };

    const setKey = ( val ) => setExtraValue( 'ai', { ...ai, keys: { ...keys, [ provider ]: val } } );

    const [ testing, setTesting ] = useState( false );
    const [ testResult, setTestResult ] = useState( null );
    const [ fetching, setFetching ] = useState( false );
    const [ fetchMsg, setFetchMsg ] = useState( null );

    const AI_BASE = '/wpuf/v1/ai-form-builder';

    const testConnection = async () => {
        setTesting( true );
        setTestResult( null );
        try {
            // The endpoint returns HTTP 400 on failure, so a resolved response is
            // a success; real failures land in catch.
            const res = await apiFetch( {
                path: `${ AI_BASE }/test`,
                method: 'POST',
                data: { provider, api_key: keys[ provider ] || '', model: values.ai_model || '' },
            } );
            setTestResult( { ok: true, message: ( res && res.message ) || __( 'Connection successful.', 'wp-user-frontend' ) } );
        } catch ( e ) {
            setTestResult( { ok: false, message: ( e && e.message ) || __( 'Connection failed.', 'wp-user-frontend' ) } );
        }
        setTesting( false );
    };

    const fetchModels = async () => {
        setFetching( true );
        setFetchMsg( null );
        try {
            if ( provider === 'google' ) {
                // Google's refresh reads the SAVED key from the DB, so a freshly
                // typed (unsaved) key won't be used — surface that on failure.
                const r = await apiFetch( { path: `${ AI_BASE }/refresh-google-models`, method: 'POST' } )
                    .catch( ( e ) => ( { success: false, message: e && e.message } ) );
                if ( r && r.success === false ) {
                    setFetchMsg( r.message || __( 'Save your Google API key first, then fetch the latest models.', 'wp-user-frontend' ) );
                }
            }
            const res = await apiFetch( { path: `${ AI_BASE }/models`, method: 'GET' } );
            const rebuilt = {};
            Object.keys( res.models || {} ).forEach( ( id ) => {
                const m = res.models[ id ];
                const p = m && m.provider;
                if ( p ) {
                    rebuilt[ p ] = rebuilt[ p ] || {};
                    rebuilt[ p ][ id ] = ( m && m.name ) || id;
                }
            } );
            setExtraValue( 'ai', { ...ai, models_by_provider: rebuilt } );
        } catch ( e ) {
            // Non-fatal — keep the existing list.
        }
        setFetching( false );
    };

    // Any extra AI fields (e.g. descriptive text) we don't special-case.
    const passthrough = fields.filter( ( f ) => f.name && handled.indexOf( f.name ) === -1 && f.type === 'html' );

    return (
        <div>
            { passthrough.map( ( f ) => <HtmlField key={ f.name } field={ { ...f, html: f.desc || f.default } } /> ) }

            { providerField && (
                <div className="wpuf-mt-6">
                    <RadioCardsField field={ providerField } name="ai_provider" value={ provider } single onChange={ ( n, v ) => setProvider( v ) } />
                </div>
            ) }

            <div className="wpuf-mt-6">
                <TextField
                    field={ { label: __( 'API Key', 'wp-user-frontend' ), help_text: stripTags( ( byName( 'api_key_current' ) || {} ).desc ) } }
                    name="api_key_current"
                    value={ keys[ provider ] || '' }
                    onChange={ ( n, v ) => setKey( v ) }
                />
            </div>

            { modelField && (
                <div className="wpuf-mt-6">
                    <div className="wpuf-flex wpuf-items-center wpuf-justify-between">
                        <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ __( 'AI Model', 'wp-user-frontend' ) }</label>
                        <button
                            type="button"
                            onClick={ fetchModels }
                            disabled={ fetching }
                            className="wpuf-text-xs wpuf-font-medium wpuf-text-primary hover:wpuf-underline disabled:wpuf-opacity-50"
                        >
                            { fetching ? __( 'Fetching…', 'wp-user-frontend' ) : __( '↻ Fetch latest models', 'wp-user-frontend' ) }
                        </button>
                    </div>
                    <SelectDropdown
                        field={ { options: models } }
                        name="ai_model"
                        value={ values.ai_model || '' }
                        onChange={ ( n, v ) => setValue( 'wpuf_ai', 'ai_model', v ) }
                    />
                    { fetchMsg && (
                        <p className="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-amber-600">{ fetchMsg }</p>
                    ) }
                </div>
            ) }

            <div className="wpuf-mt-6 wpuf-flex wpuf-items-center wpuf-gap-3">
                <button
                    type="button"
                    onClick={ testConnection }
                    disabled={ testing || ! ( keys[ provider ] || '' ).trim() }
                    className="wpuf-rounded-md wpuf-border !wpuf-border-gray-300 wpuf-bg-white wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50 disabled:wpuf-opacity-50"
                >
                    { testing ? __( 'Testing…', 'wp-user-frontend' ) : __( 'Test Connection', 'wp-user-frontend' ) }
                </button>
                { testResult && (
                    <span className={ `wpuf-text-sm wpuf-font-medium ${ testResult.ok ? 'wpuf-text-emerald-600' : 'wpuf-text-red-600' }` }>
                        { testResult.ok ? '✓ ' : '✕ ' }{ testResult.message }
                    </span>
                ) }
            </div>

            { tempField && (
                <div className="wpuf-mt-6">
                    <NumberField
                        field={ {
                            label: stripTags( tempField.label ) || __( 'Temperature', 'wp-user-frontend' ),
                            // Range 0–1 (step 0.1) — without these NumberField
                            // defaults to step=1, which makes the browser reject
                            // decimals so the value never updates / persists.
                            min: 0,
                            max: 1,
                            step: 0.1,
                            default: '0.7',
                            help_text: stripTags( tempField.desc ) || __( 'Controls randomness in responses. Lower values (0.1–0.3) are more focused; higher values (0.7–1.0) are more creative.', 'wp-user-frontend' ),
                        } }
                        name="temperature"
                        value={ values.temperature }
                        onChange={ ( n, v ) => setValue( 'wpuf_ai', 'temperature', v ) }
                    />
                </div>
            ) }
        </div>
    );
}
