import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../../store';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { getFieldValidators } from '../../extensions/registry';

/**
 * Get the icon URL for a field template.
 */
function getIconUrl( template, fieldConfig, isProActive ) {
    if ( ! fieldConfig || ! fieldConfig.icon ) {
        return '';
    }

    const data = window.wpuf_form_builder || {};

    if ( isProActive && fieldConfig.pro_feature ) {
        return ( data.pro_asset_url || '' ) + '/images/' + fieldConfig.icon + '.svg';
    }

    return ( data.asset_url || '' ) + '/images/' + fieldConfig.icon + '.svg';
}

export default function FieldItem( { template, onAdd, onProAlert, onValidationAlert } ) {
    const { fieldConfig, isProActive } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        const settings = store.getFieldSettings();
        return {
            fieldConfig: settings[ template ] || {},
            isProActive: store.getIsProActive(),
        };
    }, [ template ] );

    const isProPreview = ! isProActive && fieldConfig.pro_feature;
    const failedValidation = isFailedToValidate( template, { [ template ]: fieldConfig }, getFieldValidators() );
    const iconUrl = getIconUrl( template, fieldConfig, isProActive );
    const title = fieldConfig.title || template;

    if ( isProPreview ) {
        return (
            <div
                data-form-field={ template }
                data-source="panel"
                onClick={ () => onProAlert( template ) }
                className="wpuf-relative wpuf-group/pro-field"
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && onProAlert( template ) }
            >
                <div className="wpuf-opacity-50 wpuf-field-button wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-p-4 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer">
                    { iconUrl && (
                        <div className="wpuf-shrink-0 wpuf-mr-2 wpuf-text-gray-400">
                            <img src={ iconUrl } alt="" />
                        </div>
                    ) }
                    <div className="wpuf-min-w-0 wpuf-flex-1">
                        <p className="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">
                            { title }
                        </p>
                    </div>
                </div>
                <div className="wpuf-absolute wpuf-top-4 wpuf-right-4 wpuf-opacity-0 group-hover/pro-field:wpuf-opacity-100 wpuf-transition-all">
                    <img src={ `${ ( window.wpuf_form_builder || {} ).asset_url || '' }/images/pro-badge.svg` } alt="" />
                </div>
            </div>
        );
    }

    if ( failedValidation ) {
        return (
            <div
                data-form-field={ template }
                data-source="panel"
                onClick={ () => onValidationAlert( template ) }
                className="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-px-3 wpuf-py-4 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer"
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && onValidationAlert( template ) }
            >
                { iconUrl && (
                    <div className="wpuf-shrink-0 wpuf-mr-2">
                        <img src={ iconUrl } alt="" />
                    </div>
                ) }
                <div className="wpuf-min-w-0 wpuf-flex-1">
                    <p className="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">
                        { title }
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div
            data-form-field={ template }
            data-source="panel"
            data-label={ title }
            draggable="true"
            onDragStart={ ( e ) => {
                e.dataTransfer.setData( 'wpuf/field-template', template );
                e.dataTransfer.effectAllowed = 'copy';
            } }
            onClick={ () => onAdd( template ) }
            className="wpuf-field-button wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow wpuf-px-3 wpuf-py-4 hover:wpuf-cursor-pointer hover:wpuf-border-primary"
            role="button"
            tabIndex={ 0 }
            onKeyDown={ ( e ) => e.key === 'Enter' && onAdd( template ) }
        >
            { iconUrl && (
                <div className="wpuf-shrink-0 wpuf-mr-2">
                    <img src={ iconUrl } alt="" />
                </div>
            ) }
            <div className="wpuf-min-w-0 wpuf-flex-1">
                <p className="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">
                    { title }
                </p>
            </div>
        </div>
    );
}
