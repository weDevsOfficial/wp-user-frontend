import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { request, restPath } from '@wpuf/api';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../../store';
import {
    DndContext,
    closestCenter,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { RadioGroup, RadioGroupItem } from '@wedevs/plugin-ui';
import { ListPlus } from 'lucide-react';
import { AIConfigModal, AiButton, Button, Checkbox, Modal, Notice, ProBadge, TextInput, Textarea, dialogs } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';
import { BUILDER_SLOTS, BuilderSlot } from '../../../slots';
import { openRoute } from '../../../../../app/client';
import { builderData } from '../../../utils/boot';

/**
 * Generate a random ID for option rows.
 */
function getRandomId() {
    return Math.floor( Math.random() * ( 9999999 - 999 + 1 ) ) + 999;
}

/**
 * One option row, draggable by its handle (develop sorted rows by
 * `.sort-handler`). `children` gets the props for the handle.
 */
function SortableOptionRow( { id, index, children } ) {
    const { attributes, listeners, setNodeRef, transform, transition } = useSortable( { id: String( id ) } );

    return (
        <tr
            ref={ setNodeRef }
            style={ { transform: CSS.Translate.toString( transform ), transition } }
            data-index={ index }
            className="option-field-option flex justify-start items-center"
        >
            { children( { ...attributes, ...listeners } ) }
        </tr>
    );
}

/**
 * Option data input for select, radio, checkbox, multiselect fields.
 * Replaces Vue field-option-data component.
 *
 * Manages the list of options (label/value pairs), selected defaults,
 * show/sync value toggles, and AI generation.
 */
export default function OptionDataInput( { optionField, field } ) {
    const { updateField } = useDispatch( STORE_NAME );
    const i18n = useSelect( ( select ) => select( STORE_NAME ).getI18n(), [] );

    const isMultiple = !! optionField.is_multiple;

    // Local state for options list
    // State starts from the field, so opening the panel writes nothing.
    const [ options, setOptions ] = useState( () =>
        Object.entries( field.options || {} ).map( ( [ val, label ] ) => ( {
            label,
            value: val,
            id: getRandomId(),
        } ) )
    );
    const [ selected, setSelected ] = useState( () => {
        const fieldSelected = field.selected;

        if ( isMultiple && ! Array.isArray( fieldSelected ) ) {
            return fieldSelected ? [ fieldSelected ] : [];
        }

        return fieldSelected || ( isMultiple ? [] : '' );
    } );
    const [ showValue, setShowValue ] = useState( false );
    const [ syncValue, setSyncValue ] = useState( true );

    // AI state
    const [ showAiModal, setShowAiModal ] = useState( false );
    const [ showAiConfigModal, setShowAiConfigModal ] = useState( false );
    const [ aiPrompt, setAiPrompt ] = useState( '' );
    const [ aiLoading, setAiLoading ] = useState( false );
    const [ aiError, setAiError ] = useState( '' );
    const [ aiGeneratedOptions, setAiGeneratedOptions ] = useState( [] );

    // Only an edit (new state object) is written back to the store.
    const initialOptions = useRef( options );
    const initialSelected = useRef( selected );

    // Sync options back to field store
    useEffect( () => {
        if ( options === initialOptions.current ) {
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
        if ( selected === initialSelected.current ) {
            return;
        }
        updateField( field.id, 'selected', selected );
    }, [ selected, field.id, updateField ] );

    const sensors = useSensors( useSensor( PointerSensor, { activationConstraint: { distance: 4 } } ) );

    const handleSortEnd = useCallback( ( { active, over } ) => {
        if ( ! over || active.id === over.id ) {
            return;
        }

        setOptions( ( current ) => arrayMove(
            current,
            current.findIndex( ( option ) => String( option.id ) === active.id ),
            current.findIndex( ( option ) => String( option.id ) === over.id )
        ) );
    }, [] );

    const addOption = useCallback( () => {
        const count = options.length;
        const newOpt = ( i18n.option || 'option' ) + '-' + ( count + 1 );
        setOptions( [ ...options, { label: newOpt, value: newOpt, id: getRandomId() } ] );
    }, [ options, i18n.option ] );

    const deleteOption = useCallback( ( index ) => {
        if ( options.length === 1 ) {
            dialogs.alert( { title: i18n.last_choice_warn_msg || __( 'You must have at least one option.', 'wp-user-frontend' ) } );
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
        if ( ! builderData().ai_configured ) {
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

        request( restPath( 'wpuf/v1', '/ai-form-builder/generate-options' ), {
            method: 'POST',
            data: { prompt: aiPrompt, field_type: field.template },
        } ).then( ( response ) => {
            const opts = ( response && response.options ) || [];
            if ( opts.length > 0 ) {
                setAiGeneratedOptions( opts.map( ( opt ) => ( {
                    label: opt.label || opt,
                    value: opt.value || opt,
                    selected: true,
                } ) ) );
            } else {
                setAiError( i18n.something_went_wrong || __( 'Something went wrong.', 'wp-user-frontend' ) );
            }
        } ).catch( ( error ) => {
            setAiError( ( error && error.message ) || i18n.something_went_wrong || __( 'Something went wrong.', 'wp-user-frontend' ) );
        } ).finally( () => {
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
            <div className="flex">
                <label className=" text-gray-700">
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>

            { /* Show/Sync value toggles */ }
            <div className="mt-2 flex">
                <Checkbox
                    id={ `wpuf-show-values-${ field.id }` }
                    data-value="show-values"
                    value={ showValue }
                    onChange={ setShowValue }
                    label={ __( 'Show values', 'wp-user-frontend' ) }
                />
                <span className="ml-8">
                    <Checkbox
                        id={ `wpuf-sync-values-${ field.id }` }
                        data-value="sync-values"
                        value={ syncValue }
                        onChange={ setSyncValue }
                        label={ __( 'Sync values', 'wp-user-frontend' ) }
                    />
                </span>
            </div>

            { /* Options table */ }
            <div className="mt-4">
                { /* The label, then the actions (they move under it when the panel is narrow). */ }
                <div className="flex flex-wrap items-center justify-between gap-x-2 gap-y-1.5 mb-2">
                    <span className="text-sm text-gray-700 font-medium whitespace-nowrap">
                        { __( 'Label & Values', 'wp-user-frontend' ) }
                    </span>
                    <div className="ml-auto flex items-center gap-1.5 whitespace-nowrap">
                        <AiButton size="sm" onClick={ openAiModal } title={ __( 'AI Generate Options', 'wp-user-frontend' ) }>
                            { __( 'Generate', 'wp-user-frontend' ) }
                        </AiButton>
                        <BuilderSlot name={ BUILDER_SLOTS.OPTION_DATA_ACTIONS } fillProps={ { field, options, setOptions } } />
                        { /* Bulk Add — pro can replace via filter, free shows teaser */ }
                        { applyFilters( 'wpuf.formBuilder.optionDataBulkAdd', null, options, setOptions, syncValue ) || (
                            <span className="relative inline-block group/pro-button">
                                <Button
                                    variant="secondary"
                                    size="xs"
                                    href={ builderData().pro_link || '' }
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title={ __( 'Available in Pro Version', 'wp-user-frontend' ) }
                                >
                                    <ListPlus className="size-3.5" aria-hidden="true" />
                                    { __( 'Bulk Add', 'wp-user-frontend' ) }
                                </Button>
                                <span className="absolute top-0 right-0 opacity-0 group-hover/pro-button:opacity-100 wpuf-transition-all pointer-events-none">
                                    <ProBadge link={ false } />
                                </span>
                            </span>
                        ) }
                    </div>
                </div>

                <DndContext sensors={ sensors } collisionDetection={ closestCenter } onDragEnd={ handleSortEnd }>
                    <SortableContext items={ options.map( ( option ) => String( option.id ) ) } strategy={ verticalListSortingStrategy }>
                        { /* One radio group for the default choice of a single-choice field
                             (each row holds one RadioGroupItem); checkboxes stand alone. */ }
                        <RadioGroup
                            render={ <table className="option-field-option-chooser" /> }
                            value={ isMultiple ? undefined : ( 'string' === typeof selected ? selected : '' ) }
                            onValueChange={ ( next ) => ! isMultiple && handleSelectedChange( next, true ) }
                        >
                            <tbody>
                                { options.map( ( option, index ) => (
                                    <SortableOptionRow key={ option.id } id={ option.id } index={ index }>
                                        { ( handleProps ) => ( <>
                                            <td className="flex items-center">
                                                { isMultiple ? (
                                                    <Checkbox
                                                        data-value={ option.value }
                                                        aria-label={ option.label }
                                                        value={ Array.isArray( selected ) && selected.includes( option.value ) }
                                                        onChange={ ( on ) => handleSelectedChange( option.value, on ) }
                                                    />
                                                ) : (
                                                    <RadioGroupItem
                                                        value={ option.value }
                                                        data-value={ option.value }
                                                        aria-label={ option.label }
                                                        className="cursor-pointer"
                                                    />
                                                ) }
                                                <i className="fa fa-bars sort-handler hover:cursor-move! text-gray-400 ml-1" { ...handleProps } />
                                            </td>
                                            <td>
                                                <TextInput
                                                    className="w-full"
                                                    value={ option.label }
                                                    aria-label={ __( 'Label', 'wp-user-frontend' ) }
                                                    onChange={ ( next ) => setOptionLabel( index, next ) }
                                                />
                                            </td>
                                            { showValue && (
                                                <td>
                                                    <TextInput
                                                        className="w-full"
                                                        value={ option.value }
                                                        aria-label={ __( 'Value', 'wp-user-frontend' ) }
                                                        onChange={ ( next ) => setOptionValue( index, next ) }
                                                    />
                                                </td>
                                            ) }
                                            <td>
                                                <div className="flex ml-2">
                                                    <div
                                                        onClick={ () => deleteOption( index ) }
                                                        className="action-buttons hover:cursor-pointer"
                                                        role="button"
                                                        tabIndex={ 0 }
                                                        onKeyDown={ ( e ) => e.key === 'Enter' && deleteOption( index ) }
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 border rounded-2xl border-gray-400 hover:border-primary p-1">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14" />
                                                        </svg>
                                                    </div>
                                                    { index === options.length - 1 && (
                                                        <div
                                                            onClick={ addOption }
                                                            className="plus-buttons hover:cursor-pointer border-0!"
                                                            role="button"
                                                            tabIndex={ 0 }
                                                            onKeyDown={ ( e ) => e.key === 'Enter' && addOption() }
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="ml-1 size-6 border rounded-2xl border-gray-400 p-1">
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                            </svg>
                                                        </div>
                                                    ) }
                                                </div>
                                            </td>
                                        </> ) }
                                    </SortableOptionRow>
                                ) ) }
                            </tbody>
                        </RadioGroup>
                    </SortableContext>
                </DndContext>
            </div>

            <BuilderSlot name={ BUILDER_SLOTS.OPTION_DATA_AFTER } fillProps={ { field, options, setOptions } } />

            { /* Clear selection link for radio/select */ }
            { ! isMultiple && selected && (
                <Button variant="secondary" size="sm" className="mt-4" onClick={ clearSelection }>
                    { __( 'Clear Selection', 'wp-user-frontend' ) }
                </Button>
            ) }

            { /* AI Generate Options Modal (`.wpuf-ai-modal` is what the e2e specs find). */ }
            <Modal
                open={ showAiModal }
                onClose={ closeAiModal }
                showClose={ ! aiLoading }
                title={ __( 'AI Generate Options', 'wp-user-frontend' ) }
                className="wpuf-ai-modal sm:max-w-lg"
            >
                <div className="wpuf-ai-modal-body">
                    <label htmlFor={ `wpuf-ai-prompt-${ field.id }` } className="mb-2 block text-sm font-medium text-gray-900">
                        { __( 'Describe the options you need', 'wp-user-frontend' ) }
                    </label>
                    <Textarea
                        id={ `wpuf-ai-prompt-${ field.id }` }
                        value={ aiPrompt }
                        onChange={ ( value ) => setAiPrompt( value ) }
                        rows={ 3 }
                        placeholder={ __( 'e.g., List of US states, Business categories, Job titles', 'wp-user-frontend' ) }
                    />
                    { aiError && (
                        <Notice tone="error" className="mt-3">{ aiError }</Notice>
                    ) }
                    { aiGeneratedOptions.length > 0 && (
                        <div className="mt-4">
                            <div className="mb-2 flex items-center justify-between">
                                <span className="text-sm font-medium text-gray-900">
                                    { __( 'Generated Options', 'wp-user-frontend' ) }
                                </span>
                                <Button variant="link" onClick={ toggleAllAi }>
                                    { allAiSelected
                                        ? __( 'Deselect All', 'wp-user-frontend' )
                                        : __( 'Select All', 'wp-user-frontend' ) }
                                </Button>
                            </div>
                            <div className="wpuf-ai-options-list max-h-64 overflow-y-auto rounded-md border border-solid border-gray-200 p-1">
                                { aiGeneratedOptions.map( ( opt, idx ) => (
                                    <div key={ idx } className="rounded-sm px-2 py-1 hover:bg-gray-50">
                                        <Checkbox
                                            id={ `wpuf-ai-option-${ field.id }-${ idx }` }
                                            value={ !! opt.selected }
                                            onChange={ ( on ) => {
                                                setAiGeneratedOptions( ( prev ) => {
                                                    const next = [ ...prev ];
                                                    next[ idx ] = { ...next[ idx ], selected: on };
                                                    return next;
                                                } );
                                            } }
                                            label={ opt.label }
                                        />
                                    </div>
                                ) ) }
                            </div>
                        </div>
                    ) }
                </div>
                <div className="wpuf-ai-modal-footer mt-6 flex justify-end gap-3">
                    <Button variant="secondary" size="lg" onClick={ closeAiModal } disabled={ aiLoading }>
                        { __( 'Cancel', 'wp-user-frontend' ) }
                    </Button>
                    { aiGeneratedOptions.length === 0 ? (
                        <AiButton onClick={ generateAiOptions } disabled={ aiLoading || ! aiPrompt } aria-busy={ aiLoading || undefined }>
                            { aiLoading
                                ? __( 'Generating...', 'wp-user-frontend' )
                                : __( 'Generate', 'wp-user-frontend' ) }
                        </AiButton>
                    ) : (
                        <AiButton onClick={ importAiOptions }>
                            { __( 'Import Selected', 'wp-user-frontend' ) }
                        </AiButton>
                    ) }
                </div>
            </Modal>

            { /* AI Provider Config Modal */ }
            <AIConfigModal
                isOpen={ showAiConfigModal }
                onClose={ () => setShowAiConfigModal( false ) }
                onGoToSettings={ () => {
                    // In the admin app: the AI settings section (the leave guard still asks about unsaved edits).
                    openRoute( '/settings?tab=wpuf_ai', builderData().ai_settings_url || '' );
                } }
            />
        </div>
    );
}
