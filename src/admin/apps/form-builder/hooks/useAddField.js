import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../store';
import { createField, isFieldSingleInstance, containsField } from '../utils/fieldUtils';
import { openCustomFieldTooltip, showOops } from '../common/BuilderDialogs';

// "Don't show again" for the custom field tooltip lasts for the page, shared by
// every place that adds a field (palette click, stage drop).
const tooltipState = { enabled: true };

/**
 * Open a field's settings and expand its "Advanced Options" section (the
 * tooltip's links), as develop's popup did.
 *
 * @param {number}   fieldId           Field id.
 * @param {Function} openFieldSettings Store action.
 */
function openAdvancedOptions( fieldId, openFieldSettings ) {
    setTimeout( () => {
        openFieldSettings( parseInt( fieldId, 10 ) );

        setTimeout( () => {
            const container = document.querySelector( 'div.wpuf-form-builder-field-options' );

            if ( ! container ) {
                return;
            }

            let targetSection = null;
            let targetH3 = null;

            container.querySelectorAll( '.option-fields-section' ).forEach( ( section ) => {
                const h3 = section.querySelector( 'h3' );

                if ( ! h3 ) {
                    return;
                }

                const clone = h3.cloneNode( true );
                clone.querySelectorAll( 'i' ).forEach( ( i ) => i.remove() );

                if ( 'advanced options' === clone.textContent.trim().toLowerCase().replace( /\.$/, '' ) ) {
                    targetH3 = h3;
                    targetSection = section;
                }
            } );

            if ( targetH3 ) {
                const contentDiv = targetSection.querySelector( '.option-field-section-fields' );

                if ( contentDiv && ! contentDiv.offsetParent ) {
                    targetH3.click();
                }

                setTimeout( () => {
                    targetSection.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
                }, 350 );
            }
        }, 650 );
    }, 250 );
}

/**
 * Add a palette field to the form: single-instance check, new field, insert
 * position and the custom field tooltip. Used by the palette (click) and the
 * stage (drop at a position).
 *
 * @return {Function} `( template, dropIndex? ) => void`
 */
export default function useAddField() {
    const { fieldSettings, formFields, indexToInsert, formType } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
            indexToInsert: store.getIndexToInsert(),
            formType: store.getFormType(),
        };
    }, [] );

    // Registration forms (the URL decided before; the admin app has one URL).
    const isProfilePage = 'wpuf_profile' === formType;

    const { addField, setIndexToInsert, openFieldSettings } = useDispatch( STORE_NAME );

    const data = window.wpuf_form_builder || {};
    const singleObjects = data.wpuf_single_objects || [];

    const showTooltip = useCallback( ( field ) => {
        openCustomFieldTooltip( field.id ).then( ( result ) => {
            if ( 'hide' === result ) {
                tooltipState.enabled = false;
            } else if ( result && result.advanced ) {
                openAdvancedOptions( result.advanced, openFieldSettings );
            }
        } );
    }, [ openFieldSettings ] );

    const handleAddField = useCallback( ( template, dropIndex ) => {
        // Single-instance check
        if ( isFieldSingleInstance( template, singleObjects ) && containsField( formFields, template ) ) {
            showOops( __( 'You already have this field in the form', 'wp-user-frontend' ) );
            return;
        }

        const field = createField( template, fieldSettings, formFields );

        if ( ! field ) {
            return;
        }

        // A click appends (or uses the pending insert index); a drop on the stage
        // passes its position, as develop's add_form_field did per event type.
        let insertAt = dropIndex;
        if ( undefined === insertAt ) {
            insertAt = indexToInsert === 0 ? formFields.length : indexToInsert;
        }
        addField( field, insertAt );
        setIndexToInsert( 0 );

        // Show custom field tooltip for meta fields (not on profile forms page)
        if ( ! isProfilePage && field.is_meta === 'yes' && tooltipState.enabled ) {
            showTooltip( field );
        }
    }, [ formFields, fieldSettings, indexToInsert, singleObjects, addField, setIndexToInsert, showTooltip, isProfilePage ] );

    return handleAddField;
}
