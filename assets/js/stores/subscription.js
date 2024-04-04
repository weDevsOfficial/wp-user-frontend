import {defineStore} from 'pinia';
import {reactive, ref} from 'vue';
import {__} from '@wordpress/i18n';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( {
        currentSubscription: ref( null ),
        errors: reactive( {} ),
        updateError: reactive( {
            status: false,
            message: '',
        } ),
    } ),
    getters: {
        fieldNames: () => {
            const sections = wpufSubscriptions.fields;
            const names = [];

            for (const section in sections) {
                if ( !sections.hasOwnProperty( section ) ) {
                    continue;
                }
                for (const subsection in sections[section]) {
                    if ( !sections[section].hasOwnProperty( subsection ) ) {
                        continue;
                    }
                    for (const field in sections[section][subsection]) {
                        names.push( field );
                    }
                }
            }

            return names;
        },
        fields: () => {
            const sections = wpufSubscriptions.fields;
            const fields = [];

            for (const section in sections) {
                if ( !sections.hasOwnProperty( section ) ) {
                    continue;
                }
                for (const subsection in sections[section]) {
                    if ( !sections[section].hasOwnProperty( subsection ) ) {
                        continue;
                    }
                    for (const field in sections[section][subsection]) {
                        fields.push( sections[section][subsection][field] );
                    }
                }
            }

            return fields;
        },
    },
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },
        getValueFromField(field) {
            console.log(field.type);
            console.log(field.id);
            switch (field.type) {
                case 'input-text':
                case 'input-number':
                case 'textarea':
                case 'switcher':
                case 'select':

                    return document.querySelector('#' + field.id).value;
                case 'time-date':

                    return document.querySelector('#dp-input-' + field.id).value;
                default:

                    return '';
            }
        },
        updateSubscription() {
            if ( this.currentSubscription === null ) {
                return false;
            }

            for ( const field of this.fields ) {

                let value = '';

                if (field.type === 'inline') {

                    for ( const innerField in field.fields ) {
                        value = this.getValueFromField( innerField );
                    }

                    continue;
                } else {
                    value = this.getValueFromField( field );
                }

                console.log(value);

            }


            const subscription = this.currentSubscription;

            const requestOptions = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
                body: JSON.stringify( {subscription} )
            };

            return fetch(
                '/wp-json/wpuf/v1/wpuf_subscription/' + subscription.id,
                requestOptions )
                .then( ( response ) => response.json() )
                .catch( ( error ) => {
                    console.log( error );
                } );
        },
        modifyCurrentSubscription ( key, value, serializeKey ) {
            // if key is not found in currentSubscription, then it must be in meta_value
            if (this.currentSubscription.hasOwnProperty( key )) {
                this.currentSubscription[key] = value;
            } else if (this.currentSubscription.meta_value.hasOwnProperty( key )) {
                this.setMetaValue ( key, value )
            } else {
                if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                    return -1;
                }

                this.currentSubscription.meta_value[key][serializeKey] = value;
            }
        },
        getMetaValue (key) {
            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return '';
            }

            return this.currentSubscription.meta_value[key];
        },
        setMetaValue ( key, value ) {
            this.currentSubscription.meta_value[key] = value;
        },
        getSerializedMetaValue(key, serializeKey) {
            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return '';
            }

            const serializedValue = this.getMetaValue(key);

            if (!serializedValue.hasOwnProperty( serializeKey )) {
                return '';
            }

            return serializedValue[serializeKey];
        },
        setError( field, message ) {
            this.errors[field] = {
                status: true,
                message: message,
            };
        },
        resetErrors () {
            this.errors = {};
        },
        hasError () {
            for (const item in this.errors) {
                if (this.errors[item]) {
                    return true;
                }
            }

            return false;
        },
        validateQuickEdit () {
            const planName = this.currentSubscription.post_title;

            if ( planName === '' ) {
                this.setError( 'planName', __( 'This field is required', 'wp-user-frontend' ) );
            }

            // error if plan name contains #. PayPal doesn't allow # in package name
            if ( planName.includes('#') ) {
                this.setError( 'planName', __( '# is not supported in plan name', 'wp-user-frontend' ) );
            }
        },
        validateEdit () {
            const subscription = this.currentSubscription;

            const fields = wpufSubscriptions.fields;

            for (const section in fields) {
                if ( !fields.hasOwnProperty( section ) ) {
                    continue;
                }

                for (const subsection in fields[section]) {
                    if ( !fields[section].hasOwnProperty( subsection ) ) {
                        continue;
                    }

                    for (const field in fields[section][subsection]) {
                        const fieldData = fields[section][subsection][field];
                        let value = '';

                        switch (fieldData.db_type) {
                            case 'meta':
                                value = subscription.meta_value[fieldData.db_key];
                                break;
                            case 'meta_serialized':
                                value = subscription.meta_value[fieldData.db_key];
                                break;
                            case 'post':
                                value = subscription[fieldData.db_key];
                                break;
                            default:
                                break;
                        }

                        if ( fieldData.is_required && value === '' ) {
                            this.setError( field, __( 'This field is required', 'wp-user-frontend' ) );
                        }
                    }
                }
            }
        },
        validateFields( mode = 'update' ) {
            this.resetErrors();

            switch ( mode ) {
                case 'quickEdit':
                    this.validateQuickEdit();
                    break;
                default:
                    this.validateEdit();
                    break;
            }

            return !this.hasError();
        }
    }
} )
