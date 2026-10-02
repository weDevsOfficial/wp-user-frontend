import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../store';

/**
 * Central hook for accessing form builder state and dispatchers.
 *
 * @return {Object}
 */
export default function useFormBuilder() {
    const state = useSelect( ( select ) => {
        const store = select( STORE_NAME );

        return {
            post: store.getPost(),
            formFields: store.getFormFields(),
            panelSections: store.getPanelSections(),
            fieldSettings: store.getFieldSettings(),
            notifications: store.getNotifications(),
            settings: store.getSettings(),
            integrations: store.getIntegrations(),
            currentPanel: store.getCurrentPanel(),
            editingFieldId: store.getEditingFieldId(),
            editingField: store.getEditingField(),
            editingFieldConfig: store.getEditingFieldConfig(),
            indexToInsert: store.getIndexToInsert(),
            isDirty: store.getIsDirty(),
            isProActive: store.getIsProActive(),
            formType: store.getFormType(),
            i18n: store.getI18n(),
            showCustomFieldTooltip: store.getShowCustomFieldTooltip(),
        };
    }, [] );

    const dispatchers = useDispatch( STORE_NAME );

    return { ...state, ...dispatchers };
}
