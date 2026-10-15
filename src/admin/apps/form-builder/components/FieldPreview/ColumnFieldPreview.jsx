import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { getFieldPreview } from '../../extensions/registry';
import { builderData } from '../../utils/boot';

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
    const data = builderData();
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
            className={ `has-columns-${ columnsCount } wpuf-field-columns flex md:flex-row gap-4 p-4 w-full justify-between rounded-t-md border-t! border-r! border-l! border-dashed! border-transparent! group-hover:border-primary/50! group-hover:cursor-pointer` }
        >
            { columns.map( ( column ) => (
                <div
                    key={ column }
                    style={ { paddingRight: ( field.column_space || 0 ) + 'px' } }
                    className="flex-1 min-w-0 min-h-full wpuf-column-inner-fields"
                >
                    <div
                        data-column={ column }
                        className="border border-dashed border-gray-300 bg-gray-50/60 rounded-lg p-1"
                    >
                        <ul className="wpuf-column-fields-sortable-list min-h-16 list-none m-0! p-0!">
                            { ( innerFields[ column ] || [] ).map( ( innerField, innerIndex ) => {
                                const InnerPreview = getFieldPreview( innerField.template );
                                const innerConfig = fieldSettings[ innerField.template ];
                                const innerIsFullWidth = isFullWidth( innerField.template );
                                const innerIsProPreview = isProPreview( innerField.template );

                                return (
                                    <li
                                        key={ innerField.id || innerIndex }
                                        className={ `m-0! p-0! rounded-t-md ${ innerField.name || '' } ${ innerField.css || '' } form-field-${ innerField.template } ${ innerField.template === 'custom_hidden_field' ? 'hidden-field' : '' }` }
                                    >
                                        <div className="flex flex-col md:flex-row gap-2 p-3 rounded-lg border border-dashed border-transparent">
                                            { ! ( innerIsFullWidth || innerIsProPreview ) && (
                                                <div>
                                                    { ! isInvisible( innerField ) && (
                                                        <label
                                                            htmlFor={ `wpuf-${ innerField.name || 'cls' }` }
                                                            className="block text-sm"
                                                        >
                                                            { innerField.label }
                                                            { innerField.required === 'yes' && (
                                                                <span className="required"> *</span>
                                                            ) }
                                                        </label>
                                                    ) }
                                                </div>
                                            ) }
                                            <div className={ `relative min-w-0 ${ ( innerIsFullWidth || innerIsProPreview ) ? 'w-full' : 'w-full md:w-3/4' }` }>
                                                <div className="absolute w-full h-full z-10" />
                                                <div className="relative">
                                                    { InnerPreview && ! innerIsProPreview && (
                                                        <InnerPreview field={ innerField } />
                                                    ) }
                                                    { innerIsProPreview && (
                                                        <div className="stage-pro-alert text-center">
                                                            <label className="wpuf-pro-text-alert">
                                                                <a href={ proLink } target="_blank" rel="noopener noreferrer" className="text-gray-700 text-base">
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
