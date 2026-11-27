/**
 * Labels the subscriptions screens share: a pack's status (text + kit Badge
 * tone) and a billing period with its count, translated and pluralised.
 *
 * @since WPUF_SINCE
 */
import { __, _n } from '@wordpress/i18n';

const STATUSES = () => ( {
	publish: { label: __( 'Published', 'wp-user-frontend' ), tone: 'primary' },
	draft: { label: __( 'Draft', 'wp-user-frontend' ), tone: 'neutral' },
	pending: { label: __( 'Pending', 'wp-user-frontend' ), tone: 'warning' },
	future: { label: __( 'Future', 'wp-user-frontend' ), tone: 'info' },
	private: { label: __( 'Private', 'wp-user-frontend' ), tone: 'warning' },
	trash: { label: __( 'Trash', 'wp-user-frontend' ), tone: 'danger' },
} );

/**
 * A pack status: any other status reads as its own word (capitalised).
 *
 * @param {string} postStatus Post status.
 *
 * @return {Object} { label, tone }
 */
export function statusOf( postStatus ) {
	return STATUSES()[ postStatus ] || {
		label: postStatus ? postStatus.charAt( 0 ).toUpperCase() + postStatus.slice( 1 ) : __( 'Draft', 'wp-user-frontend' ),
		tone: 'neutral',
	};
}

/**
 * The pack's plans page highlight ("Highlight this plan"): its label, or ''
 * when the pack is not highlighted.
 *
 * @param {Object} subscription Pack (with meta_value).
 *
 * @return {string} Label.
 */
export function highlightOf( subscription ) {
	const meta = ( subscription && subscription.meta_value ) || {};

	if ( 'on' !== meta._wpuf_highlight ) {
		return '';
	}

	return meta._wpuf_highlight_label || __( 'Most popular', 'wp-user-frontend' );
}

/**
 * A billing period for a count: "week" / "weeks" (empty period = day).
 *
 * @param {string} period Stored period: day, week, month, year.
 * @param {number} count  Periods per cycle.
 *
 * @return {string} Label.
 */
export function periodLabel( period, count ) {
	switch ( period ) {
		case 'week':
			return _n( 'week', 'weeks', count, 'wp-user-frontend' );
		case 'month':
			return _n( 'month', 'months', count, 'wp-user-frontend' );
		case 'year':
			return _n( 'year', 'years', count, 'wp-user-frontend' );
		default:
			return _n( 'day', 'days', count, 'wp-user-frontend' );
	}
}

/**
 * Periods per cycle (1 when unset or 0).
 *
 * @param {*} value Stored `_billing_cycle_number`.
 *
 * @return {number} Count.
 */
export const cycleCount = ( value ) => Math.max( 1, parseInt( value, 10 ) || 1 );
