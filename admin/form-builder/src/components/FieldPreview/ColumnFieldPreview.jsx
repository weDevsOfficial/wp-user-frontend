import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { getFieldPreview } from '../../extensions/registry';

export default function ColumnFieldPreview( { field } ) {
    const { fieldSettings, isProActive } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            fieldSettings: store.getFieldSettings(),
            isProActive: store.getIsProActive(),
        };
    }, [] );

    const columnsCount = parseInt( field.columns ) || 1;
    const columns = [];

    for ( let i = 1; i <= columnsCount; i++ ) {
        columns.push( 'column-' + i );
    }

    const innerFields = field.inner_fields || {};
    const data = window.wpuf_form_builder || {};
    const proLink = data.pro_link || '';

    const isFullWidth = ( template ) => {
        return fieldSettings[ template ] && fieldSettings[ template ].is_full_width;
    };

    const isProPreview = ( template ) => {
        return ! isProActive && fieldSettings[ template ] && fieldSettings[ template ].pro_feature;
    };

    const isInvisible = ( innerField ) => {
        return innerField.recaptcha_type && innerField.recaptcha_type === 'invisible_recaptcha';
    };

    return (
        <div
            className={ `has-columns-${ columnsCount } wpuf-field-columns wpuf-flex md:wpuf-flex-row wpuf-gap-4 wpuf-p-4 wpuf-w-full wpuf-justify-between wpuf-rounded-t-md !wpuf-border-t !wpuf-border-r !wpuf-border-l !wpuf-border-dashed !wpuf-border-transparent group-hover:!wpuf-border-green-400 group-hover:wpuf-cursor-pointer` }
        >
            { columns.map( ( column ) => (
                <div
                    key={ column }
                    style={ { paddingRight: ( field.column_space || 0 ) + 'px' } }
                    className="wpuf-flex-1 wpuf-min-w-0 wpuf-min-h-full wpuf-column-inner-fields"
                >
                    <div
                        data-column={ column }
                        className="wpuf-border wpuf-border-dashed wpuf-border-green-400 wpuf-bg-green-50 wpuf-shadow-sm wpuf-rounded-md wpuf-p-1"
                    >
                        <ul className="wpuf-column-fields-sortable-list wpuf-min-h-16 wpuf-list-none !wpuf-m-0 !wpuf-p-0">
                            { ( innerFields[ column ] || [] ).map( ( innerField, innerIndex ) => {
                                const InnerPreview = getFieldPreview( innerField.template );
                                const innerConfig = fieldSettings[ innerField.template ];
                                const innerIsFullWidth = isFullWidth( innerField.template );
                                const innerIsProPreview = isProPreview( innerField.template );

                                return (
                                    <li
                                        key={ innerField.id || innerIndex }
                                        className={ `!wpuf-m-0 !wpuf-p-0 wpuf-rounded-t-md ${ innerField.name || '' } ${ innerField.css || '' } form-field-${ innerField.template } ${ innerField.template === 'custom_hidden_field' ? 'hidden-field' : '' }` }
                                    >
                                        <div className="wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-2 wpuf-p-4 wpuf-border-transparent wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed wpuf-border-emerald-400">
                                            { ! ( innerIsFullWidth || innerIsProPreview ) && (
                                                <div>
                                                    { ! isInvisible( innerField ) && (
                                                        <label
                                                            htmlFor={ `wpuf-${ innerField.name || 'cls' }` }
                                                            className="wpuf-block wpuf-text-sm"
                                                        >
                                                            { innerField.label }
                                                            { innerField.required === 'yes' && (
                                                                <span className="required"> *</span>
                                                            ) }
                                                        </label>
                                                    ) }
                                                </div>
                                            ) }
                                            <div className={ `wpuf-relative wpuf-min-w-0 ${ ( innerIsFullWidth || innerIsProPreview ) ? 'wpuf-w-full' : 'wpuf-w-full md:wpuf-w-3/4' }` }>
                                                <div className="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-z-10" />
                                                <div className="wpuf-relative">
                                                    { InnerPreview && ! innerIsProPreview && (
                                                        <InnerPreview field={ innerField } />
                                                    ) }
                                                    { innerIsProPreview && (
                                                        <div className="stage-pro-alert wpuf-text-center">
                                                            <label className="wpuf-pro-text-alert">
                                                                <a href={ proLink } target="_blank" rel="noopener noreferrer" className="wpuf-text-gray-700 wpuf-text-base">
                                                                    <strong>{ innerConfig ? innerConfig.title : innerField.template }</strong>
                                                                    { ' ' + __( 'is available in Pro Version', 'wp-user-frontend' ) }
                                                                </a>
                                                            </label>
                                                        </div>
                                                    ) }
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                );
                            } ) }
                        </ul>
                    </div>
                </div>
            ) ) }
        </div>
    );
}
