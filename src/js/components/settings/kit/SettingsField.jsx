/**
 * Shared Tailwind class names for settings field controls.
 *
 * Ported verbatim from the Form Builder React Settings kit
 * (admin/form-builder/src/components/Settings/SettingsField.jsx) so the
 * settings screen renders identically to the rest of the React admin.
 * The field components in ./fields import SETTING_CLASS_NAMES from here.
 */
export const SETTING_CLASS_NAMES = {
    text: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    number: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    textarea: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    dropdown: 'wpuf-block wpuf-w-full wpuf-min-w-full wpuf-text-gray-700 wpuf-font-normal !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 !wpuf-text-base !leading-6',
    checkbox: '!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded',
};
