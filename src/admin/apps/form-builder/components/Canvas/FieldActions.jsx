import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dialogs } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import { isFieldSingleInstance, containsField } from '../../utils/fieldUtils';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { getRandomId } from '../../utils/globalHelpers';
import { getFieldValidators } from '../../extensions/registry';
import { showOops } from '../Dnd/usePaletteDrop';
import { builderData } from '../../utils/boot';

const ACTION_CLASSES = 'inline-flex h-7 items-center gap-1 px-2 text-xs font-medium leading-none hover:cursor-pointer hover:text-white';

/**
 * Develop's bars: inner (column / repeat) fields get an icon-only bar shown on
 * hover of their own named group.
 */
const BAR_CLASSES = {
    column: 'wpuf-column-field-control-buttons absolute top-1 right-1 z-20 flex items-center rounded-md bg-primary px-0.5 shadow-sm transition duration-150 wpuf-ease-out group-hover/column-inner:opacity-100 focus-within:opacity-100',
    repeat: 'wpuf-repeat-field-control-buttons absolute top-1 right-1 z-20 flex items-center rounded-md bg-primary px-0.5 shadow-sm transition duration-150 wpuf-ease-out group-hover/repeat-inner:opacity-100 focus-within:opacity-100',
};
const BAR_INNER_CLASSES = {
    column: 'flex items-center text-white/85',
    repeat: 'flex items-center text-white/85',
};
const INNER_ACTION = 'inline-flex h-6 w-6 items-center justify-center rounded hover:bg-white/15 hover:text-white hover:cursor-pointer';
const INNER_ACTION_CLASSES = {
    column: INNER_ACTION,
    repeat: INNER_ACTION,
};

export default function FieldActions( { field, index, container = { type: 'top' }, dragListeners, dragAttributes, showMove = true, variant = 'top' } ) {
    const { editingFieldId, fieldSettings, formFields, isProActive } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            editingFieldId: store.getEditingFieldId(),
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
            isProActive: store.getIsProActive(),
        };
    }, [] );

    const {
        openFieldSettings,
        moveField,
        cloneField,
        removeField,
        cloneColumnField,
        removeColumnField,
        cloneRepeatField,
        removeRepeatField,
    } = useDispatch( STORE_NAME );

    // Copy and Remove act on the list the field lives in (top level, a column
    // cell or a repeat field), never on the top-level list by index.
    const cloneInContainer = useCallback( ( newId ) => {
        if ( 'column' === container.type ) {
            cloneColumnField( container.columnFieldId, container.column, field.id, newId );
        } else if ( 'repeat' === container.type ) {
            cloneRepeatField( container.repeatFieldId, index, newId );
        } else {
            cloneField( field.id, newId );
        }
    }, [ container, field.id, index, cloneField, cloneColumnField, cloneRepeatField ] );

    const removeFromContainer = useCallback( () => {
        if ( 'column' === container.type ) {
            removeColumnField( container.columnFieldId, container.column, index );
        } else if ( 'repeat' === container.type ) {
            removeRepeatField( container.repeatFieldId, index );
        } else {
            removeField( index );
        }
    }, [ container, index, removeField, removeColumnField, removeRepeatField ] );

    const isEditing = parseInt( editingFieldId ) === parseInt( field.id );
    const isInner = !! BAR_CLASSES[ variant ];
    const actionClasses = isInner ? INNER_ACTION_CLASSES[ variant ] : ACTION_CLASSES;
    const isProPreview = ! isProActive && !! fieldSettings[ field.template ]?.pro_feature;
    const failedValidation = isFailedToValidate( field.template, fieldSettings, getFieldValidators() );

    const data = builderData();
    const i18n = data.i18n || {};
    const singleObjects = data.wpuf_single_objects || [];

    // Move up / down one place (FlyForms field toolbar), top-level fields only.
    const canStep = ! isInner && 'top' === container.type;
    const handleStep = useCallback( ( event, delta ) => {
        event.stopPropagation();

        const to = index + delta;

        if ( to < 0 || to >= formFields.length ) {
            return;
        }

        moveField( index, to );
    }, [ index, formFields.length, moveField ] );

    const handleEdit = useCallback( () => {
        openFieldSettings( field.id );
    }, [ field.id, openFieldSettings ] );

    const handleClone = useCallback( () => {
        if ( isFieldSingleInstance( field.template, singleObjects ) && containsField( formFields, field.template ) ) {
            // The palette's styled alert (owner 2026-10-04; develop showed a plain one here).
            showOops( __( 'You already have this field in the form', 'wp-user-frontend' ) );
            return;
        }

        cloneInContainer( getRandomId() );
    }, [ field.template, formFields, singleObjects, cloneInContainer ] );

    const handleDelete = useCallback( () => {
        dialogs.confirm( {
            title: i18n.delete_field_warn_title || __( 'Are you sure?', 'wp-user-frontend' ),
            message: i18n.delete_field_warn_msg || __( 'Are you sure you want to delete this field?', 'wp-user-frontend' ),
            confirmText: i18n.yes_delete_it || __( 'Yes, delete it!', 'wp-user-frontend' ),
            cancelText: i18n.no_cancel_it || __( 'No, cancel it!', 'wp-user-frontend' ),
            tone: 'danger',
        } ).then( ( confirmed ) => {
            if ( confirmed ) {
                removeFromContainer();
            }
        } );
    }, [ i18n, removeFromContainer ] );

    return (
        <div
            className={ isInner
                ? `${ BAR_CLASSES[ variant ] } ${ isEditing ? 'opacity-100' : 'opacity-0' }`
                : `field-buttons absolute -top-3.5 right-4 z-20 rounded-md bg-primary! px-1 shadow-sm items-center transition duration-150 wpuf-ease-out flex group-hover:opacity-100 focus-within:opacity-100 ${ isEditing ? 'opacity-100' : 'opacity-0 group-has-[.column-field-items:hover]:opacity-0! group-has-[.repeat-field-items:hover]:opacity-0!' }` }
        >
            <div className={ isInner ? BAR_INNER_CLASSES[ variant ] : 'flex items-center text-white/85' }>
                { ! failedValidation ? (
                    <>
                        { showMove && (
                            <span className="inline-flex h-7 items-center pl-2 pr-1.5 mr-1 border-0 border-r border-solid border-white/30 hover:text-white" { ...dragListeners } { ...dragAttributes }>
                                <i className="fa fa-arrows move leading-none hover:cursor-move! text-[13px]" />
                            </span>
                        ) }
                        { canStep && [ [ -1, __( 'Move up', 'wp-user-frontend' ), 'M5 12.5 10 7.5l5 5' ], [ 1, __( 'Move down', 'wp-user-frontend' ), 'M5 7.5l5 5 5-5' ] ].map( ( [ delta, label, path ] ) => {
                            const disabled = index + delta < 0 || index + delta >= formFields.length;

                            return (
                                <button
                                    key={ delta }
                                    type="button"
                                    className={ `inline-flex h-7 w-6 items-center justify-center border-0 bg-transparent p-0 text-white/85 ${ disabled ? 'opacity-40 cursor-not-allowed' : 'hover:text-white hover:cursor-pointer' }` }
                                    onClick={ ( e ) => handleStep( e, delta ) }
                                    disabled={ disabled }
                                    aria-label={ label }
                                    aria-keyshortcuts={ delta < 0 ? 'Alt+ArrowUp' : 'Alt+ArrowDown' }
                                    title={ `${ label } (Alt+${ delta < 0 ? '↑' : '↓' })` }
                                >
                                    <svg className="block" width="14" height="14" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d={ path } stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            );
                        } ) }
                        <span className={ actionClasses } onClick={ handleEdit } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleEdit() }>
                            <svg className="block shrink-0" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.43306 13.9163L6.69485 10.7618C6.89603 10.2589 7.19728 9.802 7.58033 9.41896L14.4995 2.50023C15.3279 1.6718 16.6711 1.6718 17.4995 2.50023C18.3279 3.32865 18.3279 4.6718 17.4995 5.50023L10.5803 12.419C10.1973 12.802 9.74042 13.1033 9.23746 13.3044L6.08299 14.5662C5.67484 14.7295 5.2698 14.3244 5.43306 13.9163Z" fill="#A7F3D0" />
                                <path d="M3.5 5.74951C3.5 5.05916 4.05964 4.49951 4.75 4.49951H10C10.4142 4.49951 10.75 4.16373 10.75 3.74951C10.75 3.3353 10.4142 2.99951 10 2.99951H4.75C3.23122 2.99951 2 4.23073 2 5.74951V15.2495C2 16.7683 3.23122 17.9995 4.75 17.9995H14.25C15.7688 17.9995 17 16.7683 17 15.2495V9.99951C17 9.5853 16.6642 9.24951 16.25 9.24951C15.8358 9.24951 15.5 9.5853 15.5 9.99951V15.2495C15.5 15.9399 14.9404 16.4995 14.25 16.4995H4.75C4.05964 16.4995 3.5 15.9399 3.5 15.2495V5.74951Z" fill="#A7F3D0" />
                            </svg>
                            { ! isInner && __( 'Edit', 'wp-user-frontend' ) }
                        </span>
                        <span className={ actionClasses } onClick={ handleClone } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleClone() }>
                            <svg className="block shrink-0" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.75 6.875V5C13.75 3.96447 12.9105 3.125 11.875 3.125H5C3.96447 3.125 3.125 3.96447 3.125 5V11.875C3.125 12.9105 3.96447 13.75 5 13.75H6.875M13.75 6.875H15C16.0355 6.875 16.875 7.71447 16.875 8.75V15C16.875 16.0355 16.0355 16.875 15 16.875H8.75C7.71447 16.875 6.875 16.0355 6.875 15V13.75M13.75 6.875H8.75C7.71447 6.875 6.875 7.71447 6.875 8.75V13.75" stroke="#A7F3D0" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            { ! isInner && __( 'Copy', 'wp-user-frontend' ) }
                        </span>
                    </>
                ) : (
                    <>
                        <span className={ actionClasses }>
                            <i className="fa fa-arrows control-button-disabled -ml-1 rounded-l-md" />
                        </span>
                        <span className={ actionClasses }>
                            <i className="fa fa-pencil control-button-disabled -ml-1" />
                            { ! isInner && ' ' + __( 'Edit', 'wp-user-frontend' ) }
                        </span>
                        <span className={ actionClasses }>
                            <i className="fa fa-clone control-button-disabled -ml-1" />
                            { ! isInner && ' ' + __( 'Copy', 'wp-user-frontend' ) }
                        </span>
                    </>
                ) }
                <span className={ actionClasses } onClick={ handleDelete } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleDelete() }>
                    <svg className="block shrink-0" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.2837 7.5L11.9952 15M8.00481 15L7.71635 7.5M16.023 4.82547C16.308 4.86851 16.592 4.91456 16.875 4.96358M16.023 4.82547L15.1332 16.3938C15.058 17.3707 14.2434 18.125 13.2637 18.125H6.73631C5.75655 18.125 4.94198 17.3707 4.86683 16.3938L3.97696 4.82547M16.023 4.82547C15.0677 4.6812 14.1013 4.57071 13.125 4.49527M3.125 4.96358C3.40798 4.91456 3.69198 4.86851 3.97696 4.82547M3.97696 4.82547C4.93231 4.6812 5.89874 4.57071 6.875 4.49527M13.125 4.49527V3.73182C13.125 2.74902 12.3661 1.92853 11.3838 1.8971C10.9244 1.8824 10.463 1.875 10 1.875C9.53696 1.875 9.07565 1.8824 8.61618 1.8971C7.63388 1.92853 6.875 2.74902 6.875 3.73182V4.49527M13.125 4.49527C12.0938 4.41558 11.0516 4.375 10 4.375C8.94836 4.375 7.9062 4.41558 6.875 4.49527" stroke="#A7F3D0" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    { ! isInner && __( 'Remove', 'wp-user-frontend' ) }
                </span>
                { isProPreview && (
                    <span className={ `${ actionClasses } hover:bg-primary` }>
                        <a
                            href={ data.pro_link || '' }
                            target="_blank"
                            rel="noopener noreferrer"
                            className="rounded-r-md hover:bg-slate-500 hover:cursor-pointer transition duration-150 wpuf-ease-out hover:transition-all"
                        >
                            <img src={ `${ data.asset_url || '' }/images/pro-badge.svg` } alt="" className="h-4" />
                        </a>
                    </span>
                ) }
            </div>
        </div>
    );
}
