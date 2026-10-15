import { useSelect } from '@wordpress/data';
import { useDraggable } from '@dnd-kit/core';
import { STORE_NAME } from '../../store';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { getFieldValidators } from '../../extensions/registry';
import { builderData } from '../../utils/boot';

/**
 * Get the icon URL for a field template.
 */
function getIconUrl( template, fieldConfig, isProActive ) {
    if ( ! fieldConfig || ! fieldConfig.icon ) {
        return '';
    }

    const data = builderData();

    if ( isProActive && fieldConfig.pro_feature ) {
        return ( data.pro_asset_url || '' ) + '/images/' + fieldConfig.icon + '.svg';
    }

    return ( data.asset_url || '' ) + '/images/' + fieldConfig.icon + '.svg';
}

/**
 * A palette button: click (or Enter) adds the field at the end; dragging it
 * (mouse, touch hold, or Space then arrows) drops it anywhere on the canvas
 * through the builder's DndContext (components/Dnd, design.md D16).
 */
function DraggableFieldItem( { template, title, iconUrl, onAdd } ) {
    const { attributes, listeners, setNodeRef } = useDraggable( {
        id: 'palette:' + template,
        data: { kind: 'palette', template, label: title },
    } );

    return (
        <div
            ref={ setNodeRef }
            data-form-field={ template }
            data-source="panel"
            data-label={ title }
            { ...attributes }
            { ...listeners }
            onClick={ () => onAdd( template ) }
            onKeyDown={ ( e ) => {
                listeners.onKeyDown( e );

                if ( 'Enter' === e.key ) {
                    onAdd( template );
                }
            } }
            className="wpuf-field-button group/tile relative flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-2 min-h-10 hover:cursor-pointer hover:border-primary hover:bg-primary/5 touch-manipulation wpuf-transition-all"
        >
            { iconUrl && (
                <div className="shrink-0 mr-2 flex items-center">
                    <img src={ iconUrl } alt="" draggable="false" className="size-4 group-hover/tile:icon-primary" />
                </div>
            ) }
            <div className="min-w-0 flex-1">
                <p className="text-[13px] leading-tight font-normal text-gray-700 group-hover/tile:text-primary! m-0 line-clamp-2 break-words">
                    { title }
                </p>
            </div>
        </div>
    );
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
                className="relative group/pro-field"
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && onProAlert( template ) }
            >
                <div className="opacity-50 wpuf-field-button flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-2 min-h-10 hover:border-gray-300 hover:cursor-pointer">
                    { iconUrl && (
                        <div className="shrink-0 mr-2 flex items-center text-gray-400">
                            <img src={ iconUrl } alt="" className="size-4" />
                        </div>
                    ) }
                    <div className="min-w-0 flex-1">
                        <p className="text-[13px] leading-tight font-normal text-gray-700 m-0 line-clamp-2 break-words">
                            { title }
                        </p>
                    </div>
                </div>
                { /* The badge sits on the tile's top-right corner (as the Bulk Add teaser's), so it
                     never covers the label; also shown on keyboard focus. */ }
                <div className="absolute -top-2 -right-1.5 z-10 pointer-events-none opacity-0 group-hover/pro-field:opacity-100 group-focus-visible/pro-field:opacity-100 wpuf-transition-all">
                    <img src={ `${ builderData().asset_url || '' }/images/pro-badge.svg` } alt="" className="h-4" />
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
                className="relative flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-2 min-h-10 hover:border-gray-300 hover:cursor-pointer"
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && onValidationAlert( template ) }
            >
                { iconUrl && (
                    <div className="shrink-0 mr-2 flex items-center">
                        <img src={ iconUrl } alt="" className="size-4" />
                    </div>
                ) }
                <div className="min-w-0 flex-1">
                    <p className="text-[13px] leading-tight font-normal text-gray-700 m-0 line-clamp-2 break-words">
                        { title }
                    </p>
                </div>
            </div>
        );
    }

    return <DraggableFieldItem template={ template } title={ title } iconUrl={ iconUrl } onAdd={ onAdd } />;
}
