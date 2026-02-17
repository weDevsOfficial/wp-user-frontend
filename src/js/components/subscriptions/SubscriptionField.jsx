/**
 * DESCRIPTION: SubscriptionField component for rendering individual form fields
 * DESCRIPTION: Handles various field types: input-text, input-number, textarea, switcher, select, inline
 */
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import ProBadge from './ProBadge';
import ProTooltip from './ProTooltip';

const SubscriptionField = ( { field, fieldId, subscription, onFieldChange } ) => {
	const wpufSubscriptions = window.wpufSubscriptions || {};

	// Get hidden fields from store
	const hiddenFields = useSelect(
		( select ) => {
			const store = select( 'wpuf/subscriptions-field-dependency' );
			return store ? store.getHiddenFields() : [];
		},
		[]
	);

	const dispatch = useDispatch( 'wpuf/subscriptions-field-dependency' );

	// Check if field should be hidden
	const isHidden = hiddenFields.includes( fieldId );

	// Check if Pro feature
	const isPro = field.is_pro && ! wpufSubscriptions.isProActive;

	// Get field value based on db_type
	const getFieldValue = () => {
		if ( ! subscription ) {
			return field.default || '';
		}

		switch ( field.db_type ) {
			case 'meta':
				return subscription.meta_value?.[ field.db_key ] || field.default || '';

			case 'meta_serialized':
				if ( subscription.meta_value?.[ field.db_key ] ) {
					return subscription.meta_value[ field.db_key ][ field.serialize_key ] || field.default || '';
				}
				return field.default || '';

			case 'post':
				return subscription[ field.db_key ] || field.default || '';

			default:
				return field.default || '';
		}
	};

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
	const handleChange = ( newValue ) => {
		onFieldChange( field, newValue );

		// Handle field dependencies for switcher type
		if ( field.type === 'switcher' && dispatch ) {
			dispatch.toggleDependentFields( fieldId, newValue );
		}
	};

	// Convert switcher value to boolean
	const isSwitcherOn = value === 'on' || value === 'yes' || value === true;

	if ( isHidden ) {
		return null;
	}

	return (
		<div className="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-p-4">
			{/* Label */}
			{ field.label && (
				<div className="wpuf-flex wpuf-items-center wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">
					<label htmlFor={ field.name } dangerouslySetInnerHTML={ { __html: field.label } } />
					{ field.tooltip && (
						<span className="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10" data-tip={ field.tooltip }>
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
								<path d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z" stroke="#9CA3AF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
							</svg>
						</span>
					) }
					<span className="pro-icon-title wpuf-relative wpuf-pt-1 wpuf-group">
						<ProBadge isPro={ field.is_pro } />
						<ProTooltip isPro={ field.is_pro } />
					</span>
				</div>
			) }

			{/* Field Input */}
			<div className="wpuf-col-span-2 wpuf-relative wpuf-group">
				{/* Pro overlay */}
				{ isPro && (
					<div className="wpuf-hidden wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-emerald-200 group-hover:wpuf-flex wpuf-cursor-pointer wpuf-absolute wpuf-items-center wpuf-justify-center wpuf-bg-emerald-50/50 wpuf-backdrop-blur-sm wpuf-z-10 wpuf-p-4 wpuf-w-[104%] wpuf-h-[180%] wpuf-top-[-40%] wpuf-left-[-2%]">
						<a
							href={ wpufSubscriptions.upgradeUrl || '#' }
							target="_blank"
							rel="noopener noreferrer"
							className="wpuf-button button-upgrade-to-pro wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-bg-emerald-600 focus:wpuf-bg-emerald-700 hover:wpuf-bg-emerald-700 wpuf-text-white wpuf-rounded-md wpuf-gap-2 wpuf-font-medium wpuf-text-sm"
						>
							{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }
						</a>
					</div>
				) }

				{/* Input Text */}
				{ field.type === 'input-text' && (
					<input
						type="text"
						id={ field.name }
						name={ field.name }
						value={ value }
						placeholder={ field.placeholder || '' }
						onChange={ ( e ) => handleChange( e.target.value ) }
						disabled={ isPro }
						className="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none !wpuf-border-gray-300"
					/>
				) }

				{/* Input Number */}
				{ field.type === 'input-number' && (
					<input
						type="number"
						id={ field.name }
						name={ field.name }
						value={ value }
						placeholder={ field.placeholder || '' }
						min={ field.min }
						step={ field.step }
						onChange={ ( e ) => handleChange( e.target.value ) }
						onKeyDown={ ( e ) => {
							const allowedKeys = [ 'Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', '.' ];
							if ( ! allowedKeys.includes( e.key ) && isNaN( Number( e.key ) ) ) {
								e.preventDefault();
							}
						} }
						disabled={ isPro }
						className="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none !wpuf-border-gray-300"
					/>
				) }

				{/* Textarea */}
				{ field.type === 'textarea' && (
					<textarea
						id={ field.name }
						name={ field.name }
						value={ value }
						placeholder={ field.placeholder || '' }
						rows="3"
						onChange={ ( e ) => handleChange( e.target.value ) }
						disabled={ isPro }
						className="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none !wpuf-border-gray-300"
					/>
				) }

				{/* Switcher */}
				{ field.type === 'switcher' && (
					<button
						type="button"
						id={ field.name }
						name={ field.name }
						onClick={ () => handleChange( ! isSwitcherOn ) }
						disabled={ isPro }
						className={ `${ isSwitcherOn ? 'wpuf-bg-primary' : 'wpuf-bg-gray-200' } placeholder:wpuf-text-gray-400 wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out` }
						role="switch"
						aria-checked={ isSwitcherOn }
					>
						<span
							aria-hidden="true"
							className={ `${ isSwitcherOn ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0' } wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out` }
						/>
					</button>
				) }

				{/* Select */}
				{ field.type === 'select' && field.options && (
					<select
						id={ field.name }
						name={ field.name }
						value={ value }
						onChange={ ( e ) => handleChange( e.target.value ) }
						disabled={ isPro }
						className="wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-border-gray-300"
					>
						{ Object.entries( field.options ).map( ( [ key, label ] ) => (
							<option key={ key } value={ key }>
								{ label }
							</option>
						) ) }
					</select>
				) }

				{/* Inline - compound field with multiple inputs */}
				{ field.type === 'inline' && field.fields && (
					<div className="wpuf-flex wpuf-gap-2 wpuf-items-center">
						{ Object.entries( field.fields ).map( ( [ subFieldKey, subField ] ) => {
							// Get sub-field value
							let subFieldValue = subField.default || '';

							if ( subscription ) {
								// For expiration_time, parse the combined value
								if ( field.name === 'expiration-time' && subscription.meta_value?._post_expiration_time ) {
									const parsed = parseExpirationTime( subscription.meta_value._post_expiration_time );
									subFieldValue = subField.key_id === 'expiration_value' ? parsed.value : parsed.unit;
								} else if ( subField.db_type === 'meta' ) {
									subFieldValue = subscription.meta_value?.[ subField.db_key ] || subField.default || '';
								}
							}

							// Handle sub-field change
							const handleSubFieldChange = ( newValue ) => {
								// For inline fields, we need to construct the combined value
								if ( field.name === 'expiration-time' ) {
									// Get the other sub-field's value
									const otherSubFieldKey = subFieldKey === 'expiration_value' ? 'expiration_unit' : 'expiration_value';
									const otherSubField = field.fields[ otherSubFieldKey ];
									let otherValue = otherSubField.default;

									if ( subscription && subscription.meta_value?._post_expiration_time ) {
										const parsed = parseExpirationTime( subscription.meta_value._post_expiration_time );
										otherValue = otherSubFieldKey === 'expiration_value' ? parsed.value : parsed.unit;
									}

									// Combine values: "value unit" or "unit value" depending on which changed
									const combinedValue = subFieldKey === 'expiration_value'
										? `${ newValue } ${ otherValue }`
										: `${ otherValue } ${ newValue }`;

									onFieldChange( { ...field, db_key: '_post_expiration_time' }, combinedValue );
								} else {
									onFieldChange( subField, newValue );
								}
							};

							// Render input-number sub-field
							if ( subField.type === 'input-number' ) {
								return (
									<input
										key={ subFieldKey }
										type="number"
										id={ subField.name }
										name={ subField.name }
										value={ subFieldValue }
										placeholder={ subField.placeholder || '' }
										min={ subField.min }
										step={ subField.step }
										onChange={ ( e ) => handleSubFieldChange( e.target.value ) }
										onKeyDown={ ( e ) => {
											const allowedKeys = [ 'Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', '.' ];
											if ( ! allowedKeys.includes( e.key ) && isNaN( Number( e.key ) ) ) {
												e.preventDefault();
											}
										} }
										disabled={ isPro }
										className="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none !wpuf-border-gray-300"
									/>
								);
							}

							// Render select sub-field
							if ( subField.type === 'select' && subField.options ) {
								return (
									<select
										key={ subFieldKey }
										id={ subField.name }
										name={ subField.name }
										value={ subFieldValue }
										onChange={ ( e ) => handleSubFieldChange( e.target.value ) }
										disabled={ isPro }
										className="wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-border-gray-300"
									>
										{ Object.entries( subField.options ).map( ( [ key, label ] ) => (
											<option key={ key } value={ key }>
												{ label }
											</option>
										) ) }
									</select>
								);
							}

							return null;
						} ) }
					</div>
				) }

				{/* Description */}
				{ field.description && (
					<div className="label">
						<span className="label-text-alt">{ field.description }</span>
					</div>
				) }
			</div>
		</div>
	);
};

export default SubscriptionField;
