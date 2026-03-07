import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { isFieldSingleInstance, containsField } from '../../utils/fieldUtils';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { getRandomId } from '../../utils/globalHelpers';
import { getFieldValidators } from '../../extensions/registry';

const ACTION_CLASSES = 'wpuf-p-2 hover:wpuf-cursor-pointer hover:wpuf-text-white wpuf-flex';

export default function FieldActions( { field, index, dragListeners, dragAttributes } ) {
    const { editingFieldId, fieldSettings, formFields } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            editingFieldId: store.getEditingFieldId(),
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
        };
    }, [] );

    const { openFieldSettings, cloneField, removeField } = useDispatch( STORE_NAME );

    const isEditing = parseInt( editingFieldId ) === parseInt( field.id );
    const failedValidation = isFailedToValidate( field.template, fieldSettings, getFieldValidators() );

    const data = window.wpuf_form_builder || {};
    const i18n = data.i18n || {};
    const singleObjects = data.wpuf_single_objects || [];

    const handleEdit = useCallback( () => {
        openFieldSettings( field.id );
    }, [ field.id, openFieldSettings ] );

    const handleClone = useCallback( () => {
        if ( isFieldSingleInstance( field.template, singleObjects ) && containsField( formFields, field.template ) ) {
            if ( typeof window.Swal !== 'undefined' ) {
                window.Swal.fire( {
                    title: 'Oops...',
                    text: __( 'You already have this field in the form', 'wp-user-frontend' ),
                } );
            }
            return;
        }

        cloneField( field.id, getRandomId() );
    }, [ field.id, field.template, formFields, singleObjects, cloneField ] );

    const handleDelete = useCallback( () => {
        if ( typeof window.Swal === 'undefined' ) {
            removeField( index );
            return;
        }

        const iconDelete = ( window.wpuf_admin_script || {} ).asset_url + '/images/delete-icon-rounded.svg';

        window.Swal.fire( {
            title: i18n.delete_field_warn_title || __( 'Are you sure?', 'wp-user-frontend' ),
            html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' + ( i18n.delete_field_warn_msg || __( 'Are you sure you want to delete this field?', 'wp-user-frontend' ) ) + '</span>',
            iconHtml: '<img src="' + iconDelete + '" alt="delete">',
            showCancelButton: true,
            confirmButtonText: i18n.yes_delete_it || __( 'Yes, delete it!', 'wp-user-frontend' ),
            cancelButtonText: i18n.no_cancel_it || __( 'No, cancel it!', 'wp-user-frontend' ),
            cancelButtonColor: '#fff',
            confirmButtonColor: '#EF4444',
            reverseButtons: true,
        } ).then( ( result ) => {
            if ( result.isConfirmed ) {
                removeField( index );
            }
        } );
    }, [ index, i18n, removeField ] );

    return (
        <div
            className={ `field-buttons group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-primary wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-around ${ isEditing ? 'wpuf-opacity-100' : 'wpuf-opacity-0' }` }
        >
            <div className="wpuf-flex wpuf-justify-around wpuf-text-green-200">
                { ! failedValidation ? (
                    <>
                        <span className="!wpuf-mt-2.5" { ...dragListeners } { ...dragAttributes }>
                            <i className="fa fa-arrows move wpuf-pr-2 wpuf-rounded-l-md hover:!wpuf-cursor-move wpuf-border-r wpuf-border-green-200 wpuf-text-[17px]" />
                        </span>
                        <span className={ ACTION_CLASSES } onClick={ handleEdit } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleEdit() }>
                            <svg className="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.43306 13.9163L6.69485 10.7618C6.89603 10.2589 7.19728 9.802 7.58033 9.41896L14.4995 2.50023C15.3279 1.6718 16.6711 1.6718 17.4995 2.50023C18.3279 3.32865 18.3279 4.6718 17.4995 5.50023L10.5803 12.419C10.1973 12.802 9.74042 13.1033 9.23746 13.3044L6.08299 14.5662C5.67484 14.7295 5.2698 14.3244 5.43306 13.9163Z" fill="#A7F3D0" />
                                <path d="M3.5 5.74951C3.5 5.05916 4.05964 4.49951 4.75 4.49951H10C10.4142 4.49951 10.75 4.16373 10.75 3.74951C10.75 3.3353 10.4142 2.99951 10 2.99951H4.75C3.23122 2.99951 2 4.23073 2 5.74951V15.2495C2 16.7683 3.23122 17.9995 4.75 17.9995H14.25C15.7688 17.9995 17 16.7683 17 15.2495V9.99951C17 9.5853 16.6642 9.24951 16.25 9.24951C15.8358 9.24951 15.5 9.5853 15.5 9.99951V15.2495C15.5 15.9399 14.9404 16.4995 14.25 16.4995H4.75C4.05964 16.4995 3.5 15.9399 3.5 15.2495V5.74951Z" fill="#A7F3D0" />
                            </svg>
                            { ' Edit' }
                        </span>
                        <span className={ ACTION_CLASSES } onClick={ handleClone } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleClone() }>
                            <svg className="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.75 6.875V5C13.75 3.96447 12.9105 3.125 11.875 3.125H5C3.96447 3.125 3.125 3.96447 3.125 5V11.875C3.125 12.9105 3.96447 13.75 5 13.75H6.875M13.75 6.875H15C16.0355 6.875 16.875 7.71447 16.875 8.75V15C16.875 16.0355 16.0355 16.875 15 16.875H8.75C7.71447 16.875 6.875 16.0355 6.875 15V13.75M13.75 6.875H8.75C7.71447 6.875 6.875 7.71447 6.875 8.75V13.75" stroke="#A7F3D0" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            { ' Copy' }
                        </span>
                    </>
                ) : (
                    <>
                        <span className={ ACTION_CLASSES }>
                            <i className="fa fa-arrows control-button-disabled wpuf--ml-1 wpuf-rounded-l-md" />
                        </span>
                        <span className={ ACTION_CLASSES }>
                            <i className="fa fa-pencil control-button-disabled wpuf--ml-1" />
                            { ' Edit' }
                        </span>
                        <span className={ ACTION_CLASSES }>
                            <i className="fa fa-clone control-button-disabled wpuf--ml-1" />
                            { ' Copy' }
                        </span>
                    </>
                ) }
                <span className={ ACTION_CLASSES } onClick={ handleDelete } role="button" tabIndex={ 0 } onKeyDown={ ( e ) => e.key === 'Enter' && handleDelete() }>
                    <svg className="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.2837 7.5L11.9952 15M8.00481 15L7.71635 7.5M16.023 4.82547C16.308 4.86851 16.592 4.91456 16.875 4.96358M16.023 4.82547L15.1332 16.3938C15.058 17.3707 14.2434 18.125 13.2637 18.125H6.73631C5.75655 18.125 4.94198 17.3707 4.86683 16.3938L3.97696 4.82547M16.023 4.82547C15.0677 4.6812 14.1013 4.57071 13.125 4.49527M3.125 4.96358C3.40798 4.91456 3.69198 4.86851 3.97696 4.82547M3.97696 4.82547C4.93231 4.6812 5.89874 4.57071 6.875 4.49527M13.125 4.49527V3.73182C13.125 2.74902 12.3661 1.92853 11.3838 1.8971C10.9244 1.8824 10.463 1.875 10 1.875C9.53696 1.875 9.07565 1.8824 8.61618 1.8971C7.63388 1.92853 6.875 2.74902 6.875 3.73182V4.49527M13.125 4.49527C12.0938 4.41558 11.0516 4.375 10 4.375C8.94836 4.375 7.9062 4.41558 6.875 4.49527" stroke="#A7F3D0" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    { ' Remove' }
                </span>
            </div>
        </div>
    );
}
