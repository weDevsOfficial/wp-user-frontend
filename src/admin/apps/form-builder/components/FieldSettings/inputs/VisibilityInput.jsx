import { useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Checkbox, Radio } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';
import { builderData } from '../../../utils/boot';

/**
 * Visibility input for field settings.
 * Replaces Vue field-visibility component.
 *
 * Value is an object: { selected: 'everyone'|'logged_in'|'subscribed_users', choices: [] }
 *
 * Roles and subscriptions are read from builderData().
 * On the shared Radio / Checkbox wrappers (4.4c).
 */
export default function VisibilityInput( { optionField, field, value, onChange } ) {
    const options = optionField.options || {};
    const isInline = !! optionField.inline;

    const selected = value?.selected || '';
    const choices = value?.choices || [];

    // Roles and subscriptions from localized data
    const roles = useMemo( () => builderData().roles || {}, [] );
    const subscriptions = useMemo( () => builderData().subscriptions || [], [] );

    const handleSelectedChange = useCallback( ( key ) => {
        onChange( { selected: key, choices: [] } );
    }, [ onChange ] );

    const handleChoiceToggle = useCallback( ( choiceValue, checked ) => {
        const newChoices = [ ...choices ];
        if ( checked ) {
            if ( ! newChoices.includes( choiceValue ) ) {
                newChoices.push( choiceValue );
            }
        } else {
            const idx = newChoices.indexOf( choiceValue );
            if ( idx > -1 ) {
                newChoices.splice( idx, 1 );
            }
        }
        onChange( { selected, choices: newChoices } );
    }, [ selected, choices, onChange ] );

    return (
        <div className="panel-field-opt panel-field-opt-radio">
            <div className="flex">
                { optionField.title && (
                    <label className="wpuf-option-field-title text-gray-700 font-medium">
                        { optionField.title }
                    </label>
                ) }
                <SettingHelpText text={ optionField.help_text } />
            </div>

            { /* Radio options */ }
            <Radio
                name={ `visibility_${ field.id }` }
                options={ options }
                value={ selected }
                onChange={ handleSelectedChange }
                inline={ isInline }
                className={ isInline ? 'mt-2 gap-x-9 gap-y-2' : 'gap-2 m-2' }
            />

            { /* Role choices when logged_in is selected */ }
            { selected === 'logged_in' && (
                <div className="condiotional-logic-container mt-2">
                    <ul>
                        { Object.entries( roles ).map( ( [ role, roleName ] ) => (
                            <li key={ role } className="mt-2 flex items-center">
                                <Checkbox
                                    id={ `visibility_${ field.id }_${ role }` }
                                    data-value={ role }
                                    value={ choices.includes( role ) }
                                    onChange={ ( on ) => handleChoiceToggle( role, on ) }
                                    label={ roleName }
                                />
                            </li>
                        ) ) }
                    </ul>
                </div>
            ) }

            { /* Subscription choices when subscribed_users is selected */ }
            { selected === 'subscribed_users' && (
                <div className="condiotional-logic-container mt-2">
                    <ul>
                        { subscriptions.length > 0 ? (
                            subscriptions.map( ( pack ) => (
                                <li key={ pack.id } className="mt-2 flex items-center">
                                    <Checkbox
                                        id={ `visibility_${ field.id }_pack_${ pack.id }` }
                                        data-value={ String( pack.id ) }
                                        value={ choices.includes( String( pack.id ) ) }
                                        onChange={ ( on ) => handleChoiceToggle( String( pack.id ), on ) }
                                        label={ pack.title }
                                    />
                                </li>
                            ) )
                        ) : (
                            <li>
                                { __( 'No subscription packs yet. Create a pack, then come back to choose who can see this field.', 'wp-user-frontend' ) }{ ' ' }
                                <a
                                    href={ `${ ( window.wpuf_form_builder && window.wpuf_form_builder.admin_url ) || '' }admin.php?page=wp-user-frontend#/subscriptions?action=new` }
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-primary no-underline hover:underline"
                                >
                                    { __( 'Create subscription pack', 'wp-user-frontend' ) }
                                </a>
                            </li>
                        ) }
                    </ul>
                </div>
            ) }
        </div>
    );
}
