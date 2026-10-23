/**
 * DESCRIPTION: SubscriptionField component for rendering individual form fields
 * DESCRIPTION: Handles various field types: input-text, input-number, textarea, switcher, select, inline, time-date, multi-select
 */
import { useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { Minus, Plus } from 'lucide-react';
import { DateTime, HelpTip, MultiSelect, NumberInput, ProBadge, Select, TextInput, Textarea, Toggle } from '@wpuf/components';
import ProTooltip from './ProTooltip';
import { subscriptionsData } from '../../boot';
import { openRoute } from '../../../../app/client';

const ON = [ true, 'yes', 'on', 'true', '1', 1 ];

/**
 * "Enable Recurring Payment" on: which active gateways can renew a pack, or a
 * warning with the way to fix it (boot `gateways`, Prerequisites::active_gateways()).
 *
 * @return {JSX.Element} Hint.
 */
const RecurringHint = () => {
	const gateways = subscriptionsData().gateways || [];
	const recurring = gateways.filter( ( gateway ) => gateway.recurring );

	if ( recurring.length ) {
		return (
			<span className="label-text-alt text-gray-500">
				{ sprintf(
					/* translators: %s: gateway names */
					__( 'Renewed automatically by: %s. Bank Payment cannot renew a plan.', 'wp-user-frontend' ),
					recurring.map( ( gateway ) => gateway.label ).join( ', ' )
				) }
			</span>
		);
	}

	return (
		<span className="label-text-alt text-amber-700">
			{ __( 'None of the active gateways can renew a plan, so buyers cannot pay for it. Turn on PayPal or Stripe.', 'wp-user-frontend' ) }{ ' ' }
			<a
				href="#/settings?tab=wpuf_payment"
				onClick={ ( event ) => {
					event.preventDefault();
					openRoute( '/settings?tab=wpuf_payment', 'admin.php?page=wp-user-frontend#/settings?tab=wpuf_payment' );
				} }
				className="font-medium text-amber-800 underline"
			>
				{ __( 'Payment settings', 'wp-user-frontend' ) }
			</a>
		</span>
	);
};

const has = ( object, key ) => !! object && Object.prototype.hasOwnProperty.call( object, key );

/**
 * develop's read rule (SectionInputField getFieldValue): the stored value, or
 * '' when the key is missing. Defaults only come from the blank item of a new
 * subscription, so a stored '', '0' or 0 stays what it is.
 *
 * @param {Object} field        Field definition.
 * @param {Object} subscription Current subscription.
 *
 * @return {*} Value.
 */
function readFieldValue( field, subscription ) {
	if ( ! subscription ) {
		return '';
	}

	const meta = subscription.meta_value;
	let value = '';

	switch ( field.db_type ) {
		case 'meta':
			value = has( meta, field.db_key ) ? meta[ field.db_key ] : '';
			break;

		case 'meta_serialized':
			value = has( meta, field.db_key ) && has( meta[ field.db_key ], field.serialize_key )
				? meta[ field.db_key ][ field.serialize_key ]
				: '';
			break;

		default:
			value = has( subscription, field.db_key ) ? subscription[ field.db_key ] : '';
	}

	return null === value || undefined === value ? '' : value;
}

// Text controls take strings; numbers from the REST read model stay as typed.
const asText = ( value ) => ( 'number' === typeof value ? String( value ) : value );

const SubscriptionField = ( { field, fieldId, subscription, onFieldChange } ) => {
	const wpufSubscriptions = subscriptionsData();

	// Get hidden fields from store
	const hiddenFields = useSelect(
		( select ) => {
			const store = select( 'wpuf/subscriptions-field-dependency' );
			return store ? store.getHiddenFields() : [];
		},
		[]
	);

	// Validation errors (develop: red border + message under the field).
	const errors = useSelect( ( select ) => select( 'wpuf/subscriptions' ).getErrors() || {}, [] );
	const error = errors[ fieldId ] || ( field.id && errors[ field.id ] );

	// Get taxonomy restriction values from store for multi-select fields
	const isViewRestriction = field.db_key === '_sub_view_allowed_term_ids';
	const { taxonomySelected, fullRestriction } = useSelect(
		( select ) => {
			if ( field.type !== 'multi-select' ) {
				return { taxonomySelected: [], fullRestriction: {} };
			}
			const store = select( 'wpuf/subscriptions' );
			const restriction = isViewRestriction
				? store.getTaxonomyViewRestriction()
				: store.getTaxonomyRestriction();
			return {
				taxonomySelected: restriction[ field.name ] || [],
				fullRestriction: restriction,
			};
		},
		[ field.type, field.name, field.db_key ]
	);

	const dispatch = useDispatch( 'wpuf/subscriptions-field-dependency' );
	const subscriptionsDispatch = useDispatch( 'wpuf/subscriptions' );

	// Convert term_fields array to options object for MultiSelect component
	const multiSelectOptions = useMemo( () => {
		if ( field.type !== 'multi-select' || ! field.term_fields ) {
			return {};
		}
		const termsList = Array.isArray( field.term_fields ) ? field.term_fields : Object.values( field.term_fields );
		const opts = {};
		termsList.forEach( ( term ) => {
			opts[ String( term.value ) ] = term.label;
		} );
		return opts;
	}, [ field.type, field.term_fields ] );

	// Check if field should be hidden
	const isHidden = hiddenFields.includes( fieldId );

	// Check if Pro feature
	const isPro = field.is_pro && ! wpufSubscriptions.isProActive;

	// Kept for the `wpuf.subscription.fieldComponent` filter contract.
	const getFieldValue = () => readFieldValue( field, subscription );

	// Parse expiration time value (e.g., "30 day" -> { value: "30", unit: "day" })
	const parseExpirationTime = ( timeString ) => {
		if ( ! timeString || typeof timeString !== 'string' ) {
			return { value: '', unit: 'day' };
		}
		const parts = timeString.trim().split( ' ' );
		return {
			value: parts[ 0 ] || '',
			unit: parts[ 1 ] || 'day',
		};
	};

	const value = getFieldValue();

	// Handle field value change
	// Stepper buttons beside a number field: one `step` up or down, within the
	// field's min / max (`min` or `validation.min`). Without a declared minimum
	// the floor is 0, or -1 for counts where -1 means unlimited (their help says
	// so, or the value is -1 already).
	const stepNumber = ( delta ) => {
		const step = parseFloat( field.step ) || 1;
		const declared = ( key ) => {
			const raw = undefined !== field[ key ] && '' !== field[ key ] ? field[ key ] : field.validation && field.validation[ key ];

			return undefined === raw || '' === raw || isNaN( parseFloat( raw ) ) ? null : parseFloat( raw );
		};
		const help = `${ field.tooltip || '' } ${ field.help_text || '' } ${ field.placeholder || '' }`;
		const current = parseFloat( value ) || 0;
		const floor = null !== declared( 'min' ) ? declared( 'min' ) : ( /-1/.test( help ) || -1 === current ? -1 : 0 );
		const ceiling = declared( 'max' );
		const places = ( n ) => ( String( n ).split( '.' )[ 1 ] || '' ).length;
		const decimals = Math.max( places( field.step || '' ), places( value || '' ) );
		let next = current + ( delta * step );

		next = Math.max( floor, null !== ceiling ? Math.min( ceiling, next ) : next );

		handleChange( decimals ? next.toFixed( decimals ) : String( Math.round( next ) ) );
	};

	const handleChange = ( newValue ) => {
		onFieldChange( field, newValue );

		// Handle field dependencies for switcher type
		if ( field.type === 'switcher' && dispatch ) {
			dispatch.toggleDependentFields( fieldId, newValue );
		}
	};

	// Switcher state, read as develop's Vue screen did ('on' / 'yes'; the
	// post_status switcher is on for 'private'). true / '1' are accepted too so
	// values saved by earlier React builds still show correctly.
	const isSwitcherOn = value === 'on' || value === 'yes' || value === true || value === '1'
		|| ( field.db_key === 'post_status' && value === 'private' );

	// develop stores 'on' / 'off' ('private' / 'publish' for post_status).
	const switchOn = field.db_key === 'post_status' ? 'private' : 'on';
	const switchOff = field.db_key === 'post_status' ? 'publish' : 'off';

	const toggleSwitcher = ( stored ) => {
		onFieldChange( field, stored );

		if ( dispatch ) {
			dispatch.toggleDependentFields( fieldId, stored === switchOn );
		}
	};

	if ( isHidden ) {
		return null;
	}

	// Allow Pro and third-party plugins to override field rendering for custom field types
	const customField = applyFilters(
		'wpuf.subscription.fieldComponent',
		null,
		field,
		{ fieldId, value, subscription, onFieldChange, handleChange, isPro, getFieldValue }
	);

	if ( customField ) {
		return customField;
	}

	const invalid = error ? 'border-red-500 focus-visible:border-red-500' : '';

	return (
		<div className="grid grid-cols-3 gap-6 px-6 py-3">
			{/* Label */}
			{ field.label && (
				<div className="flex items-center text-sm font-medium leading-5 text-gray-700">
					<label htmlFor={ field.name } dangerouslySetInnerHTML={ { __html: field.label } } />
					{ field.tooltip && <span className="ml-2"><HelpTip text={ field.tooltip } /></span> }
					{ isPro && (
						<span className="pro-icon-title relative pt-1 group ml-2">
							<ProBadge link={ false } />
							<ProTooltip />
						</span>
					) }
				</div>
			) }

			{/* Field Input */}
			<div className="col-span-2 relative group">
				{/* Pro overlay */}
				{ isPro && (
					<div className="hidden rounded-md border border-dashed border-emerald-600 group-hover:flex cursor-pointer absolute items-center justify-center bg-emerald-50/50 backdrop-blur-xs z-10 p-4 w-[104%] h-[180%] top-[-40%] left-[-2%]">
						<a
							href={ wpufSubscriptions.upgradeUrl || '#' }
							target="_blank"
							rel="noopener noreferrer"
							className="wpuf-button button-upgrade-to-pro inline-flex items-center px-4 py-2 bg-emerald-600 focus:bg-emerald-700 hover:bg-emerald-700 text-white rounded-md gap-2 font-medium text-sm"
						>
							{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }
						</a>
					</div>
				) }

				{ field.type === 'input-text' && (
					<TextInput
						id={ field.name }
						name={ field.name }
						value={ asText( value ) }
						placeholder={ field.placeholder || '' }
						onChange={ handleChange }
						disabled={ isPro }
						aria-invalid={ error ? 'true' : undefined }
						className={ invalid }
					/>
				) }

				{ field.type === 'input-number' && (
					<div className="inline-flex items-center gap-2">
						{ [ -1, 1 ].map( ( delta ) => (
							<button
								key={ delta }
								type="button"
								className={ `inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-md border border-solid border-gray-300 bg-white text-gray-600 hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-50 ${ delta < 0 ? 'order-first' : 'order-last' }` }
								onClick={ () => stepNumber( delta ) }
								disabled={ isPro }
								aria-label={ delta < 0 ? __( 'Decrease', 'wp-user-frontend' ) : __( 'Increase', 'wp-user-frontend' ) }
								aria-controls={ field.name }
							>
								{ delta < 0 ? <Minus size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> : <Plus size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> }
							</button>
						) ) }
						<NumberInput
							id={ field.name }
							name={ field.name }
							value={ asText( value ) }
							placeholder={ field.placeholder || '' }
							min={ field.min }
							step={ field.step }
							onChange={ handleChange }
							disabled={ isPro }
							aria-invalid={ error ? 'true' : undefined }
							className={ `w-28 text-center ${ invalid || '' }` }
						/>
					</div>
				) }

				{ field.type === 'textarea' && (
					<Textarea
						id={ field.name }
						name={ field.name }
						value={ asText( value ) }
						placeholder={ field.placeholder || '' }
						rows={ 3 }
						onChange={ handleChange }
						disabled={ isPro }
						aria-invalid={ error ? 'true' : undefined }
						className={ invalid }
					/>
				) }

				{ field.type === 'switcher' && (
					<Toggle
						id={ field.name }
						value={ isSwitcherOn ? switchOn : switchOff }
						checkedValue={ switchOn }
						uncheckedValue={ switchOff }
						onChange={ toggleSwitcher }
						disabled={ isPro }
					/>
				) }

				{ field.type === 'select' && field.options && (
					<Select
						id={ field.name }
						value={ asText( value ) }
						options={ field.options }
						onChange={ handleChange }
						disabled={ isPro }
						className={ invalid }
					/>
				) }

				{ field.type === 'multi-select' && field.term_fields && (
					<MultiSelect
						options={ multiSelectOptions }
						value={ taxonomySelected.map( String ) }
						onChange={ ( selected ) => {
							const merged = {
								...fullRestriction,
								[ field.name ]: selected,
							};

							if ( isViewRestriction ) {
								subscriptionsDispatch.setTaxonomyViewRestriction( merged );
							} else {
								subscriptionsDispatch.setTaxonomyRestriction( merged );
							}
							subscriptionsDispatch.setIsDirty( true );
						} }
						placeholder={ field.placeholder || __( 'Select terms...', 'wp-user-frontend' ) }
						disabled={ isPro }
					/>
				) }

				{/* Inline - compound field with multiple inputs */}
				{/* develop SectionInnerField: each part in a half-width box (py-4 pl-3 pr-4) */}
				{ field.type === 'inline' && field.fields && (
					<div className="-ml-3 flex justify-between -mr-3">
						{ Object.entries( field.fields ).map( ( [ subFieldKey, subField ] ) => {
							// develop: the stored value or ''. Older packs only have the
							// combined "number period" value (task 1.3).
							let subFieldValue = readFieldValue( { db_type: 'meta', db_key: subField.db_key }, subscription );

							if ( '' === subFieldValue && field.name === 'expiration-time' && subscription?.meta_value?._post_expiration_time ) {
								const parsed = parseExpirationTime( subscription.meta_value._post_expiration_time );
								subFieldValue = subField.key_id === 'expiration_value' ? parsed.value : parsed.unit;
							}

							// Each part is stored under its own key (_post_expiration_number /
							// _post_expiration_period), which is what the REST save reads.
							const handleSubFieldChange = ( newValue ) => {
								onFieldChange( subField, newValue );
							};

							// A sub-field's own error (planAmountErrors: cycle, trial length).
							const subError = errors[ subField.id ] || errors[ subField.name ];

							if ( subField.type === 'input-number' ) {
								return (
									<div key={ subFieldKey } className="py-4 pl-3 pr-4 w-1/2">
									<NumberInput
										id={ subField.name }
										name={ subField.name }
										value={ asText( subFieldValue ) }
										placeholder={ subField.placeholder || '' }
										min={ subField.min }
										step={ subField.step }
										onChange={ handleSubFieldChange }
										disabled={ isPro }
										aria-invalid={ subError ? 'true' : undefined }
									/>
									{ subError && <span className="mt-1 block text-sm text-red-500">{ subError.message }</span> }
									</div>
								);
							}

							if ( subField.type === 'select' && subField.options ) {
								return (
									<div key={ subFieldKey } className="py-4 pl-3 pr-4 w-1/2">
									<Select
										id={ subField.name }
										value={ asText( subFieldValue ) }
										options={ subField.options }
										onChange={ handleSubFieldChange }
										disabled={ isPro }
									/>
									</div>
								);
							}

							return null;
						} ) }
					</div>
				) }

				{/* Publish time: YYYY-MM-DD HH:mm:ss, like the branch save path expects (task 1.21) */}
				{ field.type === 'time-date' && (
					<DateTime
						id={ field.name }
						withTime
						value={ asText( value ) }
						onChange={ handleChange }
						disabled={ isPro }
						className={ invalid }
					/>
				) }

				{/* develop: description, then the validation message */}
				{ field.description && (
					<div className="label">
						<span className="label-text-alt">{ field.description }</span>
					</div>
				) }

				{/* A field's hint (`hint`, optional `hint_link` { label, route | url }) */}
				{ field.hint && (
					<div className="label">
						<span className="label-text-alt text-gray-500">
							{ field.hint }
							{ field.hint_link && ( field.hint_link.route || field.hint_link.url ) && (
								<>
									{ ' ' }
									<a
										href={ field.hint_link.url || '#' + field.hint_link.route }
										target={ field.hint_link.url ? '_blank' : undefined }
										rel={ field.hint_link.url ? 'noopener noreferrer' : undefined }
										onClick={ field.hint_link.route ? ( event ) => {
											event.preventDefault();
											openRoute( field.hint_link.route, 'admin.php?page=wp-user-frontend#' + field.hint_link.route );
										} : undefined }
										className="font-medium text-primary no-underline hover:underline"
									>
										{ field.hint_link.label }
									</a>
								</>
							) }
						</span>
					</div>
				) }

				{ 'recurring_pay' === field.name && ON.includes( value ) && (
					<div className="label"><RecurringHint /></div>
				) }

				{ error && (
					<div className="label" role="alert">
						<span className="label-text-alt text-red-500">{ error.message }</span>
					</div>
				) }
			</div>
		</div>
	);
};

export default SubscriptionField;
