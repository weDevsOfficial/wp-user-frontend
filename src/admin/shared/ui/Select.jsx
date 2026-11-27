/**
 * Single select. Shows the stored value when it is an option, else the
 * placeholder ("- Select -" by default); the stored value is never rewritten
 * by rendering. onChange gets the option value (string).
 */
import { Select as PuiSelect, SelectContent, SelectItem, SelectTrigger, SelectValue, SmartSelect, cn } from '@wedevs/plugin-ui';
import { __ } from '@wordpress/i18n';

import { normalizeOptions, selectShownValue } from './values';

/**
 * @param {Object}       props
 * @param {*}            [props.value]       Stored value.
 * @param {Array|Object} props.options       [{ value, label, disabled }] or { value: label }.
 * @param {Function}     [props.onChange]    ( value: string ) => void
 * @param {string}       [props.placeholder] Shown when no option matches.
 * @param {boolean}      [props.disabled]    Disabled.
 * @param {string}       [props.id]          Trigger id (for <label for>).
 * @param {boolean}      [props.searchable]  Search box above the options (long lists: pages, currencies).
 */
export default function Select( { value, options, onChange, placeholder, disabled, id, className, searchable = false, 'aria-label': ariaLabel, ...props } ) {
    const items = normalizeOptions( options );
    const shown = selectShownValue( value, items );
    const labelOf = ( val ) => items.find( ( item ) => item.value === val )?.label ?? '';

    if ( searchable ) {
        return (
            <SmartSelect
                options={ items }
                value={ shown ?? '' }
                // SmartSelect clears the value when the selected option is picked
                // again; a select keeps its value (unless '' is an option).
                onValueChange={ ( next ) => {
                    if ( '' !== next || items.some( ( item ) => '' === item.value ) ) {
                        onChange?.( next );
                    }
                } }
                disabled={ disabled }
                placeholder={ placeholder ?? __( '- Select -', 'wp-user-frontend' ) }
                searchPlaceholder={ __( 'Search', 'wp-user-frontend' ) }
                emptyMessage={ __( 'No matching options', 'wp-user-frontend' ) }
                className={ cn( 'w-full h-9 ps-3 pe-3 text-sm font-normal text-gray-900 bg-white border border-gray-300 rounded-md shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30', className ) }
            />
        );
    }

    return (
        <PuiSelect value={ shown } onValueChange={ ( next ) => onChange?.( next ) } disabled={ disabled } { ...props }>
            <SelectTrigger
                id={ id }
                aria-label={ ariaLabel }
                className={ cn( 'w-full h-9 data-[size=default]:h-9 ps-3 pe-3 text-sm text-gray-900 bg-white border border-gray-300 rounded-md shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30 data-[placeholder]:text-gray-400', className ) }
            >
                <SelectValue placeholder={ placeholder ?? __( '- Select -', 'wp-user-frontend' ) }>
                    { ( val ) => ( null === val || undefined === val ? placeholder ?? __( '- Select -', 'wp-user-frontend' ) : labelOf( val ) ) }
                </SelectValue>
            </SelectTrigger>
            { /* Drop below the trigger (flip up only without room) instead of laying the
               list over it around the selected item: that layout pushed the first options
               above the viewport top, under the fixed WordPress admin bar. The height cap
               keeps a list that flips up clear of the bar too (it scrolls). */ }
            <SelectContent alignItemWithTrigger={ false } className="max-h-[min(var(--available-height),18rem)]">
                { items.map( ( item ) => (
                    // data-value: the stored value, for tests and scripts that pick by value.
                    <SelectItem key={ item.value } value={ item.value } disabled={ item.disabled } data-value={ item.value }>
                        { item.label }
                    </SelectItem>
                ) ) }
            </SelectContent>
        </PuiSelect>
    );
}
