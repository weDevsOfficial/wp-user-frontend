/**
 * `[wpuf_sub_pack]`: the classic list's notices, the user's active pack
 * with its cancel form, the listing hooks' output and the pack cards.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { CancelPlanForm, Notice, PlanCard, ServerHtml } from '@wpuf/frontend-kit';

export default function Plans( { data } ) {
    const cards = data.cards || [];

    return (
        <div className="wpuf-plans">
            { ( data.notices || [] ).map( ( notice, index ) => (
                <Notice key={ index } kind={ notice.kind }>
                    { notice.title && <strong className="wpuf-plans__notice-title">{ notice.title }</strong> }
                    { notice.text }
                </Notice>
            ) ) }

            { data.active && (
                <div className="wpuf-plans__active">
                    <div>
                        <p className="wpuf-plans__active-title">{ __( 'You have a subscription pack activated.', 'wp-user-frontend' ) }</p>
                        { /* translators: %s: pack name */ }
                        <p className="wpuf-muted">{ sprintf( __( 'Pack name: %s', 'wp-user-frontend' ), data.active.title ) }</p>
                    </div>
                    <CancelPlanForm fields={ data.active.cancel } />
                </div>
            ) }

            { data.slots?.before && <ServerHtml html={ data.slots.before } slug="plans-before" /> }

            { cards.length > 0 ? (
                <ul className="wpuf_packs wpuf-plans__grid">
                    { cards.map( ( card ) => (
                        <li key={ card.id } className={ `wpuf-pack-${ card.id }` }>
                            <PlanCard card={ card } color={ data.color } />
                        </li>
                    ) ) }
                </ul>
            ) : (
                <p className="wpuf-muted wpuf-plans__empty">{ __( 'No subscription packs are available right now.', 'wp-user-frontend' ) }</p>
            ) }

            { data.slots?.after && <ServerHtml html={ data.slots.after } slug="plans-after" /> }
        </div>
    );
}
