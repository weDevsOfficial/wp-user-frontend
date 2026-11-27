/**
 * The overview blocks: the status cards (a click filters the list), the
 * "needs your attention" card, and the side cards: plan, recent comments,
 * recent payments. Data from Dashboard_Overview; each block only when the
 * owner switched it on (the server sends nothing otherwise).
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Avatar, Button, Card, Pill, StatCard, cx, icons } from '@wpuf/frontend-kit';

const { AlertCircle, BadgeDollarSign, CalendarClock, CheckCircle2, Clock, FileText, Lock, MessageSquare, Receipt } = icons;

const STAT_ICONS = {
    publish: CheckCircle2,
    pending: Clock,
    draft: FileText,
    future: CalendarClock,
};

/**
 * @param {Object}   props
 * @param {Array}    props.stats    Status cards.
 * @param {string}   props.active   Status filter in use.
 * @param {Function} props.onSelect Called with a status ('' clears).
 */
export function StatusCards( { stats, active, onSelect } ) {
    return (
        <div className="wpuf-dashboard-stats" role="group" aria-label={ __( 'Posts by status', 'wp-user-frontend' ) }>
            { stats.map( ( stat ) => {
                const Icon = STAT_ICONS[ stat.status ] || FileText;
                const on = active === stat.status;

                return (
                    <div key={ stat.status } className={ cx( 'wpuf-dashboard-stats__item', on && 'is-active' ) }>
                        <StatCard
                            icon={ <Icon size={ 20 } strokeWidth={ 1.9 } /> }
                            label={ stat.label }
                            value={ stat.count }
                            tone={ stat.tone }
                            onClick={ () => onSelect( on ? '' : stat.status ) }
                        />
                    </div>
                );
            } ) }
        </div>
    );
}

const TONE_ICON = { danger: Lock, warning: AlertCircle, info: AlertCircle };

/**
 * @param {Object}   props
 * @param {Array}    props.items    Attention items.
 * @param {Function} props.onFilter Called with a status for an item that filters the list.
 */
export function Attention( { items, onFilter } ) {
    return (
        <Card className="wpuf-dashboard-attention" title={ __( 'Needs your attention', 'wp-user-frontend' ) }>
            <ul className="wpuf-dashboard-attention__list">
                { items.map( ( item ) => {
                    const Icon = TONE_ICON[ item.tone ] || AlertCircle;

                    return (
                        <li key={ item.key } className={ `wpuf-dashboard-attention__item is-${ item.tone }` }>
                            <span className="wpuf-dashboard-attention__icon" aria-hidden="true"><Icon size={ 16 } /></span>
                            <span className="wpuf-dashboard-attention__text">{ item.text }</span>
                            { item.action?.url && <Button size="sm" variant="secondary" href={ item.action.url }>{ item.action.label }</Button> }
                            { item.action?.filter && <Button size="sm" variant="secondary" onClick={ () => onFilter( item.action.filter ) }>{ item.action.label }</Button> }
                        </li>
                    );
                } ) }
            </ul>
        </Card>
    );
}

/**
 * The plan in a side card: name, state, price, dates, what is left.
 *
 * @param {Object} props
 * @param {Object} props.plan Plan data (Subscription_Schema::info()).
 */
export function PlanCard( { plan } ) {
    const active = 'active' === plan.state;
    const expired = 'expired' === plan.state;
    const states = {
        none: __( 'You have not subscribed to any package yet.', 'wp-user-frontend' ),
        pending: __( 'You may have processed your payment, but the pack is not active yet.', 'wp-user-frontend' ),
        missing: __( 'Your subscription pack is not exists. Please contact admin.', 'wp-user-frontend' ),
    };

    return (
        <Card className="wpuf-dashboard-side wpuf-dashboard-plan" title={ __( 'My plan', 'wp-user-frontend' ) }>
            { active || expired ? (
                <>
                    <div className="wpuf-dashboard-plan__head">
                        <span className="wpuf-dashboard-side__tile" aria-hidden="true"><BadgeDollarSign size={ 18 } /></span>
                        <div className="wpuf-dashboard-plan__name">
                            <strong>{ plan.pack?.title }</strong>
                            <span className="wpuf-muted">{ plan.price }{ plan.cycle ? ` · ${ plan.cycle }` : '' }</span>
                        </div>
                        <Pill tone={ expired ? 'red' : 'green' }>{ expired ? __( 'Expired', 'wp-user-frontend' ) : __( 'Active', 'wp-user-frontend' ) }</Pill>
                    </div>
                    { ( plan.dates || [] ).length > 0 && (
                        <dl className="wpuf-dashboard-side__facts">
                            { plan.dates.map( ( date ) => (
                                <div key={ date.label }>
                                    <dt>{ date.label }</dt>
                                    <dd>{ date.value }</dd>
                                </div>
                            ) ) }
                        </dl>
                    ) }
                    { ( plan.limits || [] ).length > 0 && (
                        <div className="wpuf-dashboard-plan__limits">
                            <h4>{ __( 'What is left', 'wp-user-frontend' ) }</h4>
                            <ul>
                                { plan.limits.map( ( limit ) => (
                                    <li key={ limit.label } className={ cx( limit.empty && 'is-empty' ) }>
                                        <span>{ limit.label }</span>
                                        <strong>{ limit.value }</strong>
                                    </li>
                                ) ) }
                            </ul>
                        </div>
                    ) }
                    { plan.packs_url && (
                        <Button variant={ expired ? 'primary' : 'secondary' } href={ plan.packs_url } className="wpuf-dashboard-side__button">
                            { expired ? __( 'Renew subscription', 'wp-user-frontend' ) : __( 'Change plan', 'wp-user-frontend' ) }
                        </Button>
                    ) }
                </>
            ) : (
                <div className="wpuf-dashboard-side__empty">
                    <p className="wpuf-muted">{ states[ plan.state ] || states.none }</p>
                    { plan.packs_url && <Button href={ plan.packs_url }>{ __( 'Browse plans', 'wp-user-frontend' ) }</Button> }
                </div>
            ) }
        </Card>
    );
}

/**
 * @param {Object} props
 * @param {Array}  props.comments Recent comments.
 */
export function CommentsCard( { comments } ) {
    return (
        <Card className="wpuf-dashboard-side wpuf-dashboard-comments" title={ __( 'Recent comments', 'wp-user-frontend' ) }>
            { ! comments.length ? (
                <div className="wpuf-dashboard-side__empty">
                    <span className="wpuf-dashboard-side__disc" aria-hidden="true"><MessageSquare size={ 18 } /></span>
                    <p className="wpuf-muted">{ __( 'No comments on your posts yet.', 'wp-user-frontend' ) }</p>
                </div>
            ) : (
                <ul className="wpuf-dashboard-feed">
                    { comments.map( ( comment ) => (
                        <li key={ comment.id }>
                            <Avatar url={ comment.avatar } alt="" size={ 32 } />
                            <div className="wpuf-dashboard-feed__body">
                                <p className="wpuf-dashboard-feed__title">
                                    { /* translators: 1: comment author, 2: post title */ }
                                    <a href={ comment.url }>{ sprintf( __( '%1$s on %2$s', 'wp-user-frontend' ), comment.author, comment.post ) }</a>
                                </p>
                                <p className="wpuf-dashboard-feed__text">{ comment.excerpt }</p>
                                <span className="wpuf-dashboard-feed__when">{ comment.when }</span>
                            </div>
                        </li>
                    ) ) }
                </ul>
            ) }
        </Card>
    );
}

const PAYMENT_TONE = { completed: 'green', pending: 'amber', failed: 'red', cancel: 'gray' };

const paymentLabel = ( status ) => ( {
    completed: __( 'Completed', 'wp-user-frontend' ),
    pending: __( 'Pending', 'wp-user-frontend' ),
    failed: __( 'Failed', 'wp-user-frontend' ),
    cancel: __( 'Cancelled', 'wp-user-frontend' ),
}[ status ] || status );

/**
 * @param {Object} props
 * @param {Object} props.payments { items, total, all_url }.
 */
export function PaymentsCard( { payments } ) {
    const all = payments.all_url && payments.total > payments.items.length
        ? <a className="wpuf-dashboard-side__more" href={ payments.all_url }>{ __( 'View all', 'wp-user-frontend' ) }</a>
        : null;

    return (
        <Card className="wpuf-dashboard-side wpuf-dashboard-payments" title={ __( 'Recent payments', 'wp-user-frontend' ) } actions={ all }>
            { ! payments.items.length ? (
                <div className="wpuf-dashboard-side__empty">
                    <span className="wpuf-dashboard-side__disc" aria-hidden="true"><Receipt size={ 18 } /></span>
                    <p className="wpuf-muted">{ __( 'No payments yet.', 'wp-user-frontend' ) }</p>
                </div>
            ) : (
                <ul className="wpuf-dashboard-payments__list">
                    { payments.items.map( ( payment ) => (
                        <li key={ payment.id }>
                            <div>
                                <strong>{ payment.item || __( 'Payment', 'wp-user-frontend' ) }</strong>
                                <span className="wpuf-muted">{ payment.date }</span>
                            </div>
                            <div className="wpuf-dashboard-payments__amount">
                                <strong>{ payment.amount }</strong>
                                <Pill tone={ PAYMENT_TONE[ payment.status ] || 'gray' }>{ paymentLabel( payment.status ) }</Pill>
                            </div>
                        </li>
                    ) ) }
                </ul>
            ) }
        </Card>
    );
}
