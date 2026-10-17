/**
 * Every field of one payment: amount and status up top, then payment,
 * buyer, item and gateway sections with icons, and the payment's actions.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Modal } from '@wpuf/components';
import { CalendarDays, CircleCheck, Clock, FileText, Hash, Mail, MapPin, Package, Percent, Receipt, Tag, User, UserRound } from 'lucide-react';

import StatusBadge from './StatusBadge';

/**
 * A link when the URL is there, else the text.
 *
 * @param {Object} props
 * @param {Object} props.item { title|code|name, url }.
 */
const Linked = ( { item } ) => {
    if ( ! item ) {
        return null;
    }

    const text = item.title || item.code || item.name;

    return item.url ? <a href={ item.url } className="font-medium text-primary no-underline hover:underline">{ text }</a> : text;
};

/**
 * "All payments …" under a user, pack or coupon: narrows the list to it.
 *
 * @param {Object}   props
 * @param {Function} props.onClick () => void
 * @param {*}        props.children Label.
 */
const ScopeLink = ( { onClick, children } ) => (
    <button
        type="button"
        onClick={ onClick }
        className="ms-2 cursor-pointer border-0 bg-transparent p-0 text-xs text-gray-500 hover:text-primary hover:underline"
    >
        { children }
    </button>
);

/**
 * @param {Object}   props
 * @param {Function} props.icon     lucide icon.
 * @param {*}        props.label    Label.
 * @param {*}        props.children Value.
 */
const Row = ( { icon: Icon, label, children } ) => (
    <div className="flex items-start gap-3 py-2 text-sm">
        <Icon size={ 16 } strokeWidth={ 1.75 } className="mt-0.5 shrink-0 text-gray-400" aria-hidden="true" />
        <dt className="w-32 shrink-0 text-gray-500">{ label }</dt>
        <dd className="m-0 min-w-0 flex-1 break-words text-gray-900">{ children || <span className="text-gray-400">–</span> }</dd>
    </div>
);

/**
 * @param {Object} props
 * @param {*}      props.title    Section title.
 * @param {*}      props.children Rows.
 */
const Group = ( { title, children } ) => (
    <section className="rounded-lg border border-solid border-gray-200 px-4 py-2">
        <h3 className="m-0 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{ title }</h3>
        <dl className="m-0">{ children }</dl>
    </section>
);

/**
 * @param {Object}   props
 * @param {Object}   props.row       Payment row (null: closed).
 * @param {Function} props.onClose   () => void
 * @param {Function} props.onAction  ( action, rows ) => void
 * @param {boolean}  props.canManage Actions allowed.
 * @param {Function} [props.onScope] ( key, id ) => void: list one user's / pack's / coupon's payments.
 * @param {Function} [props.onGatewaySettings] () => void: open the payment settings.
 */
export default function TransactionDetails( { row, onClose, onAction, canManage, onScope, onGatewaySettings } ) {
    if ( ! row ) {
        return null;
    }

    const address = Object.values( row.payer_address || {} ).filter( Boolean ).join( ', ' );
    const pending = 'pending' === row.status;
    const StatusIcon = pending ? Clock : CircleCheck;

    return (
        /* translators: %d: payment ID */
        <Modal open onClose={ onClose } title={ sprintf( __( 'Payment #%d', 'wp-user-frontend' ), row.id ) } className="sm:max-w-xl">
            <div className="flex items-center gap-4 rounded-lg bg-gray-50 p-4">
                <span className={ `inline-flex size-12 shrink-0 items-center justify-center rounded-full ${ pending ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-primary' }` } aria-hidden="true">
                    <StatusIcon size={ 24 } strokeWidth={ 1.75 } />
                </span>
                <div className="min-w-0 flex-1">
                    <p className="m-0 text-2xl font-semibold leading-tight text-gray-900">{ row.amounts.cost }</p>
                    <p className="m-0 mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                        <CalendarDays size={ 14 } aria-hidden="true" />
                        { row.date }
                    </p>
                </div>
                <StatusBadge status={ row.status } />
            </div>

            <div className="mt-4 grid gap-3">
                <Group title={ __( 'Payment', 'wp-user-frontend' ) }>
                    <Row icon={ Receipt } label={ __( 'Subtotal', 'wp-user-frontend' ) }>{ row.amounts.subtotal }</Row>
                    <Row icon={ Tag } label={ __( 'Coupon', 'wp-user-frontend' ) }>
                        { row.coupon && <Linked item={ row.coupon } /> }
                        { row.coupon && onScope && <ScopeLink onClick={ () => onScope( 'coupon_id', row.coupon.id ) }>{ __( 'All payments with it', 'wp-user-frontend' ) }</ScopeLink> }
                        { row.amounts.discount && <span className="ms-2 text-gray-500">−{ row.amounts.discount }</span> }
                    </Row>
                    <Row icon={ Percent } label={ __( 'Tax', 'wp-user-frontend' ) }>{ row.amounts.tax }</Row>
                </Group>

                <Group title={ __( 'Buyer', 'wp-user-frontend' ) }>
                    <Row icon={ User } label={ __( 'User', 'wp-user-frontend' ) }>
                        { row.user && <Linked item={ row.user } /> }
                        { row.user && onScope && <ScopeLink onClick={ () => onScope( 'user_id', row.user.id ) }>{ __( 'All payments by this user', 'wp-user-frontend' ) }</ScopeLink> }
                    </Row>
                    <Row icon={ UserRound } label={ __( 'Payer', 'wp-user-frontend' ) }>{ row.payer }</Row>
                    <Row icon={ Mail } label={ __( 'Email', 'wp-user-frontend' ) }>{ row.payer_email }</Row>
                    <Row icon={ MapPin } label={ __( 'Address', 'wp-user-frontend' ) }>{ address }</Row>
                </Group>

                <Group title={ __( 'Item', 'wp-user-frontend' ) }>
                    { row.pack && (
                        <Row icon={ Package } label={ __( 'Subscription', 'wp-user-frontend' ) }>
                            <Linked item={ row.pack } />
                            { onScope && <ScopeLink onClick={ () => onScope( 'pack_id', row.pack.id ) }>{ __( 'All payments for this pack', 'wp-user-frontend' ) }</ScopeLink> }
                        </Row>
                    ) }
                    { row.post && <Row icon={ FileText } label={ __( 'Post', 'wp-user-frontend' ) }><Linked item={ row.post } /></Row> }
                    { ! row.pack && ! row.post && <Row icon={ Package } label={ __( 'Item', 'wp-user-frontend' ) } /> }
                </Group>

                <Group title={ __( 'Gateway', 'wp-user-frontend' ) }>
                    <div className="flex items-center gap-3 py-2 text-sm">
                        { row.gateway_logo && (
                            <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-solid border-gray-200 bg-white">
                                <img src={ row.gateway_logo } alt="" className="max-h-6 max-w-7" />
                            </span>
                        ) }
                        <span className="font-medium text-gray-900">{ row.gateway || '–' }</span>
                        { onGatewaySettings && <ScopeLink onClick={ onGatewaySettings }>{ __( 'Gateway settings', 'wp-user-frontend' ) }</ScopeLink> }
                    </div>
                    <Row icon={ Hash } label={ __( 'Transaction ID', 'wp-user-frontend' ) }>{ row.transaction_id }</Row>
                </Group>
            </div>

            { canManage && (
                <div className="mt-6 flex justify-end gap-2">
                    { 'order' === row.kind && (
                        <>
                            <Button variant="secondary" onClick={ () => onAction( 'reject', [ row ] ) }>{ __( 'Reject', 'wp-user-frontend' ) }</Button>
                            <Button onClick={ () => onAction( 'accept', [ row ] ) }>{ __( 'Accept', 'wp-user-frontend' ) }</Button>
                        </>
                    ) }
                    { 'transaction' === row.kind && (
                        <Button variant="destructive" onClick={ () => onAction( 'delete', [ row ] ) }>{ __( 'Delete', 'wp-user-frontend' ) }</Button>
                    ) }
                </div>
            ) }
        </Modal>
    );
}
