import { useState, useRef, useEffect } from '@wordpress/element';
import SettingHelpText from './SettingHelpText';

/**
 * Custom dropdown select for field settings.
 * Replaces Vue field-select component with the custom dropdown UI.
 */
export default function SelectInput( { optionField, value, onChange } ) {
    const [ showOptions, setShowOptions ] = useState( false );
    const wrapperRef = useRef( null );
    const options = optionField.options || {};

    const selectedLabel = options[ value ] || '';

    // Close dropdown on outside click
    useEffect( () => {
        if ( ! showOptions ) {
            return;
        }

        function handleClickOutside( e ) {
            if ( wrapperRef.current && ! wrapperRef.current.contains( e.target ) ) {
                setShowOptions( false );
            }
        }

        document.addEventListener( 'mousedown', handleClickOutside );

        return () => document.removeEventListener( 'mousedown', handleClickOutside );
    }, [ showOptions ] );

    return (
        <div className="panel-field-opt panel-field-opt-select">
            <div className="wpuf-flex">
                { optionField.title && (
                    <label className="!wpuf-mb-0">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>

            <div className="option-fields-section wpuf-relative" ref={ wrapperRef }>
                <div
                    className="wpuf-my-4 wpuf-w-full wpuf-min-w-full !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md focus:!wpuf-ring-transparent hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base"
                    onClick={ () => setShowOptions( ! showOptions ) }
                    role="button"
                    tabIndex={ 0 }
                    onKeyDown={ ( e ) => e.key === 'Enter' && setShowOptions( ! showOptions ) }
                >
                    { selectedLabel }
                    <i className={ `fa ${ showOptions ? 'fa-angle-up' : 'fa-angle-down' } wpuf-text-base` } />
                </div>

                { showOptions && (
                    <div className="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-40 wpuf--mt-4">
                        <ul>
                            { Object.entries( options ).map( ( [ key, label ] ) => (
                                <li
                                    key={ key }
                                    className="wpuf-text-sm wpuf-color-gray-900 wpuf-py-2 wpuf-px-4 hover:wpuf-cursor-pointer hover:wpuf-bg-gray-100"
                                    onClick={ () => {
                                        onChange( key );
                                        setShowOptions( false );
                                    } }
                                    role="option"
                                    aria-selected={ value === key }
                                >
                                    { label }
                                </li>
                            ) ) }
                        </ul>
                    </div>
                ) }
            </div>
        </div>
    );
}
