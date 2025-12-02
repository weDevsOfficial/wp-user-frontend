import { __ } from '@wordpress/i18n';
import { useState, useEffect, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

export default function SectionInputField({ field, fieldId, serializeKey, subscription }) {
    const { item, errors, getMetaValue, getSerializedMetaValue, getTaxonomyRestriction, getTaxonomyViewRestriction } = useSelect(
        (select) => {
            const store = select('wpuf/subscriptions');
            return {
                item: store.getItem(),
                errors: store.getErrors(),
                getMetaValue: (key) => store.getMetaValue(key),
                getSerializedMetaValue: (key, serializeKey) => store.getSerializedMetaValue(key, serializeKey),
                getTaxonomyRestriction: () => store.getTaxonomyRestriction(),
                getTaxonomyViewRestriction: () => store.getTaxonomyViewRestriction(),
            };
        },
        []
    );

    const { modifyItem } = useDispatch('wpuf/subscriptions');

    const [value, setValue] = useState('');
    const [dateValue, setDateValue] = useState(new Date());

    const wpufSubscriptions = window.wpufSubscriptions || {};
    const showProBadge = field.is_pro && !wpufSubscriptions.isProActive;

    // Get field value based on db_type
    useEffect(() => {
        if (!item) return;

        let fieldValue = '';
        if (field.type === 'multi-select' && field.id) {
            if (field.id.startsWith('view_')) {
                const taxonomyViewRestriction = getTaxonomyViewRestriction();
                fieldValue = taxonomyViewRestriction[field.id] || [];
            } else {
                const taxonomyRestriction = getTaxonomyRestriction();
                fieldValue = taxonomyRestriction[field.id] || [];
            }
        } else {
            switch (field.db_type) {
                case 'meta':
                    fieldValue = getMetaValue(field.db_key) || '';
                    break;
                case 'meta_serialized':
                    fieldValue = getSerializedMetaValue(field.db_key, serializeKey) || '';
                    break;
                default:
                    fieldValue = item[field.db_key] || '';
            }
        }

        // Transform value based on field type
        if (field.type === 'switcher') {
            setValue(fieldValue === 'on' || fieldValue === 'yes' || fieldValue === 'private');
        } else if (field.type === 'time-date') {
            setDateValue(fieldValue ? new Date(fieldValue) : new Date());
        } else if (field.type === 'multi-select') {
            setValue(Array.isArray(fieldValue) ? fieldValue : []);
        } else {
            setValue(fieldValue);
        }
    }, [item, field, serializeKey, getMetaValue, getSerializedMetaValue, getTaxonomyRestriction, getTaxonomyViewRestriction]);

    const hasError = errors[fieldId] && errors[fieldId].status;
    const errorMessage = hasError ? errors[fieldId].message : '';

    const handleChange = (newValue) => {
        setValue(newValue);
        modifyItem(field.db_key, newValue, serializeKey);
    };

    const handleDateChange = (date) => {
        setDateValue(date);
        const formattedDate = date.toISOString().slice(0, 19).replace('T', ' ');
        modifyItem(field.db_key, formattedDate);
    };

    const toggleSwitch = () => {
        const newValue = !value;
        setValue(newValue);
        const switchValue = newValue ? 'on' : 'off';
        modifyItem(field.db_key, switchValue, serializeKey);
    };

    if (field.type === 'input-text') {
        return (
            <div className="wpuf-w-full wpuf-col-span-2 wpuf-relative wpuf-group">
                {showProBadge && (
                    <div className="wpuf-hidden wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-emerald-200 group-hover:wpuf-flex group-hover:wpuf-cursor-pointer wpuf-absolute wpuf-items-center wpuf-justify-center wpuf-bg-emerald-50/50 wpuf-backdrop-blur-sm wpuf-z-10 wpuf-p-4 wpuf-w-[104%] wpuf-h-[180%] wpuf-top-[-40%] wpuf-left-[-2%]">
                        <a
                            href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&utm_medium=popup"
                            target="_blank"
                            className="wpuf-button button-upgrade-to-pro wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-bg-emerald-600 focus:wpuf-bg-emerald-700 hover:wpuf-bg-emerald-700 wpuf-text-white hover:wpuf-text-white wpuf-rounded-md wpuf-gap-2 wpuf-font-medium wpuf-text-sm"
                        >
                            {__('Upgrade to Pro', 'wp-user-frontend')}
                        </a>
                    </div>
                )}
                <input
                    type="text"
                    value={value}
                    name={field.name}
                    id={field.name}
                    placeholder={field.placeholder || ''}
                    onChange={(e) => handleChange(e.target.value)}
                    className={`placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none ${
                        hasError ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'
                    }`}
                />
                {hasError && <p className="wpuf-mt-1 wpuf-text-sm wpuf-text-red-600">{errorMessage}</p>}
            </div>
        );
    }

    if (field.type === 'switcher') {
        return (
            <div className="wpuf-w-full wpuf-col-span-2">
                <button
                    onClick={toggleSwitch}
                    type="button"
                    className={`placeholder:wpuf-text-gray-400 wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out ${
                        value ? 'wpuf-bg-primary' : 'wpuf-bg-gray-200'
                    }`}
                    role="switch"
                >
                    <span
                        aria-hidden="true"
                        className={`wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out ${
                            value ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'
                        }`}
                    ></span>
                </button>
            </div>
        );
    }

    if (field.type === 'time-date') {
        return (
            <div className="wpuf-w-full wpuf-col-span-2">
                <DatePicker
                    selected={dateValue}
                    onChange={handleDateChange}
                    showTimeSelect
                    timeFormat="HH:mm:ss"
                    dateFormat="yyyy-MM-dd HH:mm:ss"
                    className="wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm"
                />
            </div>
        );
    }

    // Default: render as text input
    return (
        <div className="wpuf-w-full wpuf-col-span-2">
            <input
                type="text"
                value={value}
                onChange={(e) => handleChange(e.target.value)}
                className={`wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm ${
                    hasError ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'
                }`}
            />
            {hasError && <p className="wpuf-mt-1 wpuf-text-sm wpuf-text-red-600">{errorMessage}</p>}
        </div>
    );
}

