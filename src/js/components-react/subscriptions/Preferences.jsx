import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

export default function Preferences() {
    const [isSaving, setIsSaving] = useState(false);
    const [settings, setSettings] = useState({
        button_color: '', // Empty means use Tailwind's wpuf-bg-primary
    });

    const { addNotice } = useDispatch('wpuf/subscriptions-notice');

    const wpufSubscriptions = window.wpufSubscriptions || {};

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await apiFetch({
                path: '/wpuf/v1/subscription-settings',
                method: 'GET',
            });

            if (response) {
                setSettings((prev) => ({ ...prev, ...response }));
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    };

    const saveSettings = async () => {
        setIsSaving(true);

        try {
            const response = await apiFetch({
                path: '/wpuf/v1/subscription-settings',
                method: 'POST',
                data: settings,
            });

            if (response) {
                addNotice({
                    type: 'success',
                    message: __('Preferences saved successfully', 'wp-user-frontend'),
                });
            } else {
                throw new Error('Failed to save settings');
            }
        } catch (error) {
            addNotice({
                type: 'danger',
                message: __('Failed to save settings', 'wp-user-frontend'),
            });
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <div className="wpuf-p-10 wpuf-max-w-4xl">
            <div className="wpuf-mb-6">
                <h2 className="wpuf-text-2xl wpuf-font-semibold wpuf-text-gray-900 wpuf-mb-2">
                    {__('Subscription Preferences', 'wp-user-frontend')}
                </h2>
                <p className="wpuf-text-sm wpuf-text-gray-600">
                    {__('Configure subscription appearance preferences', 'wp-user-frontend')}
                </p>
            </div>

            <div>
                <div className="wpuf-space-y-6">
                    {/* Button Appearance Section */}
                    <div>
                        <h3 className="wpuf-text-lg wpuf-font-medium wpuf-text-gray-900 wpuf-mb-4">
                            {__('Color Settings', 'wp-user-frontend')}
                        </h3>

                        <div>
                            <div className="wpuf-flex wpuf-items-center wpuf-mb-1">
                                <label className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">
                                    {__('Button Color', 'wp-user-frontend')}
                                </label>
                                <span
                                    className="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
                                    title={__(
                                        'Custom color for subscription buttons. Leave empty to use the default primary color from theme.',
                                        'wp-user-frontend'
                                    )}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
                                        <path
                                            d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
                                            stroke="#9CA3AF"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        ></path>
                                    </svg>
                                </span>
                            </div>
                            <div className="wpuf-flex wpuf-items-center wpuf-space-x-3">
                                <div className="wpuf-flex wpuf-items-center wpuf-space-x-3">
                                    <input
                                        type="color"
                                        value={settings.button_color || '#4f46e5'}
                                        onChange={(e) => setSettings({ ...settings, button_color: e.target.value })}
                                        className="wpuf-h-10 wpuf-w-20 wpuf-rounded wpuf-border wpuf-border-gray-300 wpuf-cursor-pointer"
                                    />
                                    <input
                                        type="text"
                                        value={settings.button_color}
                                        onChange={(e) => setSettings({ ...settings, button_color: e.target.value })}
                                        placeholder="#4f46e5 or empty for default"
                                        className="wpuf-rounded-md wpuf-border-gray-300 wpuf-shadow-sm focus:wpuf-border-primary focus:wpuf-ring-primary wpuf-text-sm wpuf-w-48"
                                    />
                                </div>
                                <div className="wpuf-ml-4 wpuf-w-32">
                                    <button
                                        type="button"
                                        className={`wpuf-subscription-buy-btn wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm wpuf-ring-0 wpuf-transition-all wpuf-duration-200 wpuf-leading-6 ${
                                            settings.button_color ? '' : 'wpuf-bg-primary hover:wpuf-bg-primaryHover'
                                        }`}
                                        style={
                                            settings.button_color
                                                ? {
                                                      backgroundColor: settings.button_color,
                                                  }
                                                : {}
                                        }
                                        onMouseEnter={(e) => {
                                            if (settings.button_color) {
                                                e.target.style.filter = 'brightness(0.9)';
                                            }
                                        }}
                                        onMouseLeave={(e) => {
                                            if (settings.button_color) {
                                                e.target.style.filter = 'brightness(1)';
                                            }
                                        }}
                                    >
                                        {__('Buy Now', 'wp-user-frontend')}
                                    </button>
                                </div>
                            </div>
                            <p className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-2">
                                {__('Leave empty to use the default primary color from your Tailwind configuration.', 'wp-user-frontend')}
                            </p>
                        </div>
                    </div>

                    {/* Save Button */}
                    <div className="wpuf-pt-6 wpuf-flex wpuf-justify-end">
                        <button
                            onClick={saveSettings}
                            disabled={isSaving}
                            type="button"
                            className="wpuf-rounded-md wpuf-bg-primary wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-primary disabled:wpuf-opacity-50"
                        >
                            {isSaving ? __('Saving...', 'wp-user-frontend') : __('Save Preferences', 'wp-user-frontend')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

