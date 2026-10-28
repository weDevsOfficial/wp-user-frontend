import { __ } from '@wordpress/i18n';
import { HelpTextIcon } from '@wpuf/components';

/**
 * Label row of a settings field: label, help tooltip, optional "Learn More"
 * link, then `children`.
 *
 * @param {Object} props
 * @param {Object} props.field     Field (label, help_text, link).
 * @param {string} [props.htmlFor] Control id.
 */
export default function SettingLabel( { field, htmlFor, children } ) {
    return (
        <div className="flex items-center">
            { field.label && (
                <label htmlFor={ htmlFor } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                    { field.label }
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

/**
 * `long_help` HTML under a control (server-side kses).
 *
 * @param {Object} props
 * @param {string} [props.html] HTML.
 */
export function LongHelp( { html } ) {
    if ( ! html ) {
        return null;
    }

    // eslint-disable-next-line react/no-danger
    return <div className="text-sm mt-4 wpuf-long-help" dangerouslySetInnerHTML={ { __html: html } } />;
}
