/**
 * One tool: icon tile, title, text, actions.
 *
 * @since WPUF_SINCE
 */

/**
 * @param {Object}   props
 * @param {Function} props.icon          lucide icon.
 * @param {*}        props.title         Title.
 * @param {*}        [props.description] Text.
 * @param {boolean}  [props.danger]      Red tile (removes data).
 * @param {*}        [props.children]    Actions and extra content.
 */
export default function ToolCard( { icon: Icon, title, description, danger = false, children } ) {
    const tint = danger ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-primary';

    return (
        <section className="flex gap-4 rounded-[10px] border border-solid border-gray-200 bg-white p-5">
            <span className={ `inline-flex size-10 shrink-0 items-center justify-center rounded-lg ${ tint }` } aria-hidden="true">
                <Icon size={ 20 } strokeWidth={ 1.75 } />
            </span>
            <div className="min-w-0 flex-1">
                <h2 className="m-0 text-base font-semibold text-gray-900">{ title }</h2>
                { description && <p className="m-0 mt-1 text-sm text-gray-500">{ description }</p> }
                { children && <div className="mt-4">{ children }</div> }
            </div>
        </section>
    );
}
