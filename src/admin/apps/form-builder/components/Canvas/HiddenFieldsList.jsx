import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import FieldActions from './FieldActions';

/**
 * Hidden fields are hidden on the stage, so they are listed under it with their
 * key and value and the field actions, as develop's builder stage did.
 */
export default function HiddenFieldsList() {
    const { formFields, editingFieldId } = useSelect( ( select ) => ( {
        formFields: select( STORE_NAME ).getFormFields(),
        editingFieldId: select( STORE_NAME ).getEditingFieldId(),
    } ), [] );

    const hidden = formFields
        .map( ( field, index ) => ( { field, index } ) )
        .filter( ( { field } ) => 'custom_hidden_field' === field.template );

    if ( ! hidden.length ) {
        return null;
    }

    return (
        <div className="border-t border-dashed border-gray-300 mt-2">
            <h4>{ __( 'Hidden Fields', 'wp-user-frontend' ) }</h4>
            <ul className="wpuf-form">
                { hidden.map( ( { field, index } ) => {
                    const isEditing = parseInt( editingFieldId ) === parseInt( field.id );

                    return (
                        <li key={ field.id } className="field-items group group/hidden-fields m-0! p-0! hover:cursor-pointer">
                            <div className={ `flex rounded-t-lg border-t border-r border-l border-dashed group-hover/hidden-fields:border-primaryHover group-hover/hidden-fields:bg-primary/5 ${ isEditing ? 'bg-primary/5 border-primaryHover' : 'border-transparent' }` }>
                                <div className="bg-primary m-4 py-2 px-4 w-full rounded-lg">
                                    <strong>{ __( 'key', 'wp-user-frontend' ) }</strong>: { field.name } |{ ' ' }
                                    <strong>{ __( 'value', 'wp-user-frontend' ) }</strong>: { field.meta_value }
                                </div>
                            </div>
                            <FieldActions field={ field } index={ index } showMove={ false } />
                        </li>
                    );
                } ) }
            </ul>
        </div>
    );
}
