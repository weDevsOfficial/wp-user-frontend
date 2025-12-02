import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { ExclamationCircleIcon } from '@heroicons/react/20/solid';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import UpdateButton from './UpdateButton';

export default function QuickEdit() {
    const { item, errors, updateError, isUpdating } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            item: store.getItem(),
            errors: store.getErrors(),
            updateError: store.getUpdateError(),
            isUpdating: store.isUpdating(),
        };
    }, []);

    const {
        setItem,
        resetErrors,
        validateFields,
        updateItem,
        setErrors,
    } = useDispatch('wpuf/subscriptions');

    const { setQuickEditStatus } = useDispatch('wpuf/subscriptions-quick-edit');
    const { addNotice } = useDispatch('wpuf/subscriptions-notice');

    const [title, setTitle] = useState('');
    const [date, setDate] = useState(new Date());

    useEffect(() => {
        if (item) {
            setTitle(item.post_title || '');
            if (item.post_date) {
                setDate(new Date(item.post_date));
            }
        }
    }, [item]);

    const getFormattedDate = (dateObj) => {
        const year = dateObj.getFullYear();
        const month = dateObj.getMonth() + 1 < 10 ? '0' + (dateObj.getMonth() + 1) : dateObj.getMonth() + 1;
        const day = dateObj.getDate() < 10 ? '0' + dateObj.getDate() : dateObj.getDate();
        const hours = dateObj.getHours() < 10 ? '0' + dateObj.getHours() : dateObj.getHours();
        const minutes = dateObj.getMinutes() < 10 ? '0' + dateObj.getMinutes() : dateObj.getMinutes();
        const seconds = dateObj.getSeconds() < 10 ? '0' + dateObj.getSeconds() : dateObj.getSeconds();

        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    };

    const handleDateChange = (dateObj) => {
        setDate(dateObj);
        if (item) {
            const updatedItem = {
                ...item,
                post_date: getFormattedDate(dateObj),
            };
            setItem(updatedItem);
        }
    };

    const handleUpdateSubscription = async () => {
        if (!item) return;

        resetErrors();

        const updatedItem = {
            ...item,
            post_title: title,
            post_date: getFormattedDate(date),
        };
        setItem(updatedItem);

        const isValid = validateFields('quickEdit');
        if (!isValid) {
            return;
        }

        try {
            const result = await updateItem();
            if (result && result.success) {
                addNotice({
                    type: 'success',
                    message: result.message,
                });
                setQuickEditStatus(false);
            } else if (result && !result.success) {
                // Error is handled by updateError in store
            }
        } catch (error) {
            console.error('Update error:', error);
        }
    };

    if (!item) {
        return null;
    }

    const hasPlanNameError = errors.planName && errors.planName.status;
    const hasDateError = errors.date && errors.date.status;

    return (
        <div className="wpuf-rounded-lg wpuf-fixed wpuf-z-20 wpuf-top-1/3 wpuf-left-[calc(50%-5rem)] wpuf-w-1/3 wpuf-bg-white wpuf-p-6 wpuf-border wpuf-border-gray-200 wpuf-shadow">
            <div className="wpuf-px-2">
                <label htmlFor="plan-name" className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                    {__('Plan name', 'wp-user-frontend')}
                </label>
                <div className="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                    <input
                        type="text"
                        name="plan-name"
                        id="plan-name"
                        className={`wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm ${
                            hasPlanNameError
                                ? '!wpuf-border-red-500 wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500'
                                : ''
                        }`}
                        aria-invalid={hasPlanNameError}
                        aria-describedby="plan-name-error"
                        onChange={(e) => setTitle(e.target.value)}
                        value={title}
                    />
                    {hasPlanNameError && (
                        <div className="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3">
                            <ExclamationCircleIcon className="wpuf-h-5 wpuf-w-5 wpuf-text-red-500" aria-hidden="true" />
                        </div>
                    )}
                </div>
                {hasPlanNameError && (
                    <p className="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="plan-name-error">
                        {errors.planName.message}
                    </p>
                )}
            </div>
            <div className="wpuf-px-2 wpuf-mt-4">
                <label htmlFor="date" className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                    {__('Date', 'wp-user-frontend')}
                </label>
                <div
                    className={`wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm ${
                        hasDateError
                            ? 'wpuf-border wpuf-border-red-500 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500'
                            : 'wpuf-ring-primary'
                    }`}
                >
                    <DatePicker
                        selected={date}
                        onChange={handleDateChange}
                        showTimeSelect
                        timeFormat="HH:mm:ss"
                        dateFormat="yyyy-MM-dd HH:mm:ss"
                        className="wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm"
                    />
                </div>
                {hasDateError && (
                    <p className="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="date-error">
                        {__('Not a valid date', 'wp-user-frontend')}
                    </p>
                )}
            </div>
            <div className="wpuf-px-2 wpuf-mt-4">
                {updateError && updateError.status && (
                    <p id="filled_error_help" className="wpuf-mt-2 wpuf-text-xs wpuf-text-red-600">
                        {updateError.message}
                    </p>
                )}
            </div>
            <div className="wpuf-flex wpuf-mt-8 wpuf-flex-row-reverse">
                <UpdateButton onUpdateSubscription={handleUpdateSubscription} />
                <button
                    onClick={() => {
                        setQuickEditStatus(false);
                        setErrors({});
                    }}
                    disabled={isUpdating}
                    type="button"
                    className={`wpuf-rounded-lg wpuf-mr-4 wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 ${
                        isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
                    }`}
                >
                    {__('Cancel', 'wp-user-frontend')}
                </button>
            </div>
        </div>
    );
}
