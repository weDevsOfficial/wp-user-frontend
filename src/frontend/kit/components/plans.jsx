/**
 * Subscription pieces shared by the plans app (`[wpuf_sub_pack]`,
 * `[wpuf_sub_info]`) and the account page's Subscription section:
 * PlanCard (one pack), SubscriptionCard (the user's subscription) and
 * CancelPlanForm (the classic cancel POST, after a confirm dialog).
 *
 * Data: Subscription_Schema::packs() / info(). Buying is a plain link to the
 * URL the server built (registration or the payment page); cancelling posts
 * the classic fields to the page, so Payment_Flow::user_subscription_cancel()
 * and its hooks run as before.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Check, CalendarClock, BadgeDollarSign, ClipboardList } from '../icons';
import confirmDialog from './confirm';
import { Button, Pill, cx } from './primitives';
import ServerHtml from './ServerHtml';

const VISIBLE_FEATURES = 5;

/**
 * The classic cancel form: hidden fields from the server, a confirm, then a
 * native submit (no fetch: the handler redirects and prints its own notices).
 *
 * @param {Object} props
 * @param {Object} props.fields Field name => value (Subscription_Schema cancel fields)
 * @param {string} [props.label]
 * @param {string} [props.size]  md (next to the card's other buttons) | sm
 */
export function CancelPlanForm( { fields, label = __( 'Cancel subscription', 'wp-user-frontend' ), size = 'md' } ) {
    const form = useRef( null );
    const [ busy, setBusy ] = useState( false );

    if ( ! fields ) {
        return null;
    }

    const onSubmit = async ( event ) => {
        event.preventDefault();

        const ok = await confirmDialog( {
            title: __( 'Cancel this subscription?', 'wp-user-frontend' ),
            text: __( 'Your pack stops at once and you lose what is left of it.', 'wp-user-frontend' ),
            confirmText: __( 'Cancel subscription', 'wp-user-frontend' ),
        } );

        if ( ok && form.current ) {
            setBusy( true );
            HTMLFormElement.prototype.submit.call( form.current );
        }
    };

    return (
        <form ref={ form } method="post" action="" className="wpuf-plan-cancel" onSubmit={ onSubmit }>
            { Object.keys( fields ).map( ( name ) => (
                <input key={ name } type="hidden" name={ name } value={ fields[ name ] } />
            ) ) }
            <Button type="submit" variant="danger" size={ size } busy={ busy }>{ label }</Button>
        </form>
    );
}

/**
 * One subscription pack.
 *
 * @param {Object} props
 * @param {Object} props.card  Subscription_Schema card
 * @param {string} [props.color] The "button color" setting
 */
export function PlanCard( { card, color = '' } ) {
    const [ open, setOpen ] = useState( false );
    const features = card.features || [];
    const hidden = Math.max( 0, features.length - VISIBLE_FEATURES );
    const shown = open ? features : features.slice( 0, VISIBLE_FEATURES );
    const style = color ? { '--wpuf-plan-accent': color } : undefined;

    return (
        <article className={ cx( 'wpuf-plan-card', `wpuf-pack-${ card.id }`, card.featured && 'is-featured', card.current && 'is-current' ) } style={ style }>
            <header className="wpuf-plan-card__head">
                <h3 className="wpuf-plan-card__title">{ card.title }</h3>
                { card.current ? (
                    <Pill tone="green">{ __( 'Current plan', 'wp-user-frontend' ) }</Pill>
                ) : card.featured && (
                    <Pill tone="blue">{ __( 'Most popular', 'wp-user-frontend' ) }</Pill>
                ) }
            </header>

            { card.description && <ServerHtml html={ card.description } className="wpuf-plan-card__desc" /> }

            <div className="wpuf-plan-card__price">
                <span className="wpuf-plan-card__amount">{ card.price }</span>
                { ! card.free && card.cycle && <span className="wpuf-plan-card__cycle">{ card.cycle }</span> }
            </div>
            { card.trial && <p className="wpuf-plan-card__trial">{ card.trial }</p> }

            <div className="wpuf-plan-card__action">
                { card.disabled || ! card.url ? (
                    <span className="wpuf-btn wpuf-btn--secondary wpuf-plan-card__button is-disabled" aria-disabled="true">{ card.button }</span>
                ) : (
                    <Button href={ card.url } className="wpuf-plan-card__button wpuf-subscription-buy-btn">{ card.button }</Button>
                ) }
            </div>

            { features.length > 0 && (
                <div className="wpuf-plan-card__features">
                    <ul>
                        { shown.map( ( feature, index ) => (
                            <li key={ index }>
                                <Check size={ 16 } aria-hidden="true" />
                                <span>{ feature }</span>
                            </li>
                        ) ) }
                    </ul>
                    { hidden > 0 && (
                        <button type="button" className="wpuf-plan-card__more" aria-expanded={ open } onClick={ () => setOpen( ! open ) }>
                            { open
                                ? __( 'See less', 'wp-user-frontend' )
                                /* translators: %d: number of more features */
                                : sprintf( _n( 'See %d more feature', 'See %d more features', hidden, 'wp-user-frontend' ), hidden ) }
                        </button>
                    ) }
                </div>
            ) }
        </article>
    );
}

/**
 * The user's subscription.
 *
 * @param {Object} props
 * @param {Object} props.info Subscription_Schema::info()
 */
export function SubscriptionCard( { info } ) {
    const browse = info.packs_url && (
        <Button href={ info.packs_url }>{ __( 'Browse plans', 'wp-user-frontend' ) }</Button>
    );
    let body;

    if ( 'active' === info.state || 'expired' === info.state ) {
        const expired = 'expired' === info.state;

        body = (
            <section className={ cx( 'wpuf-plan-status', expired && 'is-expired' ) }>
                <header className="wpuf-plan-status__head">
                    <span className="wpuf-plan-status__icon" aria-hidden="true"><BadgeDollarSign size={ 20 } /></span>
                    <div className="wpuf-plan-status__title">
                        <span className="wpuf-muted">{ __( 'Current plan', 'wp-user-frontend' ) }</span>
                        <h3>{ info.pack?.title }</h3>
                    </div>
                    <Pill tone={ expired ? 'red' : 'green' }>{ expired ? __( 'Expired', 'wp-user-frontend' ) : __( 'Active', 'wp-user-frontend' ) }</Pill>
                </header>

                <div className="wpuf-plan-status__price">
                    <span className="wpuf-plan-card__amount">{ info.price }</span>
                    { info.cycle && <span className="wpuf-plan-card__cycle">{ info.cycle }</span> }
                </div>

                { ( info.dates || [] ).length > 0 && (
                    <ul className="wpuf-plan-status__dates">
                        { info.dates.map( ( date ) => (
                            <li key={ date.label }>
                                <CalendarClock size={ 16 } aria-hidden="true" />
                                <span className="wpuf-muted">{ date.label }</span>
                                <strong>{ date.value }</strong>
                            </li>
                        ) ) }
                    </ul>
                ) }

                { ( info.limits || [] ).length > 0 && (
                    <div className="wpuf-plan-status__limits">
                        <h4>{ __( 'What is left', 'wp-user-frontend' ) }</h4>
                        <ul>
                            { info.limits.map( ( limit ) => (
                                <li key={ limit.label }>
                                    <span className="wpuf-muted">{ limit.label }</span>
                                    <strong>{ limit.value }</strong>
                                </li>
                            ) ) }
                        </ul>
                    </div>
                ) }

                { expired && <p className="wpuf-plan-status__note">{ __( 'Your subscription has expired. Pick a plan to keep posting.', 'wp-user-frontend' ) }</p> }

                <footer className="wpuf-plan-status__actions">
                    { info.packs_url && (
                        <Button variant={ expired ? 'primary' : 'secondary' } href={ info.packs_url }>
                            { expired ? __( 'Renew subscription', 'wp-user-frontend' ) : __( 'Change plan', 'wp-user-frontend' ) }
                        </Button>
                    ) }
                    { ! expired && <CancelPlanForm fields={ info.cancel } /> }
                </footer>
            </section>
        );
    } else {
        const texts = {
            none: [ __( 'No active subscription', 'wp-user-frontend' ), __( 'You have not subscribed to any package yet.', 'wp-user-frontend' ) ],
            pending: [
                info.pack?.title
                    /* translators: %s: pack name */
                    ? sprintf( __( '%s: payment pending', 'wp-user-frontend' ), info.pack.title )
                    : __( 'Your plan is not active yet', 'wp-user-frontend' ),
                __( 'You may have processed your payment, but the pack is not active yet.', 'wp-user-frontend' ),
            ],
            missing: [ __( 'Subscription not found', 'wp-user-frontend' ), __( 'Your subscription pack is not exists. Please contact admin.', 'wp-user-frontend' ) ],
            guest: [ __( 'Log in to see your subscription', 'wp-user-frontend' ), '' ],
        };
        const [ title, text ] = texts[ info.state ] || texts.none;

        body = (
            <div className={ cx( 'wpuf-state-card', `wpuf-state-card--plan-${ info.state }` ) } role="status">
                <span className="wpuf-state-card__icon" aria-hidden="true"><ClipboardList size={ 22 } /></span>
                <h3 className="wpuf-state-card__title">{ title }</h3>
                { text && <p className="wpuf-state-card__text">{ text }</p> }
                { 'none' === info.state && browse }
            </div>
        );
    }

    return (
        <>
            { body }
            { info.slots?.extra && <ServerHtml html={ info.slots.extra } className="wpuf-plan-extra" slug="subscription" /> }
        </>
    );
}
