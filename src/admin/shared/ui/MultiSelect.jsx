/**
 * Multiple select with search; value and onChange use a list of strings
 * (falsy stored value -> []). "Select all" shows only when there are two or
 * more choices; a list that is only a placeholder option ("No subscription
 * packs available") shows that text as a disabled note: nothing to tick.
 */
import { SmartMultiSelect, cn } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';

import { multiSelectChoices, normalizeOptions, toList } from './values';

/**
 * @param {Object}       props
 * @param {*}            [props.value]       Stored list.
 * @param {Array|Object} props.options       Options.
 * @param {Function}     [props.onChange]    ( values: string[] ) => void
 * @param {string}       [props.placeholder] Shown when nothing is selected.
 */
export default function MultiSelect( { value, options, onChange, placeholder, className, ...props } ) {
    const { choices, empty } = multiSelectChoices( normalizeOptions( options ) );

    if ( empty ) {
        return (
            <div
                id={ props.id }
                aria-disabled="true"
                className={ cn( 'flex min-h-9 w-full items-center rounded-md border border-dashed border-gray-300 bg-gray-50 ps-3 pe-3 text-sm text-gray-500', className ) }
            >
                { empty }
            </div>
        );
    }

    return (
        <SmartMultiSelect
            options={ choices }
            hideSelectAll={ choices.length < 2 }
            value={ toList( value ) }
            onValueChange={ ( next ) => onChange?.( toList( next ) ) }
            placeholder={ placeholder ?? __( '- Select -', 'wp-user-frontend' ) }
            searchPlaceholder={ __( 'Search', 'wp-user-frontend' ) }
            // The Select trigger's look: 12px inset, gray-900 text, gray-400
            // placeholder, the same chevron and focus ring.
            className={ cn( 'min-h-9 ps-3 pe-3 text-sm text-gray-900 bg-white border border-gray-300 rounded-md shadow-none focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/30 [&_.text-muted-foreground]:text-gray-400 [&_.lucide-chevron-down]:opacity-100 [&_.lucide-chevron-down]:text-muted-foreground', className ) }
            { ...props }
        />
    );
}
