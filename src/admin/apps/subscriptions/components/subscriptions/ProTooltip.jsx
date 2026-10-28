/**
 * develop's Pro tooltip (assets/js/components/ProTooltip.vue), shown by the
 * legacy admin.css rule `span.pro-icon-title:hover .wpuf-pro-field-tooltip`.
 * Rendered only while Pro is not active.
 */
import { __ } from '@wordpress/i18n';
import { subscriptionsData } from '../../boot';

const CHECK = 'M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z';

const FEATURES = [
	__( '24/7 Priority Support', 'wp-user-frontend' ),
	__( '20+ Premium Modules', 'wp-user-frontend' ),
	__( 'User Activity and Reports', 'wp-user-frontend' ),
	__( 'Private Messaging Option', 'wp-user-frontend' ),
	__( 'License for 20 websites', 'wp-user-frontend' ),
];

const ProTooltip = () => {
	const wpufSubscriptions = subscriptionsData();

	if ( wpufSubscriptions.isProActive ) {
		return null;
	}

	return (
		<div className="wpuf-pro-field-tooltip" style={ { left: '50%', top: '-0.5em' } }>
			<h3 className="tooltip-header">{ __( 'Available in Pro. Also enjoy:', 'wp-user-frontend' ) }</h3>
			<ul>
				{ FEATURES.map( ( feature ) => (
					<li key={ feature } className="flex items-center">
						<span className="tooltip-check">
							<svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path fillRule="evenodd" clipRule="evenodd" d={ CHECK } fill="white" />
							</svg>
						</span>
						{ ' ' + feature }
					</li>
				) ) }
			</ul>
			<div className="pro-link">
				<a
					href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&utm_medium=popup"
					target="_blank"
					rel="noopener noreferrer"
					className="wpuf-button button-upgrade-to-pro flex items-center w-[calc(100%-2rem)] justify-around"
				>
					{ __( 'Upgrade to PRO', 'wp-user-frontend' ) }
				</a>
			</div>
			<i style={ { left: '50%', top: '100%', transform: 'initial' } }></i>
		</div>
	);
};

export default ProTooltip;
