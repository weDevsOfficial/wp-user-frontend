import { useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SettingHelpText from './SettingHelpText';

/**
 * Visibility input for field settings.
 * Replaces Vue field-visibility component.
 *
 * Value is an object: { selected: 'everyone'|'logged_in'|'subscribed_users', choices: [] }
 *
 * Roles and subscriptions are read from window.wpuf_form_builder
 * (must be added to PHP localize_script in Section 15).
 */
export default function VisibilityInput( { optionField, field, value, onChange, builderClassNames } ) {
    const options = optionField.options || {};
    const isInline = !! optionField.inline;

    const selected = value?.selected || '';
    const choices = value?.choices || [];

    // Roles and subscriptions from localized data
    const roles = useMemo( () => window.wpuf_form_builder?.roles || {}, [] );
    const subscriptions = useMemo( () => window.wpuf_form_builder?.subscriptions || [], [] );

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
            <div className="wpuf-flex">
                { optionField.title && (
                    <label className="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">
                        { optionField.title }
                    </label>
                ) }
                <SettingHelpText text={ optionField.help_text } />
            </div>

            { /* Radio options */ }
            { isInline ? (
                <div className="wpuf-mt-2 wpuf-flex wpuf-flex-wrap">
                    { Object.entries( options ).map( ( [ key, label ] ) => (
                        <div key={ key } className="wpuf-items-center wpuf-mr-9">
                            <label className="wpuf-block wpuf-my-1 wpuf-mr-2 wpuf-font-medium wpuf-text-gray-900">
                                <input
                                    type="radio"
                                    name={ `visibility_${ field.id }` }
                                    value={ key }
                                    checked={ selected === key }
                                    onChange={ () => handleSelectedChange( key ) }
                                    className={ builderClassNames( 'radio' ) }
                                />
                                { label }
                            </label>
                        </div>
                    ) ) }
                </div>
            ) : (
                Object.entries( options ).map( ( [ key, label ] ) => (
                    <div key={ key } className="wpuf-flex wpuf-items-center wpuf-gap-x-2 wpuf-m-2">
                        <label className="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
                            <input
                                type="radio"
                                name={ `visibility_${ field.id }` }
                                value={ key }
                                checked={ selected === key }
                                onChange={ () => handleSelectedChange( key ) }
                                className="checked:!wpuf-bg-primary checked:before:!wpuf-bg-transparent"
                            />
                            { label }
                        </label>
                    </div>
                ) )
            ) }

            { /* Role choices when logged_in is selected */ }
            { selected === 'logged_in' && (
                <div className="condiotional-logic-container wpuf-mt-2">
                    <ul>
                        { Object.entries( roles ).map( ( [ role, roleName ] ) => (
                            <li key={ role } className="wpuf-mt-2 wpuf-flex wpuf-items-center">
                                <label className="wpuf-flex wpuf-items-center">
                                    <input
                                        className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                                        type="checkbox"
                                        value={ role }
                                        checked={ choices.includes( role ) }
                                        onChange={ ( e ) => handleChoiceToggle( role, e.target.checked ) }
                                    />
                                    { roleName }
                                </label>
                            </li>
                        ) ) }
                    </ul>
                </div>
            ) }

            { /* Subscription choices when subscribed_users is selected */ }
            { selected === 'subscribed_users' && (
                <div className="condiotional-logic-container wpuf-mt-2">
                    <ul>
                        { subscriptions.length > 0 ? (
                            subscriptions.map( ( pack ) => (
                                <li key={ pack.ID } className="wpuf-mt-2 wpuf-flex wpuf-items-center">
                                    <label className="wpuf-flex wpuf-items-center">
                                        <input
                                            className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                                            type="checkbox"
                                            value={ String( pack.ID ) }
                                            checked={ choices.includes( String( pack.ID ) ) }
                                            onChange={ ( e ) => handleChoiceToggle( String( pack.ID ), e.target.checked ) }
                                        />
                                        { pack.post_title }
                                    </label>
                                </li>
                            ) )
                        ) : (
                            <li>{ __( 'No subscription plan found.', 'wp-user-frontend' ) }</li>
                        ) }
                    </ul>
                </div>
            ) }
        </div>
    );
}
