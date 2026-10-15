import { useDispatch } from '@wordpress/data';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { STORE_NAME } from '../../store';

export default function FieldGroup( { section, index, children } ) {
    const { togglePanelSection } = useDispatch( STORE_NAME );

    if ( ! section.fields || section.fields.length === 0 ) {
        return null;
    }

    return (
        <div className="panel-form-field-group mb-5">
            <h3
                className={ `flex items-center justify-between hover:cursor-pointer text-xs! uppercase tracking-wide m-0 font-semibold! hover:text-gray-900! ${ section.show ? 'text-gray-700' : 'text-gray-500' }` }
                onClick={ () => togglePanelSection( index ) }
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && togglePanelSection( index ) }
            >
                { section.title }
                { section.show
                    ? <ChevronDown className="size-4 shrink-0 text-gray-500" aria-hidden="true" />
                    : <ChevronRight className="size-4 shrink-0 text-gray-400" aria-hidden="true" /> }
            </h3>
            { section.show && (
                <div
                    id={ `panel-form-field-buttons-${ section.id }` }
                    className="panel-form-field-buttons grid grid-cols-2 gap-2 mt-2.5"
                >
                    { children }
                </div>
            ) }
        </div>
    );
}
