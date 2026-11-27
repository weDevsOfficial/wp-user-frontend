import { __ } from '@wordpress/i18n';
import { HelpTextIcon } from '@wpuf/components';

/**
 * Label row of a settings field (develop wpuf_render_settings_field): label,
 * help tooltip and the optional "Learn More" link, then `children` (e.g. a Pro
 * badge).
 *
 * @param {Object} props
 * @param {Object} props.field   Field definition (label, help_text, link, required).
 * @param {string} [props.htmlFor] Control id.
 */
export default function SettingLabel( { field, htmlFor, children } ) {
    return (
        <div className="flex items-center">
            { field.label && (
                <label htmlFor={ htmlFor } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                    { field.label }
                    { field.required && <span className="ml-0.5 text-red-600" aria-hidden="true">*</span> }
                </label>
            ) }
            { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            { field.link && (
                <a href={ field.link } target="_blank" rel="noopener noreferrer" title={ __( 'Learn More', 'wp-user-frontend' ) } className="focus:shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" className="size-5 ml-1 stroke-gray-50 hover:stroke-gray-200">
                        <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                        <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                    </svg>
                </a>
            ) }
            { children }
        </div>
    );
}
