import {defineStore} from 'pinia';

export const useFieldDependencyStore = defineStore( 'fieldDependency', {
    state: () => ( {
        modifierFields: {}
    } ),
    actions: {
        getModifierFieldsValue( field ) {
            return this.modifierFields[field] || '';
        },
        setModifierFieldsValue( field, value ) {
            this.modifierFields[field] = value;
        },
        checkDependency( field, value ) {
            const fields = wpufSubscriptions.fieldDependency[field];

            if (!fields) {
                return;
            }

            for ( const f of fields ) {
                const {field: modifierField, condition, value: modifierValue} = f;

                if ( condition === '==' && value === modifierValue ) {
                    this.setModifierFieldsValue( modifierField, modifierValue );
                } else if ( condition === '!=' && value !== modifierValue ) {
                    this.setModifierFieldsValue( modifierField, modifierValue );
                } else {
                    this.setModifierFieldsValue( modifierField, '' );
                }
            }
        }
    },
} );
