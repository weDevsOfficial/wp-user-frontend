/**
 * ShortcodeCopy component — displays a shortcode with a copy-to-clipboard button.
 *
 * @since WPUF_SINCE
 */
import { COPY_SVG_PATH } from '../utils/constants';

const ShortcodeCopy = ( { shortcode, copiedKey, currentCopiedKey, onCopy } ) => {
    const isCopied = currentCopiedKey === copiedKey;

    return (
        <div className="wpuf-flex wpuf-items-center">
            <code className="wpuf-mr-2 wpuf-bg-gray-50 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-shadow-sm wpuf-py-[10px] wpuf-px-[14px]">
                { isCopied ? 'Copied!' : shortcode }
            </code>
            <button
                onClick={ () => onCopy( shortcode, copiedKey ) }
                className="wpuf-text-gray-500 hover:wpuf-text-gray-700 wpuf-focus:outline-none"
                title="Copy shortcode"
            >
                <svg
                    className="wpuf-stroke-gray-400"
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d={ COPY_SVG_PATH }
                        stroke="#6B7280"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </button>
        </div>
    );
};

export default ShortcodeCopy;
