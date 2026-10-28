/**
 * User Frontend > Coupons without Pro: the admin app route #/coupons
 * (Admin\Screens\CouponsPromo). What Pro's coupons do, with a preview of
 * the list.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, PageFooter, PageHeader, PageShell, ProBadge, WpufProviders } from '@wpuf/components';
import { CalendarRange, ExternalLink, Layers, Mail, Percent, Repeat, Tag } from 'lucide-react';

import { registerScreen } from '../../app/client';

const data = () => window.wpufCouponsPromo || {};

const FEATURES = [
    { icon: Percent, title: __( 'Fixed or percentage', 'wp-user-frontend' ), text: __( 'Take a set amount or a share off the pack price.', 'wp-user-frontend' ) },
    { icon: Layers, title: __( 'Pick the packs', 'wp-user-frontend' ), text: __( 'Apply a code to every pack or only to the ones you choose.', 'wp-user-frontend' ) },
    { icon: Repeat, title: __( 'Usage limits', 'wp-user-frontend' ), text: __( 'Stop a code after it has been used a number of times.', 'wp-user-frontend' ) },
    { icon: CalendarRange, title: __( 'Start and end dates', 'wp-user-frontend' ), text: __( 'Run a code for a launch week or a seasonal sale.', 'wp-user-frontend' ) },
    { icon: Mail, title: __( 'Email restriction', 'wp-user-frontend' ), text: __( 'Only let the people you invite use a code.', 'wp-user-frontend' ) },
    { icon: Tag, title: __( 'Tracked in transactions', 'wp-user-frontend' ), text: __( 'See the code and the discount on every payment.', 'wp-user-frontend' ) },
];

const PREVIEW = [
    [ 'LAUNCH20', '20%', '12 / 50', __( 'Active', 'wp-user-frontend' ) ],
    [ 'WELCOME5', '$5.00', '3 / ∞', __( 'Active', 'wp-user-frontend' ) ],
    [ 'SUMMER', '15%', '40 / 40', __( 'Expired', 'wp-user-frontend' ) ],
];

function CouponsPromo() {
    const promo = data();

    return (
        <PageShell>
            <PageHeader utm="wpuf-coupons" helpUrl={ promo.docsUrl } helpLabel={ __( 'Learn more about Coupons', 'wp-user-frontend' ) } />

            <div className="wpuf-coupons-promo mt-6 pb-10">
                <div className="flex items-center gap-3">
                    <h1 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ __( 'Coupons', 'wp-user-frontend' ) }</h1>
                    <ProBadge utm="wpuf-coupons" />
                </div>
                <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Use Coupon codes for subscription for discounts.', 'wp-user-frontend' ) }</p>

                <section className="mt-6 overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                    <div className="grid gap-8 p-6 lg:grid-cols-[1fr_420px] lg:p-8">
                        <div>
                            <h2 className="m-0 text-lg font-semibold text-gray-900">{ __( 'Discount codes for your subscription packs', 'wp-user-frontend' ) }</h2>
                            <p className="m-0 mt-2 max-w-xl text-sm text-gray-500">{ __( 'This feature is only available in the Pro Version.', 'wp-user-frontend' ) }</p>

                            <ul className="m-0 mt-6 grid list-none gap-4 p-0 sm:grid-cols-2">
                                { FEATURES.map( ( feature ) => {
                                    const Icon = feature.icon;

                                    return (
                                        <li key={ feature.title } className="m-0 flex gap-3">
                                            <span className="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-primary" aria-hidden="true">
                                                <Icon size={ 18 } strokeWidth={ 1.75 } />
                                            </span>
                                            <span>
                                                <span className="block text-sm font-medium text-gray-900">{ feature.title }</span>
                                                <span className="block text-sm text-gray-500">{ feature.text }</span>
                                            </span>
                                        </li>
                                    );
                                } ) }
                            </ul>

                            <div className="mt-8 flex flex-wrap gap-3">
                                <Button onClick={ () => window.open( promo.upgradeUrl, '_blank', 'noopener' ) }>{ __( 'Upgrade to Pro Version', 'wp-user-frontend' ) }</Button>
                                <Button variant="secondary" onClick={ () => window.open( promo.docsUrl, '_blank', 'noopener' ) }>
                                    <ExternalLink size={ 16 } aria-hidden="true" />
                                    { __( 'Learn more about Coupons', 'wp-user-frontend' ) }
                                </Button>
                            </div>
                        </div>

                        <div className="pointer-events-none select-none self-center rounded-[10px] border border-solid border-gray-200 bg-gray-50 p-3" aria-hidden="true">
                            <div className="overflow-hidden rounded-lg border border-solid border-gray-200 bg-white">
                                <div className="grid grid-cols-[1.4fr_1fr_1fr_1fr] gap-2 border-0 border-b border-solid border-gray-200 px-4 py-2 text-[11px] uppercase text-gray-400">
                                    <span>{ __( 'Code', 'wp-user-frontend' ) }</span>
                                    <span>{ __( 'Discount', 'wp-user-frontend' ) }</span>
                                    <span>{ __( 'Used', 'wp-user-frontend' ) }</span>
                                    <span>{ __( 'Status', 'wp-user-frontend' ) }</span>
                                </div>
                                { PREVIEW.map( ( row ) => (
                                    <div key={ row[ 0 ] } className="grid grid-cols-[1.4fr_1fr_1fr_1fr] items-center gap-2 border-0 border-b border-solid border-gray-100 px-4 py-3 text-xs last:border-b-0">
                                        <span className="font-mono font-medium text-gray-900">{ row[ 0 ] }</span>
                                        <span className="text-gray-700">{ row[ 1 ] }</span>
                                        <span className="text-gray-500">{ row[ 2 ] }</span>
                                        <span>
                                            <span className={ `rounded px-2 py-0.5 text-[11px] ${ row[ 3 ] === PREVIEW[ 2 ][ 3 ] ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' }` }>{ row[ 3 ] }</span>
                                        </span>
                                    </div>
                                ) ) }
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <PageFooter />
        </PageShell>
    );
}

registerScreen( 'coupons-promo', [ 'wpuf-coupons-promo' ], ( element ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <CouponsPromo />
        </WpufProviders>
    );

    return () => root.unmount();
} );
