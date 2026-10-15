import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { getFieldPreview } from '../../extensions/registry';
import { hasHiddenCssClass } from '../../utils/canvasHelpers';
import { builderData } from '../../utils/boot';

/**
 * Previews keep their inputs uncontrolled (defaultValue), so remount one when a
 * shown default changes; develop's Vue bindings updated the stage live.
 *
 * @param {Object} field Field
 * @return {string} Key
 */
function previewKey( field ) {
    return JSON.stringify( [ field.default, field.country_list, field.address?.country_select?.value ] );
}

/**
 * Inner (column / repeat) fields use develop's compact row: no icons or hidden
 * badge, small label, named hover group.
 */
const INNER_ROW = {
    column: 'flex flex-col md:flex-row gap-2 p-3 rounded-lg border border-dashed border-transparent group-hover/column-inner:border-primary/50',
    repeat: 'flex flex-col md:flex-row gap-2 p-3 rounded-lg border border-dashed border-transparent group-hover/repeat-inner:border-primary/50',
};

export default function FieldPreview( { field, variant = 'top' } ) {
    const { fieldSettings, isProActive, editingFieldId } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            fieldSettings: store.getFieldSettings(),
            isProActive: store.getIsProActive(),
            editingFieldId: store.getEditingFieldId(),
        };
    }, [] );

    const config = fieldSettings[ field.template ];
    const isFullWidth = config && config.is_full_width;
    const isProPreview = ! isProActive && config && config.pro_feature;
    const isInvisible = field.recaptcha_type && field.recaptcha_type === 'invisible_recaptcha';
    const isEditing = parseInt( editingFieldId ) === parseInt( field.id );

    const data = builderData();
    const proLink = data.pro_link || '';

    // Try to get a registered preview component
    const PreviewComponent = getFieldPreview( field.template );

    const body = (
        <>
            { PreviewComponent && ! isProPreview ? (
                <PreviewComponent key={ previewKey( field ) } field={ field } />
            ) : null }
            { isProPreview && (
                <div className="stage-pro-alert text-center">
                    <label className="wpuf-pro-text-alert">
                        <a href={ proLink } target="_blank" rel="noopener noreferrer" className="text-gray-700 text-base">
                            <strong>{ config ? config.title : field.template }</strong>
                            { ' ' + __( 'is available in Pro Version', 'wp-user-frontend' ) }
                        </a>
                    </label>
                </div>
            ) }
        </>
    );

    if ( INNER_ROW[ variant ] ) {
        return (
            <div className={ INNER_ROW[ variant ] }>
                { ! ( isFullWidth || isProPreview ) && (
                    <div className={ 'repeat' === variant ? 'w-1/4 flex items-center' : undefined }>
                        { ! isInvisible && (
                            <label htmlFor={ `wpuf-${ field.name || 'cls' }` } className="block text-sm">
                                { field.label }
                                { field.required === 'yes' && <span className="required"> *</span> }
                            </label>
                        ) }
                    </div>
                ) }
                <div className={ `relative min-w-0 ${ ( isFullWidth || isProPreview ) ? 'w-full' : 'w-full md:w-3/4' }` }>
                    <div className="absolute w-full h-full z-10" />
                    <div className="relative">{ body }</div>
                </div>
            </div>
        );
    }

    return (
        <div
            className={ `flex justify-between px-5 py-4 rounded-lg border group-hover:cursor-pointer ${ isEditing ? 'border-solid border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-dashed border-transparent group-hover:border-primary/60' }` }
        >
            { ! ( isFullWidth || isProPreview ) && (
                <div className="w-1/4 flex items-center">
                    { field.show_icon === 'yes' && field.field_icon && field.icon_position === 'left_label' && (
                        <span className="wpuf-field-label-icon inline-flex items-center mr-1">
                            { field.field_icon.indexOf( 'http' ) === 0 || field.field_icon.indexOf( '/' ) === 0
                                ? <img src={ field.field_icon } alt="" className="wpuf-field-icon wpuf-field-icon-img" />
                                : <i className={ `${ field.field_icon } wpuf-field-icon` } />
                            }
                        </span>
                    ) }
                    { ! isInvisible && (
                        <label
                            htmlFor={ `wpuf-${ field.name || 'cls' }` }
                            className="block text-sm font-medium leading-6 text-gray-900"
                        >
                            { field.label }
                            { field.required === 'yes' && <span className="required"> *</span> }
                            { hasHiddenCssClass( field.css ) && (
                                <span
                                    className="inline-flex items-center ml-2 px-2 py-0.5 rounded-sm text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-300"
                                    title={ __( 'This field will be hidden on the frontend due to CSS class', 'wp-user-frontend' ) }
                                >
                                    <svg className="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                    </svg>
                                    { __( 'Hidden on frontend', 'wp-user-frontend' ) }
                                </span>
                            ) }
                        </label>
                    ) }
                    { field.show_icon === 'yes' && field.field_icon && field.icon_position === 'right_label' && (
                        <span className="wpuf-field-label-icon inline-flex items-center ml-2">
                            { field.field_icon.indexOf( 'http' ) === 0 || field.field_icon.indexOf( '/' ) === 0
                                ? <img src={ field.field_icon } alt="" className="wpuf-field-icon wpuf-field-icon-img" />
                                : <i className={ `${ field.field_icon } wpuf-field-icon` } />
                            }
                        </span>
                    ) }
                </div>
            ) }
            <div
                className={ `relative ${ ( isFullWidth || isProPreview ) ? 'w-full' : 'w-3/4' }` }
            >
                <div className="absolute w-full h-full z-10" />
                { body }
            </div>
        </div>
    );
}
