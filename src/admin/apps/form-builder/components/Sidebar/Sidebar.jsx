import { __ } from '@wordpress/i18n';
import FieldPanel from './FieldPanel';
import FieldOptionsPanel from '../FieldSettings/FieldOptionsPanel';

/**
 * One builder side column (FlyForms layout): the field palette on the left,
 * the selected field's options on the right, both always visible.
 *
 * @param {Object} props
 * @param {string} props.panel `fields` or `options`
 */
export default function Sidebar( { panel = 'fields' } ) {
    const isFields = 'fields' === panel;

    return (
        <div className="p-4">
            <h2 className="m-0! mb-4! text-xs! font-semibold uppercase tracking-wide text-gray-500">
                { isFields ? __( 'Add Fields', 'wp-user-frontend' ) : __( 'Field Options', 'wp-user-frontend' ) }
            </h2>
            <section>
                <div className={ `wpuf-form-builder-panel mb-6 ${ isFields ? 'wpuf-form-builder-panel-fields' : 'wpuf-form-builder-panel-options' }` }>
                    { isFields ? <FieldPanel /> : <FieldOptionsPanel /> }
                </div>
            </section>
        </div>
    );
}
