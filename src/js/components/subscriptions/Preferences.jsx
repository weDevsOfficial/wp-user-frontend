/**
 * DESCRIPTION: Preferences component for subscription settings
 * DESCRIPTION: Manages subscription appearance preferences like button color
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const Preferences = () => {
	const [ buttonColor, setButtonColor ] = useState( '' );
	const [ isSaving, setIsSaving ] = useState( false );

	const { addNotice } = useDispatch( 'wpuf/subscriptions-notice' );

	// Load settings on mount
	useEffect( () => {
		const loadSettings = async () => {
			try {
				const response = await apiFetch( {
					path: '/wpuf/v1/subscription-settings',
					method: 'GET',
				} );

				if ( response.button_color !== undefined ) {
					setButtonColor( response.button_color || '' );
				}
			} catch ( error ) {
				console.error( '[Preferences] Error loading settings:', error );
			}
		};

		loadSettings();
	}, [] );

	// Save settings
	const handleSave = useCallback( async () => {
		setIsSaving( true );

		try {
			const response = await apiFetch( {
				path: '/wpuf/v1/subscription-settings',
				method: 'POST',
				data: {
					button_color: buttonColor,
				},
			} );

			addNotice( {
				content: __( 'Preferences saved successfully', 'wp-user-frontend' ),
				type: 'success',
			} );
		} catch ( error ) {
			console.error( '[Preferences] Error saving settings:', error );
			addNotice( {
				content: __( 'Failed to save settings', 'wp-user-frontend' ),
				type: 'error',
			} );
		} finally {
			setIsSaving( false );
		}
	}, [ buttonColor, addNotice ] );

	// Handle color input change from native color picker
	const handleColorInputChange = useCallback( ( e ) => {
		setButtonColor( e.target.value );
	}, [] );

	// Clear color
	const handleClearColor = useCallback( () => {
		setButtonColor( '#079669' );
	}, [] );

	// Handle text input change
	const handleTextInputChange = useCallback( ( e ) => {
		setButtonColor( e.target.value );
	}, [] );

	return (
		<div className="wpuf-p-10 wpuf-max-w-4xl">
			<div className="wpuf-mb-6">
				<h2 className="wpuf-text-2xl wpuf-font-semibold wpuf-text-gray-900 wpuf-mb-2">
					{ __( 'Subscription Preferences', 'wp-user-frontend' ) }
				</h2>
				<p className="wpuf-text-sm wpuf-text-gray-600">
					{ __( 'Configure subscription appearance preferences', 'wp-user-frontend' ) }
				</p>
			</div>

			<div>
				<div className="wpuf-space-y-6">
					{/* Button Appearance Section */}
					<div>
						<h3 className="wpuf-text-lg wpuf-font-medium wpuf-text-gray-900 wpuf-mb-4">
							{ __( 'Color Settings', 'wp-user-frontend' ) }
						</h3>

						<div>
							<div className="wpuf-flex wpuf-items-center wpuf-mb-1">
								<label className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">
									{ __( 'Button Color', 'wp-user-frontend' ) }
								</label>
								<span
									className="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
									data-tip={ __( 'Custom color for subscription buttons. Leave empty to use the default primary color from theme.', 'wp-user-frontend' ) }
								>
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
										<path
											d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
											stroke="#9CA3AF"
											strokeWidth="2"
											strokeLinecap="round"
											strokeLinejoin="round"
										/>
									</svg>
								</span>
							</div>
							<div className="wpuf-flex wpuf-items-center wpuf-gap-4">
								<div className="wpuf-flex wpuf-flex-col wpuf-gap-2">
									<div className="wpuf-flex wpuf-items-center wpuf-gap-2">
										<input
											value={ buttonColor }
											onChange={ handleColorInputChange }
											type="color"
											className="wpuf-w-12 wpuf-h-10 wpuf-rounded-md !wpuf-bg-transparent !wpuf-border-0 wpuf-cursor-pointer"
										/>
										<input
											value={ buttonColor }
											onChange={ handleTextInputChange }
											type="text"
											placeholder={ __( 'Default', 'wp-user-frontend' ) }
											className="wpuf-rounded-md wpuf-border-gray-300 wpuf-shadow-sm focus:wpuf-border-primary focus:wpuf-ring-primary wpuf-text-sm wpuf-w-32"
										/>
										{ buttonColor && (
											<button
												type="button"
												onClick={ handleClearColor }
												className="wpuf-text-xs wpuf-text-gray-500 hover:wpuf-text-gray-700 wpuf-underline"
											>
												{ __( 'Clear', 'wp-user-frontend' ) }
											</button>
										) }
									</div>
								</div>
								<div className="wpuf-ml-4 wpuf-w-32">
									<button
										type="button"
										style={ buttonColor ? { backgroundColor: buttonColor } : {backgroundColor: "#079669"} }
										onMouseOver={ ( e ) => {
											if ( buttonColor ) {
												e.target.style.filter = 'brightness(0.9)';
											}
										} }
										onMouseOut={ ( e ) => {
											if ( buttonColor ) {
												e.target.style.filter = 'brightness(1)';
											}
										} }
										className="wpuf-subscription-buy-btn wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm wpuf-ring-0 wpuf-transition-all wpuf-duration-200 wpuf-leading-6"
									>
										{ __( 'Buy Now', 'wp-user-frontend' ) }
									</button>
								</div>
							</div>
							<p className="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-2">
								{ __( 'Leave empty to use the default primary color from your Tailwind configuration.', 'wp-user-frontend' ) }
							</p>
						</div>
					</div>

					{/* Save Button */}
					<div className="wpuf-pt-6 wpuf-flex wpuf-justify-end">
						<button
							onClick={ handleSave }
							disabled={ isSaving }
							type="button"
							className="wpuf-rounded-md wpuf-bg-primary wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-primary disabled:wpuf-opacity-50"
						>
							{ isSaving ? __( 'Saving...', 'wp-user-frontend' ) : __( 'Save Preferences', 'wp-user-frontend' ) }
						</button>
					</div>
				</div>
			</div>
		</div>
	);
};

export default Preferences;
