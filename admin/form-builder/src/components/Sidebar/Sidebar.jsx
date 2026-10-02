import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import FieldPanel from './FieldPanel';
import FieldOptionsPanel from '../FieldSettings/FieldOptionsPanel';

export default function Sidebar() {
    const currentPanel = useSelect( ( select ) => {
        return select( STORE_NAME ).getCurrentPanel();
    }, [] );

    const { setCurrentPanel } = useDispatch( STORE_NAME );

    const isAddFields = currentPanel === 'form-fields-v4-1';
    const isFieldOptions = currentPanel === 'field-options';

    return (
        <div className="wpuf-p-6 wpuf-pb-0 wpuf-mb-8">
            <div
                role="tablist"
                className="wpuf-tabs wpuf-tabs-boxed wpuf-text-gray-500 wpuf-rounded-xl wpuf-px-3 wpuf-py-2 wpuf-text-base wpuf-font-medium wpuf-bg-gray-100"
            >
                <a
                    role="tab"
                    className={ `wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-transition-all ${ isAddFields ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : '' }` }
                    href="#add-fields"
                    onClick={ ( e ) => {
                        e.preventDefault();
                        setCurrentPanel( 'form-fields-v4-1' );
                    } }
                >
                    { __( 'Add Fields', 'wp-user-frontend' ) }
                </a>
                <a
                    role="tab"
                    className={ `wpuf-tab wpuf-h-10 hover:wpuf-bg-white hover:wpuf-text-gray-800 hover:wpuf-shadow-sm focus:wpuf-shadow-none wpuf-ml-1 wpuf-transition-all ${ isFieldOptions ? 'wpuf-bg-white wpuf-text-gray-800 wpuf-shadow-sm' : 'wpuf-text-gray-500' }` }
                    href="#field-options"
                    onClick={ ( e ) => {
                        e.preventDefault();
                        setCurrentPanel( 'field-options' );
                    } }
                >
                    { __( 'Field Options', 'wp-user-frontend' ) }
                </a>
            </div>
            <section>
                <div className="wpuf-form-builder-panel wpuf-mt-6 wpuf-mb-32">
                    { isAddFields && <FieldPanel /> }
                    { isFieldOptions && <FieldOptionsPanel /> }
                </div>
            </section>
        </div>
    );
}
