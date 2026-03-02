/**
 * DESCRIPTION: Loading spinner component for async operations
 * DESCRIPTION: Displays centered spinning loader for subscription data fetching
 */

/**
 * Loading spinner component
 *
 * @return {JSX.Element} Loading spinner element
 */
const LoadingSpinner = () => {
	return (
		<div className="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
			<div className="wpuf-animate-spin wpuf-h-12 wpuf-w-12 wpuf-border-4 wpuf-border-green-500 wpuf-border-t-transparent wpuf-rounded-full"></div>
		</div>
	);
};

export default LoadingSpinner;
