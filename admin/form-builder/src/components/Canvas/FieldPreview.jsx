import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { getFieldPreview } from '../../extensions/registry';
import { hasHiddenCssClass } from '../../utils/canvasHelpers';

export default function FieldPreview( { field } ) {
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

    const data = window.wpuf_form_builder || {};
    const proLink = data.pro_link || '';

    // Try to get a registered preview component
    const PreviewComponent = getFieldPreview( field.template );

    return (
        <div
            className={ `wpuf-flex wpuf-justify-between wpuf-p-6 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-primary group-hover:wpuf-cursor-pointer !wpuf-pb-3 ${ isEditing ? 'wpuf-bg-green-50 wpuf-border-primary' : 'wpuf-border-transparent' }` }
        >
            { ! ( isFullWidth || isProPreview ) && (
                <div className="wpuf-w-1/4 wpuf-flex wpuf-items-center">
                    { field.show_icon === 'yes' && field.field_icon && field.icon_position === 'left_label' && (
                        <span className="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-mr-1">
                            { field.field_icon.indexOf( 'http' ) === 0 || field.field_icon.indexOf( '/' ) === 0
                                ? <img src={ field.field_icon } alt="" className="wpuf-field-icon wpuf-field-icon-img" />
                                : <i className={ `${ field.field_icon } wpuf-field-icon` } />
                            }
                        </span>
                    ) }
                    { ! isInvisible && (
                        <label
                            htmlFor={ `wpuf-${ field.name || 'cls' }` }
                            className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900"
                        >
                            { field.label }
                            { field.required === 'yes' && <span className="required"> *</span> }
                            { hasHiddenCssClass( field.css ) && (
                                <span
                                    className="wpuf-inline-flex wpuf-items-center wpuf-ml-2 wpuf-px-2 wpuf-py-0.5 wpuf-rounded wpuf-text-xs wpuf-font-medium wpuf-bg-yellow-100 wpuf-text-yellow-800 wpuf-border wpuf-border-yellow-300"
                                    title={ __( 'This field will be hidden on the frontend due to CSS class', 'wp-user-frontend' ) }
                                >
                                    <svg className="wpuf-w-3 wpuf-h-3 wpuf-mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                    </svg>
                                    { __( 'Hidden on frontend', 'wp-user-frontend' ) }
                                </span>
                            ) }
                        </label>
                    ) }
                    { field.show_icon === 'yes' && field.field_icon && field.icon_position === 'right_label' && (
                        <span className="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                            { field.field_icon.indexOf( 'http' ) === 0 || field.field_icon.indexOf( '/' ) === 0
                                ? <img src={ field.field_icon } alt="" className="wpuf-field-icon wpuf-field-icon-img" />
                                : <i className={ `${ field.field_icon } wpuf-field-icon` } />
                            }
                        </span>
                    ) }
                </div>
            ) }
            <div
                className={ `wpuf-relative ${ ( isFullWidth || isProPreview ) ? 'wpuf-w-full' : 'wpuf-w-3/4' }` }
            >
                <div className="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-z-10" />
                { PreviewComponent && ! isProPreview ? (
                    <PreviewComponent field={ field } />
                ) : null }
                { isProPreview && (
                    <div className="stage-pro-alert wpuf-text-center">
                        <label className="wpuf-pro-text-alert">
                            <a href={ proLink } target="_blank" rel="noopener noreferrer" className="wpuf-text-gray-700 wpuf-text-base">
                                <strong>{ config ? config.title : field.template }</strong>
                                { ' ' + __( 'is available in Pro Version', 'wp-user-frontend' ) }
                            </a>
                        </label>
                    </div>
                ) }
            </div>
        </div>
    );
}
