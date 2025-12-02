import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SectionInputField from './SectionInputField';

export default function Subsection({ subSection, subscription, fields, currentSection }) {
    const openTabs = ['overview', 'content_limit', 'payment_details'];
    const isClosed = !openTabs.includes(subSection.id);
    const [closed, setClosed] = useState(isClosed);

    if (!fields || Object.keys(fields).length === 0) {
        return null;
    }

    return (
        <div className="wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-rounded-b-xl wpuf-mt-4 wpuf-mb-4">
            <h2 className="wpuf-m-0">
                <button
                    type="button"
                    onClick={() => setClosed(!closed)}
                    className={`wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-bg-gray-100 wpuf-gap-3 ${
                        closed ? 'wpuf-rounded-xl' : 'wpuf-rounded-t-xl'
                    }`}
                >
                    <span className="wpuf-flex">
                        {subSection.label}
                        {subSection.sub_label && (
                            <span className="wpuf-relative wpuf-m-0 wpuf-p-0 wpuf-ml-2 wpuf-mt-[1px] wpuf-italic wpuf-text-[11px] wpuf-text-gray-400">
                                {subSection.sub_label}
                            </span>
                        )}
                    </span>
                    <svg
                        className={`wpuf-w-3 wpuf-h-3 shrink-0 ${closed ? 'wpuf-rotate-90' : 'wpuf-rotate-180'}`}
                        data-accordion-icon
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 10 6"
                    >
                        <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5 5 1 1 5" />
                    </svg>
                </button>
            </h2>
            {!closed && (
                <div>
                    {Object.entries(fields).map(([fieldId, field]) => {
                        if (field.type === 'inline') {
                            // Handle inline fields separately if needed
                            return null;
                        }
                        return (
                            <SectionInputField
                                key={fieldId}
                                field={field}
                                fieldId={fieldId}
                                serializeKey={field.serialize_key}
                                subscription={subscription}
                            />
                        );
                    })}
                    {subSection.notice && (
                        <div className="wpuf-rounded-b-xl wpuf-bg-yellow-50 wpuf-p-4">
                            <div className="wpuf-flex wpuf-items-center">
                                <div className="wpuf-flex-shrink-0">
                                    <svg
                                        className="wpuf-h-5 wpuf-w-5 wpuf-text-yellow-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div className="wpuf-ml-3">
                                    <div className="wpuf-mt-2 wpuf-text-sm wpuf-text-yellow-700">
                                        <p dangerouslySetInnerHTML={{ __html: subSection.notice.message }}></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

