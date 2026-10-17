import { RawHTML } from '@wordpress/element';
import CopyValue from './CopyValue';

/**
 * Static HTML field: the legacy `html` type (informational blocks). Like the
 * legacy row it shows the label, then the `desc` markup (filtered with
 * `wp_kses_post` on the server).
 */
export default function HtmlField( { field } ) {
    const html = field.desc || '';

    // A read-only value the module printed as an <input> (Social Login redirect
    // URIs): the server hands it over as `copy_value` (wp_kses_post drops inputs).
    if ( field.copy_value ) {
        return (
            <div>
                { field.label && (
                    <p className="text-sm font-medium text-gray-700 m-0 mb-1">{ field.label }</p>
                ) }
                <CopyValue value={ field.copy_value } id={ field.name } />
            </div>
        );
    }

    if ( ! html && ! field.label ) {
        return null;
    }

    return (
        <div>
            { field.label && (
                <p className="text-sm font-medium text-gray-700 m-0 mb-1">{ field.label }</p>
            ) }
            { html && <RawHTML className="text-sm text-gray-600">{ html }</RawHTML> }
        </div>
    );
}
