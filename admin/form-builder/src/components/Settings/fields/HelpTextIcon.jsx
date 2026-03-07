/**
 * Help text tooltip icon — matches Vue's <help-text> component.
 */
export default function HelpTextIcon( { text } ) {
    if ( ! text ) {
        return null;
    }

    return (
        <span className="wpuf-tooltip wpuf-tooltip-top wpuf-ml-2 wpuf-z-10" data-tip={ text }>
            <svg className="wpuf-w-4 wpuf-h-4 wpuf-text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
        </span>
    );
}
