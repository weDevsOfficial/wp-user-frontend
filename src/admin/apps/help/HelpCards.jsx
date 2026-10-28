/**
 * The three cards under the topics: review, report a bug, contact support.
 *
 * @since WPUF_SINCE
 */
import { Button } from '@wpuf/components';
import { Bug, Headset, ThumbsUp } from 'lucide-react';

// Icon and tint per card, in the order Help_Content::blocks() lists them.
const ICONS = [
    { Icon: ThumbsUp, tint: 'bg-sky-50 text-sky-600' },
    { Icon: Bug, tint: 'bg-purple-50 text-purple-600' },
    { Icon: Headset, tint: 'bg-indigo-50 text-indigo-600' },
];

/**
 * @param {Object}   props
 * @param {Object[]} props.blocks { title, text, button, url }.
 */
export default function HelpCards( { blocks } ) {
    return (
        <div className="wpuf-help-cards mt-6 grid gap-6 md:grid-cols-3">
            { blocks.map( ( block, index ) => {
                const { Icon, tint } = ICONS[ index % ICONS.length ];

                return (
                    <div key={ block.url } className="flex flex-col items-center rounded-[10px] border border-solid border-gray-200 bg-white p-6 text-center shadow-sm">
                        <span className={ `flex size-12 items-center justify-center rounded-full ${ tint }` } aria-hidden="true">
                            <Icon className="size-6" strokeWidth={ 1.75 } />
                        </span>
                        <h3 className="m-0 mt-4 text-base font-semibold text-gray-900">{ block.title }</h3>
                        <p className="m-0 mt-2 flex-1 text-sm text-gray-500">{ block.text }</p>
                        <Button variant="secondary" className="mt-5" onClick={ () => window.open( block.url, '_blank', 'noopener' ) }>{ block.button }</Button>
                    </div>
                );
            } ) }
        </div>
    );
}
