/**
 * DESCRIPTION: Custom hook for subscription actions
 * DESCRIPTION: Consolidates common useDispatch patterns for subscription operations
 */
import { useDispatch } from '@wordpress/data';

/**
 * Hook for accessing subscription action dispatchers
 *
 * @return {Object} Subscription action methods
 */
export const useSubscriptionActions = () => {
	const dispatch = useDispatch( 'wpuf/subscriptions' );
	return {
		setItem: dispatch.setItem,
		setItemCopy: dispatch.setItemCopy,
		modifyItem: dispatch.modifyItem,
		updateItem: dispatch.updateItem,
		deleteItem: dispatch.deleteItem,
		fetchItems: dispatch.fetchItems,
		fetchCounts: dispatch.fetchCounts,
		setIsDirty: dispatch.setIsDirty,
		setIsUnsavedPopupOpen: dispatch.setIsUnsavedPopupOpen,
		setBlankItem: dispatch.setBlankItem,
		validateFields: dispatch.validateFields,
		setCurrentStatus: dispatch.setCurrentStatus,
		populateTaxonomyRestrictionData: dispatch.populateTaxonomyRestrictionData,
	};
};

export default useSubscriptionActions;
