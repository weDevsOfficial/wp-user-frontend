/**
 * SlotFill + PluginArea for one screen: Pro and third-party code add UI with
 * `registerPlugin( name, { scope: 'wpuf-<screen>', render } )` and fills for
 * the screen's slots (shared/filters.js SLOTS). Each screen wraps itself in it
 * in its own screen task (design.md D24).
 */
import { SlotFillProvider } from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';

/**
 * @param {Object} props
 * @param {string} props.screen   Screen key (`form-builder`, `forms-list`, `subscriptions`, `settings`).
 * @param {*}      props.children Screen tree.
 */
export default function ScreenSlots( { screen, children } ) {
    return (
        <SlotFillProvider>
            { children }
            <PluginArea scope={ `wpuf-${ screen }` } />
        </SlotFillProvider>
    );
}
