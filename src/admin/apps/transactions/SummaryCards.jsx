/**
 * Totals above the list: income and tax of completed payments, completed
 * and pending counts.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { SkeletonBar } from '@wpuf/components';
import { CircleCheck, Clock, Landmark, Wallet } from 'lucide-react';

/**
 * @param {Object} props
 * @param {Object} props.counts { income, tax, completed, pending }.
 */
export default function SummaryCards( { counts } ) {
    const cards = [
        { key: 'income', icon: Wallet, tint: 'bg-emerald-50 text-primary', label: __( 'Total income', 'wp-user-frontend' ), value: counts.income },
        { key: 'tax', icon: Landmark, tint: 'bg-sky-50 text-sky-600', label: __( 'Tax collected', 'wp-user-frontend' ), value: counts.tax },
        { key: 'completed', icon: CircleCheck, tint: 'bg-emerald-50 text-primary', label: __( 'Completed', 'wp-user-frontend' ), value: counts.completed },
        { key: 'pending', icon: Clock, tint: 'bg-amber-50 text-amber-600', label: __( 'Pending', 'wp-user-frontend' ), value: counts.pending },
    ];

    return (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            { cards.map( ( card ) => {
                const Icon = card.icon;

                return (
                    <div key={ card.key } className="flex items-center gap-4 rounded-[10px] border border-solid border-gray-200 bg-white p-5 shadow-sm">
                        <span className={ `inline-flex size-10 shrink-0 items-center justify-center rounded-lg ${ card.tint }` } aria-hidden="true">
                            <Icon size={ 20 } strokeWidth={ 1.75 } />
                        </span>
                        <div className="min-w-0">
                            <p className="m-0 text-sm text-gray-500">{ card.label }</p>
                            { /* Until the first answer: a placeholder, not a dash that reads as "none". */ }
                            { undefined === card.value || null === card.value
                                ? <SkeletonBar className="mt-2 h-6 w-20" />
                                : <p className="m-0 mt-1 truncate text-xl font-semibold text-gray-900">{ card.value }</p> }
                        </div>
                    </div>
                );
            } ) }
        </div>
    );
}
