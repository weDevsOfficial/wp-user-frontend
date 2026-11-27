/**
 * Add a palette field where it was dropped (design.md D16), with develop's
 * checks and refusal messages: stage (useAddField: single instance, custom
 * field tooltip), column cell (column / hidden / step fields refused, single
 * instance), repeat (allowed types only).
 *
 * @since WPUF_SINCE
 */
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import useAddField from '../../hooks/useAddField';
import { createField, isFieldSingleInstance, containsField } from '../../utils/fieldUtils';
import { canDrop, parseContainer } from '../../utils/dndTree';
import { showOops } from '../../common/BuilderDialogs';
import { builderData } from '../../utils/boot';

// Develop's "Oops..." alert, kept importable from here.
export { showOops };

/**
 * @return {Function} `( template, { container, index } ) => void`
 */
export default function usePaletteDrop() {
    const { fieldSettings, formFields } = useSelect( ( select ) => {
        const store = select( STORE_NAME );

        return {
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
        };
    }, [] );

    const { addColumnField, addRepeatField } = useDispatch( STORE_NAME );
    const addToStage = useAddField();

    return useCallback( ( template, target ) => {
        const list = parseContainer( target.container );

        if ( 'top' === list.type ) {
            addToStage( template, target.index );
            return;
        }

        if ( ! canDrop( { kind: 'palette', template }, target.container ) ) {
            showOops( 'column' === list.type
                ? __( 'You cannot add this field as inner column field', 'wp-user-frontend' )
                : __( 'This field type is not supported in repeat field', 'wp-user-frontend' ) );
            return;
        }

        if ( 'column' === list.type ) {
            const singleObjects = builderData().wpuf_single_objects || [];

            if ( isFieldSingleInstance( template, singleObjects ) && containsField( formFields, template ) ) {
                showOops( __( 'You already have this field in the form', 'wp-user-frontend' ) );
                return;
            }
        }

        const field = createField( template, fieldSettings, formFields, { innerField: true } );

        if ( ! field ) {
            return;
        }

        if ( 'column' === list.type ) {
            addColumnField( parseInt( list.fieldId, 10 ), list.column, target.index, field );
        } else {
            addRepeatField( parseInt( list.fieldId, 10 ), target.index, field );
        }
    }, [ fieldSettings, formFields, addColumnField, addRepeatField, addToStage ] );
}
