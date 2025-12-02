import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import Subsection from './Subsection';

export default function SubscriptionsDetails() {
    const [currentTab, setCurrentTab] = useState('subscription_details');

    const { item } = useSelect((select) => ({
        item: select('wpuf/subscriptions').getItem(),
    }), []);

    // Note: Field dependency will be handled by Subsection components

    const wpufSubscriptions = window.wpufSubscriptions || {};

    useEffect(() => {
        // Add dependent fields to dependency store
        if (wpufSubscriptions.dependentFields) {
            addDependentFields(wpufSubscriptions.dependentFields);
        }
    }, [addDependentFields]);

    if (!wpufSubscriptions.sections || !wpufSubscriptions.subSections || !wpufSubscriptions.fields) {
        return (
            <div className="wpuf-mt-4">
                <p>{__('Loading form fields...', 'wp-user-frontend')}</p>
            </div>
        );
    }

    return (
        <>
            <div className="wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200">
                <ul className="wpuf-flex wpuf-flex-wrap wpuf--mb-px">
                    {wpufSubscriptions.sections.map((section) => (
                        <li key={section.id} className="wpuf-mb-0 wpuf-me-2">
                            <button
                                onClick={() => setCurrentTab(section.id)}
                                className={`active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-rounded-t-lg hover:wpuf-text-primary hover:wpuf-border-b-2 hover:wpuf-border-primary wpuf-transition-all ${
                                    currentTab === section.id
                                        ? 'wpuf-border-b-2 wpuf-border-primary wpuf-text-primary'
                                        : ''
                                }`}
                            >
                                {section.title}
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
            {Object.entries(wpufSubscriptions.subSections).map(([key, subSections]) => (
                <div key={key} style={{ display: currentTab === key ? 'block' : 'none' }}>
                    {subSections.map((section) => (
                        <Subsection
                            key={section.id}
                            currentSection={currentTab}
                            subSection={section}
                            subscription={item}
                            fields={wpufSubscriptions.fields[key]?.[section.id] || {}}
                        />
                    ))}
                </div>
            ))}
        </>
    );
}

