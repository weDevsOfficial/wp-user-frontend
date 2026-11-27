import { useMemo } from '@wordpress/element';

/**
 * Whether a field meets a setting's dependencies. Every dependency must hold;
 * an array dependency accepts any of its values.
 * Replaces the Vue option-field.js mixin's `met_dependencies` computed.
 *
 * @param {Object}      field        The current editing field
 * @param {Object|null} dependencies Dependencies object from the setting definition
 *                                   e.g. { required: 'yes' } or { post_type: ['post', 'page'] }
 * @return {boolean} Whether all dependencies are met (true = show the setting)
 */
export function meetsDependencies( field, dependencies ) {
    if ( ! dependencies ) {
        return true;
    }

    const deps = Object.keys( dependencies );

    if ( ! deps.length ) {
        return true;
    }

    for ( let i = 0; i < deps.length; i++ ) {
        const requiredValue = dependencies[ deps[ i ] ];
        const fieldValue = field[ deps[ i ] ];

        // Array dependency: field value must be included in the array; a match
        // moves on to the next dependency.
        if ( Array.isArray( requiredValue ) ) {
            if ( ! requiredValue.includes( fieldValue ) ) {
                return false;
            }

            continue;
        }

        // Single value dependency: must match exactly
        if ( requiredValue !== fieldValue ) {
            return false;
        }
    }

    return true;
}

/**
 * Hook form of meetsDependencies().
 *
 * @param {Object}      field        The current editing field
 * @param {Object|null} dependencies Dependencies object from the setting definition
 * @return {boolean} Whether all dependencies are met
 */
export default function useSettingDependency( field, dependencies ) {
    return useMemo( () => meetsDependencies( field, dependencies ), [ field, dependencies ] );
}
