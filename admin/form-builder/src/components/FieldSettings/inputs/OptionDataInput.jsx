import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../../store';
import SettingHelpText from './SettingHelpText';

/**
 * Generate a random ID for option rows.
 */
function getRandomId() {
    return Math.floor( Math.random() * ( 9999999 - 999 + 1 ) ) + 999;
}

/**
 * Option data input for select, radio, checkbox, multiselect fields.
 * Replaces Vue field-option-data component.
 *
 * Manages the list of options (label/value pairs), selected defaults,
 * show/sync value toggles, and AI generation.
 */
export default function OptionDataInput( { optionField, field, builderClassNames } ) {
    const { updateField } = useDispatch( STORE_NAME );
    const i18n = useSelect( ( select ) => select( STORE_NAME ).getI18n(), [] );

    const isMultiple = !! optionField.is_multiple;

    // Local state for options list
    const [ options, setOptions ] = useState( [] );
    const [ selected, setSelected ] = useState( isMultiple ? [] : '' );
    const [ showValue, setShowValue ] = useState( false );
    const [ syncValue, setSyncValue ] = useState( true );

    // AI state
    const [ showAiModal, setShowAiModal ] = useState( false );
    const [ showAiConfigModal, setShowAiConfigModal ] = useState( false );
    const [ aiPrompt, setAiPrompt ] = useState( '' );
    const [ aiLoading, setAiLoading ] = useState( false );
    const [ aiError, setAiError ] = useState( '' );
    const [ aiGeneratedOptions, setAiGeneratedOptions ] = useState( [] );

    const initialized = useRef( false );

    // Initialize options from field data on mount
    useEffect( () => {
        if ( initialized.current ) {
            return;
        }
        initialized.current = true;

        const fieldOptions = field.options || {};
        const initialOptions = Object.entries( fieldOptions ).map( ( [ val, label ] ) => ( {
            label,
            value: val,
            id: getRandomId(),
        } ) );

        setOptions( initialOptions );

        const fieldSelected = field.selected;
        if ( isMultiple && ! Array.isArray( fieldSelected ) ) {
            setSelected( fieldSelected ? [ fieldSelected ] : [] );
        } else {
            setSelected( fieldSelected || ( isMultiple ? [] : '' ) );
        }
    }, [ field.options, field.selected, isMultiple ] );

    // Sync options back to field store
    useEffect( () => {
        if ( ! initialized.current ) {
            return;
        }

        const optionsObj = {};
        for ( const opt of options ) {
            optionsObj[ '' + opt.value ] = opt.label;
        }
        updateField( field.id, 'options', optionsObj );
    }, [ options, field.id, updateField ] );

    // Sync selected back to field store
    useEffect( () => {
        if ( ! initialized.current ) {
            return;
        }
        updateField( field.id, 'selected', selected );
    }, [ selected, field.id, updateField ] );

    const addOption = useCallback( () => {
        const count = options.length;
        const newOpt = ( i18n.option || 'option' ) + '-' + ( count + 1 );
        setOptions( [ ...options, { label: newOpt, value: newOpt, id: getRandomId() } ] );
    }, [ options, i18n.option ] );

    const deleteOption = useCallback( ( index ) => {
        if ( options.length === 1 ) {
            if ( window.swal ) {
                window.swal( {
                    text: i18n.last_choice_warn_msg || __( 'You must have at least one option.', 'wp-user-frontend' ),
                    showCancelButton: false,
                    confirmButtonColor: '#46b450',
                } );
            }
            return;
        }
        const newOptions = [ ...options ];
        newOptions.splice( index, 1 );
        setOptions( newOptions );
    }, [ options, i18n.last_choice_warn_msg ] );

    const setOptionLabel = useCallback( ( index, label ) => {
        const newOptions = [ ...options ];
        newOptions[ index ] = { ...newOptions[ index ], label };
        if ( syncValue ) {
            newOptions[ index ].value = label.toLocaleLowerCase().replace( /\s/g, '_' );
        }
        setOptions( newOptions );
    }, [ options, syncValue ] );

    const setOptionValue = useCallback( ( index, value ) => {
        const newOptions = [ ...options ];
        newOptions[ index ] = { ...newOptions[ index ], value };
        setOptions( newOptions );
    }, [ options ] );

    const handleSelectedChange = useCallback( ( optValue, checked ) => {
        if ( isMultiple ) {
            if ( checked ) {
                setSelected( ( prev ) => [ ...prev, optValue ] );
            } else {
                setSelected( ( prev ) => prev.filter( ( v ) => v !== optValue ) );
            }
        } else {
            setSelected( optValue );
        }
    }, [ isMultiple ] );

    const clearSelection = useCallback( () => {
        setSelected( isMultiple ? [] : '' );
    }, [ isMultiple ] );

    // AI methods
    const openAiModal = useCallback( () => {
        if ( window.wpuf_form_builder && ! window.wpuf_form_builder.ai_configured ) {
            setShowAiConfigModal( true );
            return;
        }
        setShowAiModal( true );
        setAiPrompt( '' );
        setAiError( '' );
        setAiGeneratedOptions( [] );
    }, [] );

    const closeAiModal = useCallback( () => {
        setShowAiModal( false );
        setAiPrompt( '' );
        setAiError( '' );
        setAiGeneratedOptions( [] );
        setAiLoading( false );
    }, [] );

    const generateAiOptions = useCallback( () => {
        if ( ! aiPrompt.trim() ) {
            return;
        }

        setAiLoading( true );
        setAiError( '' );

        wp.ajax.post( 'wpuf_ai_generate_field_options', {
            prompt: aiPrompt,
            field_type: field.template,
            nonce: window.wpuf_form_builder?.nonce,
        } ).done( ( response ) => {
            const opts = response.options || ( response.data && response.data.options ) || [];
            if ( opts.length > 0 ) {
                setAiGeneratedOptions( opts.map( ( opt ) => ( {
                    label: opt.label || opt,
                    value: opt.value || opt,
                    selected: true,
                } ) ) );
            } else {
                setAiError( response.message || i18n.something_went_wrong || __( 'Something went wrong.', 'wp-user-frontend' ) );
            }
        } ).fail( ( error ) => {
            setAiError( error.message || i18n.something_went_wrong || __( 'Something went wrong.', 'wp-user-frontend' ) );
        } ).always( () => {
            setAiLoading( false );
        } );
    }, [ aiPrompt, field.template, i18n.something_went_wrong ] );

    const importAiOptions = useCallback( () => {
        const selectedOpts = aiGeneratedOptions.filter( ( opt ) => opt.selected );
        const newOptions = [
            ...options,
            ...selectedOpts.map( ( opt ) => ( {
                label: opt.label,
                value: opt.value,
                id: getRandomId(),
            } ) ),
        ];
        setOptions( newOptions );
        closeAiModal();
    }, [ aiGeneratedOptions, options, closeAiModal ] );

    const allAiSelected = aiGeneratedOptions.length > 0 && aiGeneratedOptions.every( ( opt ) => opt.selected );

    const toggleAllAi = useCallback( () => {
        const selectState = ! allAiSelected;
        setAiGeneratedOptions( ( prev ) => prev.map( ( opt ) => ( { ...opt, selected: selectState } ) ) );
    }, [ allAiSelected ] );

    if ( field.hide_option_data ) {
        return null;
    }

    return (
        <div className="panel-field-opt panel-field-opt-text">
            <div className="wpuf-flex">
                <label className="wpuf-font-sm wpuf-text-gray-700">
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>

            { /* Show/Sync value toggles */ }
            <div className="wpuf-mt-2 wpuf-flex">
                <label className="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700">
                    <input
                        type="checkbox"
                        checked={ showValue }
                        onChange={ ( e ) => setShowValue( e.target.checked ) }
                        className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                    />
                    { __( 'Show values', 'wp-user-frontend' ) }
                </label>
                <label className="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700 wpuf-ml-8">
                    <input
                        type="checkbox"
                        checked={ syncValue }
                        onChange={ ( e ) => setSyncValue( e.target.checked ) }
                        className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                    />
                    { __( 'Sync values', 'wp-user-frontend' ) }
                </label>
            </div>

            { /* Options table */ }
            <div className="wpuf-mt-4">
                <div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-2">
                    <div className="wpuf-flex wpuf-items-center wpuf-gap-2">
                        <span className="wpuf-text-sm wpuf-text-gray-700 wpuf-font-medium">
                            { __( 'Label & Values', 'wp-user-frontend' ) }
                        </span>
                        <button
                            type="button"
                            onClick={ openAiModal }
                            className="wpuf-w-8 wpuf-h-8 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-shadow-sm hover:wpuf-shadow-md wpuf-border-0"
                            style={ { background: 'linear-gradient(135deg, #FFEE00 0%, #D500FF 28%, #0082FF 100%)' } }
                            title={ __( 'AI Generate Options', 'wp-user-frontend' ) }
                        >
                            <svg className="wpuf-w-5 wpuf-h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>
                    { /* Bulk Add — pro can replace via filter, free shows teaser */ }
                    { applyFilters( 'wpuf.formBuilder.optionDataBulkAdd', null, options, setOptions ) || (
                        <a
                            href={ ( window.wpuf_form_builder || {} ).pro_link || '' }
                            target="_blank"
                            rel="noopener noreferrer"
                            className="wpuf-relative wpuf-inline-block wpuf-group/pro-button"
                        >
                            <button
                                type="button"
                                className="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1 wpuf-rounded-md wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-gray-600 wpuf-bg-gray-100 hover:wpuf-bg-gray-200 wpuf-cursor-pointer wpuf-border-0"
                                title={ __( 'Available in Pro Version', 'wp-user-frontend' ) }
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="wpuf-size-4">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                                { __( 'Bulk Add', 'wp-user-frontend' ) }
                            </button>
                            <div className="wpuf-absolute wpuf-top-0 wpuf-right-0 wpuf-opacity-0 group-hover/pro-button:wpuf-opacity-100 wpuf-transition-all wpuf-pointer-events-none">
                                <img src={ `${ ( window.wpuf_form_builder || {} ).asset_url || '' }/images/pro-badge.svg` } alt="" />
                            </div>
                        </a>
                    ) }
                </div>

                <table className="option-field-option-chooser">
                    <tbody>
                        { options.map( ( option, index ) => (
                            <tr
                                key={ option.id }
                                data-index={ index }
                                className="option-field-option wpuf-flex wpuf-justify-start wpuf-items-center"
                            >
                                <td className="wpuf-flex wpuf-items-center">
                                    { isMultiple ? (
                                        <input
                                            type="checkbox"
                                            value={ option.value }
                                            checked={ Array.isArray( selected ) && selected.includes( option.value ) }
                                            onChange={ ( e ) => handleSelectedChange( option.value, e.target.checked ) }
                                            className={ builderClassNames( 'checkbox' ) }
                                        />
                                    ) : (
                                        <input
                                            type="radio"
                                            value={ option.value }
                                            checked={ selected === option.value }
                                            onChange={ () => handleSelectedChange( option.value, true ) }
                                            className={ `!wpuf-mt-0 ${ builderClassNames( 'radio' ) }` }
                                        />
                                    ) }
                                    <i className="fa fa-bars sort-handler hover:!wpuf-cursor-move wpuf-text-gray-400 wpuf-ml-1" />
                                </td>
                                <td>
                                    <input
                                        className={ `${ builderClassNames( 'text' ) } !wpuf-w-full` }
                                        type="text"
                                        value={ option.label }
                                        onChange={ ( e ) => setOptionLabel( index, e.target.value ) }
                                    />
                                </td>
                                { showValue && (
                                    <td>
                                        <input
                                            className={ `${ builderClassNames( 'text' ) } !wpuf-w-full` }
                                            type="text"
                                            value={ option.value }
                                            onChange={ ( e ) => setOptionValue( index, e.target.value ) }
                                        />
                                    </td>
                                ) }
                                <td>
                                    <div className="wpuf-flex wpuf-ml-2">
                                        <div
                                            onClick={ () => deleteOption( index ) }
                                            className="action-buttons hover:wpuf-cursor-pointer"
                                            role="button"
                                            tabIndex={ 0 }
                                            onKeyDown={ ( e ) => e.key === 'Enter' && deleteOption( index ) }
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 hover:wpuf-border-primary wpuf-p-1">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14" />
                                            </svg>
                                        </div>
                                        { index === options.length - 1 && (
                                            <div
                                                onClick={ addOption }
                                                className="plus-buttons hover:wpuf-cursor-pointer !wpuf-border-0"
                                                role="button"
                                                tabIndex={ 0 }
                                                onKeyDown={ ( e ) => e.key === 'Enter' && addOption() }
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="wpuf-ml-1 wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 wpuf-p-1">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </div>
                                        ) }
                                    </div>
                                </td>
                            </tr>
                        ) ) }
                    </tbody>
                </table>
            </div>

            { /* Clear selection link for radio/select */ }
            { ! isMultiple && selected && (
                <a
                    className="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700 hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 wpuf-mt-4"
                    href="#clear"
                    onClick={ ( e ) => {
                        e.preventDefault();
                        clearSelection();
                    } }
                >
                    { __( 'Clear Selection', 'wp-user-frontend' ) }
                </a>
            ) }

            { /* AI Generate Options Modal */ }
            { showAiModal && (
                <div className="wpuf-ai-modal-overlay" onClick={ closeAiModal }>
                    <div className="wpuf-ai-modal" onClick={ ( e ) => e.stopPropagation() }>
                        <div className="wpuf-ai-modal-header">
                            <h3>{ __( 'AI Generate Options', 'wp-user-frontend' ) }</h3>
                            <button type="button" onClick={ closeAiModal } className="wpuf-ai-modal-close">&times;</button>
                        </div>
                        <div className="wpuf-ai-modal-body">
                            <label className="wpuf-block wpuf-mb-2 wpuf-text-sm wpuf-font-medium">
                                { __( 'Describe the options you need', 'wp-user-frontend' ) }
                            </label>
                            <textarea
                                value={ aiPrompt }
                                onChange={ ( e ) => setAiPrompt( e.target.value ) }
                                rows="3"
                                className="wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-border wpuf-rounded"
                                placeholder={ __( 'e.g., List of US states, Business categories, Job titles', 'wp-user-frontend' ) }
                            />
                            { aiError && (
                                <div className="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600">{ aiError }</div>
                            ) }
                            { aiGeneratedOptions.length > 0 && (
                                <div className="wpuf-mt-4">
                                    <div className="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mb-2">
                                        <span className="wpuf-text-sm wpuf-font-medium">
                                            { __( 'Generated Options', 'wp-user-frontend' ) }
                                        </span>
                                        <button type="button" onClick={ toggleAllAi } className="wpuf-text-sm wpuf-text-primary hover:wpuf-underline">
                                            { allAiSelected
                                                ? __( 'Deselect All', 'wp-user-frontend' )
                                                : __( 'Select All', 'wp-user-frontend' ) }
                                        </button>
                                    </div>
                                    <div className="wpuf-ai-options-list">
                                        { aiGeneratedOptions.map( ( opt, idx ) => (
                                            <label key={ idx } className="wpuf-flex wpuf-items-center wpuf-py-1 wpuf-cursor-pointer hover:wpuf-bg-gray-50 wpuf-rounded wpuf-px-2">
                                                <input
                                                    type="checkbox"
                                                    checked={ opt.selected }
                                                    onChange={ ( e ) => {
                                                        setAiGeneratedOptions( ( prev ) => {
                                                            const next = [ ...prev ];
                                                            next[ idx ] = { ...next[ idx ], selected: e.target.checked };
                                                            return next;
                                                        } );
                                                    } }
                                                    className={ builderClassNames( 'checkbox' ) }
                                                />
                                                <span className="wpuf-text-sm wpuf-text-gray-700">{ opt.label }</span>
                                            </label>
                                        ) ) }
                                    </div>
                                </div>
                            ) }
                        </div>
                        <div className="wpuf-ai-modal-footer">
                            <button type="button" onClick={ closeAiModal } className="wpuf-btn wpuf-btn-secondary">
                                { __( 'Cancel', 'wp-user-frontend' ) }
                            </button>
                            { aiGeneratedOptions.length === 0 ? (
                                <button
                                    type="button"
                                    onClick={ generateAiOptions }
                                    disabled={ aiLoading || ! aiPrompt }
                                    className="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white wpuf-inline-flex wpuf-items-center disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed wpuf-border-0"
                                >
                                    { aiLoading && <i className="fa fa-spinner fa-spin wpuf-mr-1" /> }
                                    { aiLoading
                                        ? __( 'Generating...', 'wp-user-frontend' )
                                        : __( 'Generate', 'wp-user-frontend' ) }
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    onClick={ importAiOptions }
                                    className="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white wpuf-inline-flex wpuf-items-center wpuf-border-0"
                                >
                                    { __( 'Import Selected', 'wp-user-frontend' ) }
                                </button>
                            ) }
                        </div>
                    </div>
                </div>
            ) }

            { /* AI Provider Config Modal */ }
            { showAiConfigModal && (
                <div className="wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-black wpuf-bg-opacity-50 wpuf-z-[1000000] wpuf-flex wpuf-items-center wpuf-justify-center">
                    <div className="wpuf-bg-white wpuf-rounded-md wpuf-p-8 wpuf-max-w-xl wpuf-w-full wpuf-mx-5 wpuf-relative">
                        <div className="wpuf-flex wpuf-justify-center wpuf-mb-8">
                            <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="110" height="110" rx="55" fill="#D1FAE5" />
                                <path fillRule="evenodd" clipRule="evenodd" d="M60 41C55.0294 41 51 45.0294 51 50C51 50.525 51.0451 51.0402 51.1317 51.5419C51.2213 52.0604 51.089 52.4967 50.8369 52.7489L42.1716 61.4142C41.4214 62.1644 41 63.1818 41 64.2426V68C41 68.5523 41.4477 69 42 69H47C47.5523 69 48 68.5523 48 68V66H50C50.5523 66 51 65.5523 51 65V63H53C53.2652 63 53.5196 62.8946 53.7071 62.7071L57.2511 59.1631C57.5033 58.911 57.9396 58.7787 58.4581 58.8683C58.9598 58.9549 59.475 59 60 59C64.9706 59 69 54.9706 69 50C69 45.0294 64.9706 41 60 41ZM60 45C59.4477 45 59 45.4477 59 46C59 46.5523 59.4477 47 60 47C61.6569 47 63 48.3431 63 50C63 50.5523 63.4477 51 64 51C64.5523 51 65 50.5523 65 50C65 47.2386 62.7614 45 60 45Z" fill="#065F46" />
                            </svg>
                        </div>
                        <h2 className="wpuf-text-2xl wpuf-font-medium wpuf-text-center wpuf-text-gray-900 wpuf-mb-4">
                            { __( 'AI Provider Not Configured', 'wp-user-frontend' ) }
                        </h2>
                        <p className="wpuf-text-lg wpuf-text-center wpuf-text-gray-400 wpuf-mb-16">
                            { __( 'To use AI Form Generation, please connect an AI provider by adding your API key in the settings', 'wp-user-frontend' ) }
                        </p>
                        <div className="wpuf-flex wpuf-justify-center wpuf-gap-3">
                            <button
                                type="button"
                                onClick={ () => setShowAiConfigModal( false ) }
                                className="wpuf-px-6 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-text-lg wpuf-transition-colors wpuf-min-w-btn-cancel"
                            >
                                { __( 'Cancel', 'wp-user-frontend' ) }
                            </button>
                            <button
                                type="button"
                                onClick={ () => {
                                    window.location.href = window.wpuf_form_builder?.ai_settings_url || '';
                                } }
                                className="wpuf-px-6 wpuf-py-3 wpuf-bg-emerald-700 hover:wpuf-bg-emerald-800 wpuf-text-white wpuf-rounded-md wpuf-text-lg wpuf-transition-colors wpuf-min-w-btn-save"
                            >
                                { __( 'Go to Settings', 'wp-user-frontend' ) }
                            </button>
                        </div>
                    </div>
                </div>
            ) }
        </div>
    );
}
