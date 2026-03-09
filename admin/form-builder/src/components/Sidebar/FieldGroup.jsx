import { useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../../store';

export default function FieldGroup( { section, index, children } ) {
    const { togglePanelSection } = useDispatch( STORE_NAME );

    if ( ! section.fields || section.fields.length === 0 ) {
        return null;
    }

    return (
        <div className="panel-form-field-group wpuf-mb-8">
            <h3
                className={ `wpuf-flex wpuf-justify-between hover:wpuf-cursor-pointer wpuf-text-base wpuf-m-0 !wpuf-font-medium ${ section.show ? 'wpuf-text-primary' : 'wpuf-text-gray-500' }` }
                onClick={ () => togglePanelSection( index ) }
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && togglePanelSection( index ) }
            >
                { section.title }
                <i className={ `wpuf-text-2xl ${ section.show ? 'fa fa-angle-down wpuf-text-primary' : 'fa fa-angle-right wpuf-text-gray-500' }` } />
            </h3>
            { section.show && (
                <div
                    id={ `panel-form-field-buttons-${ section.id }` }
                    className="panel-form-field-buttons wpuf-grid wpuf-grid-cols-1 wpuf-gap-3 sm:wpuf-grid-cols-2 wpuf-mt-3"
                >
                    { children }
                </div>
            ) }
        </div>
    );
}
