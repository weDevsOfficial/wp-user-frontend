import { useState } from '@wordpress/element';

const Tooltip = ( { content, children, className = '' } ) => {
    const [visible, setVisible] = useState(false);
    const tooltipId = `wpuf-tooltip-${Math.random().toString(36).substr(2, 9)}`;

    return (
        <span
            className={`wpuf-relative ${className}`.trim()}
            onMouseEnter={() => setVisible(true)}
            onMouseLeave={() => setVisible(false)}
            onFocus={() => setVisible(true)}
            onBlur={() => setVisible(false)}
        >
            <span
                tabIndex={0}
                aria-describedby={visible ? tooltipId : undefined}
                className="wpuf-cursor-pointer wpuf-outline-none"
            >
                {children}
            </span>
            {visible && (
                <span
                    id={tooltipId}
                    role="tooltip"
                    className="wpuf-absolute wpuf-bottom-full wpuf-mb-2 wpuf-left-1/2 wpuf-transform -wpuf-translate-x-1/2 wpuf-z-10 wpuf-bg-gray-800 wpuf-text-white wpuf-text-xs wpuf-px-4 wpuf-py-2 wpuf-rounded wpuf-shadow-lg wpuf-w-64 wpuf-text-center"
                    style={{ whiteSpace: 'pre-line' }}
                >
                    {content}
                </span>
            )}
        </span>
    );
};

export default Tooltip;
