/**
 * DESCRIPTION: What the subscriptions still need before visitors can buy a pack
 * DESCRIPTION: (payments on, packs page with its shortcode, payment page, a gateway...), each with
 * DESCRIPTION: its quick action. Only unmet checks show; nothing shows when all are met.
 *
 * @since WPUF_SINCE
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Button, Notice, notify } from '@wpuf/components';
import { addPageShortcode, fetchSetupChecks } from '../../api/subscription';
import { openRoute } from '../../../../app/client';

const TONES = { error: 'error', warning: 'warning', info: 'info' };
const ORDER = { error: 0, warning: 1, info: 2 };

const openSection = ( section ) => {
	const route = `/settings?tab=${ section }`;
	openRoute( route, 'admin.php?page=wp-user-frontend#' + route );
};

const SetupChecklist = () => {
	const [ checks, setChecks ] = useState( [] );
	const [ busy, setBusy ] = useState( '' );

	const load = useCallback( () => {
		fetchSetupChecks()
			.then( ( response ) => setChecks( Array.isArray( response?.checks ) ? response.checks : [] ) )
			// The list works without the checks: say nothing when they fail to load.
			.catch( () => setChecks( [] ) );
	}, [] );

	useEffect( () => {
		load();
	}, [ load ] );

	const open = checks
		.filter( ( check ) => ! check.ok )
		.sort( ( a, b ) => ( ORDER[ a.severity ] ?? 1 ) - ( ORDER[ b.severity ] ?? 1 ) );

	if ( ! open.length ) {
		return null;
	}

	const addShortcode = ( check ) => {
		setBusy( check.id );
		addPageShortcode( check.page, check.setting )
			.then( () => {
				notify( __( 'Shortcode added to the page.', 'wp-user-frontend' ), 'success' );
				load();
			} )
			.catch( ( error ) => notify( ( error && error.message ) || __( 'Could not change the page.', 'wp-user-frontend' ), 'error' ) )
			.finally( () => setBusy( '' ) );
	};

	return (
		<section className="mt-6 flex flex-col gap-3" aria-label={ __( 'Subscription setup', 'wp-user-frontend' ) }>
			{ open.map( ( check ) => (
				<Notice
					key={ check.id }
					tone={ TONES[ check.severity ] || 'warning' }
					title={ check.title }
					action={
						<div className="flex flex-wrap items-center gap-2">
							{ check.page > 0 && check.setting && (
								<>
									<Button size="sm" busy={ busy === check.id } onClick={ () => addShortcode( check ) }>
										{ __( 'Add the shortcode', 'wp-user-frontend' ) }
									</Button>
									<Button size="sm" variant="secondary" className="no-underline!" href={ `post.php?post=${ check.page }&action=edit` } target="_blank" rel="noopener noreferrer">
										{ __( 'Edit page', 'wp-user-frontend' ) }
									</Button>
								</>
							) }
							{ ( check.actions || [] ).map( ( action, index ) => (
								action.route ? (
									<Button key={ action.label } size="sm" variant={ 0 === index ? 'primary' : 'secondary' } onClick={ () => openRoute( action.route, 'admin.php?page=wp-user-frontend#' + action.route ) }>
										{ action.label }
									</Button>
								) : (
									<Button key={ action.label } size="sm" variant={ 0 === index ? 'primary' : 'secondary' } className="no-underline!" href={ action.url }>
										{ action.label }
									</Button>
								)
							) ) }
							{ check.section && (
								<Button size="sm" variant="secondary" onClick={ () => openSection( check.section ) }>
									{ __( 'Open payment settings', 'wp-user-frontend' ) }
								</Button>
							) }
						</div>
					}
				>
					{ check.description }
				</Notice>
			) ) }
			<p className="sr-only">
				{ /* translators: %d: number of setup steps left */ }
				{ sprintf( _n( '%d setup step left', '%d setup steps left', open.length, 'wp-user-frontend' ), open.length ) }
			</p>
		</section>
	);
};

export default SetupChecklist;
