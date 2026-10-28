/**
 * Empty state, the develop cases:
 * - size "md" (forms list "No Post Forms Created Yet" / "No Items Here!"):
 *   padded white block, optional image, 18px title (32px above), text
 *   (32px above, 40px below when there are actions), actions 12px apart;
 * - size "compact" (forms list "no match"): just the title, 32px above;
 * - size "lg" (subscriptions "No Subscription created yet!"): 50vh centered
 *   block, 30px title, text 32px below it, actions 48px below.
 *
 * FlyHR's empty state (owner 2026-10-08, every list of the React admin):
 * - size "page" (a whole list is empty): a tinted circular badge with a
 *   lucide glyph, a "+" pill on it when there is an action, a bold 24px
 *   title, a muted line and the call to action;
 * - size "card" (no match / an empty tab or card): the same, smaller.
 * For these two sizes `icon` is the icon component (e.g. lucide `Receipt`).
 */
import { cn } from '@wedevs/plugin-ui';
import { Inbox, Plus } from 'lucide-react';

/**
 * FlyHR empty state (sizes page / card).
 *
 * @param {Object}   props
 * @param {Function} [props.icon]        Icon component (default Inbox).
 * @param {*}        props.title         Title.
 * @param {*}        [props.description] Muted text.
 * @param {*}        [props.actions]     Call to action.
 * @param {boolean}  [props.page]        Page size.
 * @param {boolean}  [props.showPlus]    Force the "+" pill.
 * @param {string}   [props.className]   Classes.
 */
function Badge( { icon: Icon = Inbox, title, description, actions, page, showPlus, className } ) {
    const plus = showPlus ?? Boolean( actions );

    return (
        <div data-wpuf-ui="" className={ cn( 'flex flex-col items-center text-center', page ? 'px-6 py-14' : 'px-4 py-10', className ) }>
            <div aria-hidden="true" className={ cn( 'relative flex shrink-0 items-center justify-center rounded-full bg-emerald-50', page ? 'size-40' : 'size-24' ) }>
                <Icon size={ page ? 56 : 36 } strokeWidth={ 1.75 } className="text-[#94A3B8]" />
                { plus && (
                    <span className={ cn( 'absolute flex items-center justify-center rounded-full border-solid border-white bg-primary text-white', page ? '-bottom-1 end-0 size-14 border-[6px]' : '-bottom-1 -end-1 size-9 border-4' ) }>
                        <Plus size={ page ? 22 : 14 } strokeWidth={ 2.5 } />
                    </span>
                ) }
            </div>
            { page
                ? <h2 className="m-0 mt-8 text-2xl font-bold leading-8 text-gray-900">{ title }</h2>
                : <p className="m-0 mt-5 text-base font-semibold leading-6 text-gray-900">{ title }</p> }
            { description && <p className={ cn( 'm-0 max-w-md text-sm text-gray-500', page ? 'mt-3 leading-6' : 'mt-1 leading-5' ) }>{ description }</p> }
            { actions && <div className={ cn( 'flex flex-wrap justify-center gap-3', page ? 'mt-8' : 'mt-5' ) }>{ actions }</div> }
        </div>
    );
}

/**
 * @param {Object} props
 * @param {*}      props.title         Title.
 * @param {*}      [props.description] Text under the title.
 * @param {string} [props.image]       Image URL (local asset).
 * @param {*}      [props.icon]        Inline icon above the title (lg).
 * @param {*}      [props.actions]     Buttons.
 * @param {string} [props.size]        md|compact|lg|page|card
 * @param {boolean} [props.showPlus]   page / card: force the "+" pill.
 */
export default function EmptyState( { title, description, image, icon, actions, size = 'md', showPlus, className } ) {
    if ( 'page' === size || 'card' === size ) {
        return <Badge icon={ icon || undefined } title={ title } description={ description } actions={ actions } page={ 'page' === size } showPlus={ showPlus } className={ className } />;
    }

    if ( 'compact' === size ) {
        return (
            <div data-wpuf-ui="" className={ cn( 'text-center', className ) }>
                <h2 className="m-0 mt-8 text-lg font-normal text-gray-800">{ title }</h2>
            </div>
        );
    }

    if ( 'lg' === size ) {
        return (
            <div data-wpuf-ui="" className={ cn( 'flex h-[50vh] items-center justify-center', className ) }>
                <div className="w-3/4 text-center">
                    { image && <img src={ image } alt="" className="mx-auto max-w-full" /> }
                    { icon }
                    { title && <h3 className="m-0 text-3xl font-normal text-gray-900">{ title }</h3> }
                    { description && <p className="m-0 mt-8 text-sm text-gray-500 text-center">{ description }</p> }
                    { actions && <div className="mt-12 flex justify-center gap-3">{ actions }</div> }
                </div>
            </div>
        );
    }

    return (
        <div data-wpuf-ui="" className={ cn( 'grid min-h-full bg-white px-6 py-24 sm:py-32 lg:px-8', className ) }>
            <div className="flex flex-col items-center">
                { image && <img src={ image } alt="" className="max-w-full" /> }
                { title && <h2 className="m-0 mt-8 text-lg font-normal text-gray-800">{ title }</h2> }
                { description && <p className={ cn( 'm-0 mt-8 text-sm text-gray-500', actions && 'mb-10' ) }>{ description }</p> }
                { actions && <div className="flex justify-center gap-3">{ actions }</div> }
            </div>
        </div>
    );
}
