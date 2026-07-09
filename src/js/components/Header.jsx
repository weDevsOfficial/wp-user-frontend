/**
 * DESCRIPTION: Header component for WP User Frontend admin pages
 * DESCRIPTION: Displays logo, version, upgrade link, and support links
 */
import { __ } from '@wordpress/i18n';

const Header = ( { utm = 'wpuf-header' } ) => {
	const wpuf = window.wpuf_admin_script;
	const logoUrl = wpuf.asset_url + '/images/wpuf-icon-circle.svg';
	const upgradeUrl = wpuf.upgradeUrl + '?utm_source=' + utm + '&utm_medium=wpuf-header';
	const supportUrl = wpuf.support_url;

	return (
		<div className="wpuf-p-[20px] wpuf-flex wpuf-justify-between wpuf-items-center wpuf-border-b-2 wpuf-border-gray-100">
			<div className="wpuf-flex wpuf-justify-start wpuf-items-center">
				<img src={ logoUrl } alt="WPUF Icon" className="wpuf-w-12 wpuf-mr-4" />
				<h2 className="wpuf-text-2xl wpuf-leading-7 wpuf-font-bold wpuf-m-0">WP User Frontend</h2>
				<span className="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20">
					v{ wpuf.version }
				</span>
				{ ! wpuf.isProActive && (
					<a
						href={ upgradeUrl }
						target="_blank"
						rel="noreferrer"
						className="wpuf-btn-primary wpuf-flex wpuf-ml-4 wpuf-p-2"
					>
						{ __( 'Upgrade to PRO', 'wp-user-frontend' ) }
					</a>
				) }
			</div>
			<div className="wpuf-flex wpuf-justify-end wpuf-items-center wpuf-w-2/4">
				<span
					id="wpuf-headway-icon"
					className="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-rounded-full wpuf-p-1 wpuf-shadow-sm hover:wpuf-bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
				></span>
				<a
					className="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-canny-link wpuf-text-center wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-shadow-sm hover:wpuf-bg-slate-100 focus:wpuf-bg-slate-100"
					target="_blank"
					href="https://wpuf.canny.io/ideas"
					rel="noreferrer"
				>
					💡 { __( 'Submit Ideas', 'wp-user-frontend' ) }
				</a>
				<a
					href={ supportUrl }
					target="_blank"
					rel="noreferrer"
					className="wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white"
				>
					{ __( 'Support ', 'wp-user-frontend' ) }
					&nbsp;&nbsp;
					<span className="dashicons dashicons-businessperson"></span>
				</a>
			</div>
		</div>
	);
};

export default Header;
