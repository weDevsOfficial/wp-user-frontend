/**
 * DESCRIPTION: "Usage" cell of the pack subscribers table: what the subscriber used of the
 * DESCRIPTION: pack and what is left (posts per type, featured items), on the user's current
 * DESCRIPTION: row only; "Details" opens every limit in the forms table look.
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Badge, Button, Modal } from '@wpuf/components';

const TH = 'px-4 font-normal';
const TD = 'px-4 py-2.5 align-middle text-[13px]';

// "3 of 10 used" / "2 used" (unlimited).
const usedText = ( line ) => {
	if ( -1 === line.limit ) {
		return null === line.used
			? __( 'Unlimited', 'wp-user-frontend' )
			/* translators: %d: number used */
			: sprintf( __( '%d used', 'wp-user-frontend' ), line.used );
	}

	/* translators: 1: number used, 2: limit */
	return sprintf( __( '%1$d of %2$d used', 'wp-user-frontend' ), line.used, line.limit );
};

const leftText = ( line ) => ( -1 === line.limit
	? __( 'Unlimited', 'wp-user-frontend' )
	/* translators: %d: number left */
	: sprintf( __( '%d left', 'wp-user-frontend' ), line.remaining ) );

// Share of the limit used (finite limits only).
const Bar = ( { line } ) => {
	if ( -1 === line.limit || line.limit <= 0 ) {
		return null;
	}

	const share = Math.min( 100, Math.round( ( line.used / line.limit ) * 100 ) );
	const tone = 0 === line.remaining ? 'bg-red-500' : share >= 80 ? 'bg-amber-500' : 'bg-primary';

	return (
		<div className="mt-1 h-1.5 w-28 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
			<div className={ `h-full rounded-full ${ tone }` } style={ { width: `${ share }%` } } />
		</div>
	);
};

const SubscriberUsage = ( { usage, name } ) => {
	const [ open, setOpen ] = useState( false );

	if ( ! usage ) {
		return (
			<span className="text-gray-400" title={ __( 'Usage shows on the subscriber\'s current subscription.', 'wp-user-frontend' ) }>
				&mdash;
			</span>
		);
	}

	const lines = [ ...( usage.items || [] ), ...( usage.featured ? [ usage.featured ] : [] ) ];

	if ( ! lines.length ) {
		return <span className="text-gray-400">&mdash;</span>;
	}

	// The tightest limit first: the line closest to running out.
	const first = [ ...lines ].sort( ( a, b ) => ( -1 === a.limit ? Infinity : a.remaining ) - ( -1 === b.limit ? Infinity : b.remaining ) )[ 0 ];

	return (
		<div className="flex items-center gap-3">
			<div className="min-w-0">
				<div className="whitespace-nowrap text-[13px] text-gray-900">
					<span className="font-medium">{ first.label }</span>{ ': ' }{ usedText( first ) }
					{ -1 !== first.limit && <span className="text-gray-500">{ ' · ' }{ leftText( first ) }</span> }
				</div>
				<Bar line={ first } />
			</div>
			<Button size="xs" variant="secondary" onClick={ () => setOpen( true ) }>
				{ __( 'Details', 'wp-user-frontend' ) }
			</Button>
			<Modal
				open={ open }
				onClose={ () => setOpen( false ) }
				/* translators: %s: subscriber name */
				title={ sprintf( __( 'Usage of %s', 'wp-user-frontend' ), name ) }
				className="sm:max-w-2xl"
			>
				<div data-wpuf-ui="" className="overflow-hidden rounded-[10px] border border-solid border-gray-200">
					<table className="w-full border-collapse text-left">
						<thead className="border-0 border-b border-solid border-gray-200 bg-white">
							<tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
								<th scope="col" className={ TH }>{ __( 'Item', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Used', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Remaining', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Allowed', 'wp-user-frontend' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ lines.map( ( line ) => (
								<tr key={ line.type } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0">
									<td className={ TD + ' font-medium text-gray-900' }>
										{ line.label }
										<Bar line={ line } />
									</td>
									<td className={ TD + ' text-gray-700' }>{ null === line.used ? '—' : line.used }</td>
									<td className={ TD }>
										{ -1 === line.limit
											? <span className="text-gray-500">{ __( 'Unlimited', 'wp-user-frontend' ) }</span>
											: <Badge tone={ 0 === line.remaining ? 'danger' : 'neutral' }>{ line.remaining }</Badge> }
									</td>
									<td className={ TD + ' text-gray-500' }>{ -1 === line.limit ? __( 'Unlimited', 'wp-user-frontend' ) : line.limit }</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
				<p className="m-0 mt-3 text-xs text-gray-500">
					{ usage.expire && sprintf(
						/* translators: %s: date */
						__( 'Expires: %s.', 'wp-user-frontend' ),
						usage.expire
					) }
					{ ' ' }
					{ __( 'For unlimited types, "Used" counts the posts made since the subscription started.', 'wp-user-frontend' ) }
				</p>
			</Modal>
		</div>
	);
};

export default SubscriberUsage;
