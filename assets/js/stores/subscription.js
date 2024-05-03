import {defineStore} from 'pinia';
import {reactive, ref} from 'vue';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( {
        subscriptionList: ref( [] ),
        currentSubscriptionStatus: ref( 'all' ),
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
        setBlankSubscription() {
            this.currentSubscription = {};
            this.currentSubscription.meta_value = {};

            for (const field of this.fields) {
                switch (field.db_type) {
                    case 'post':
                        this.currentSubscription[field.db_key] = field.default;
                        break;

                    case 'meta':
                        this.currentSubscription.meta_value[field.db_key] = field.default;
                        break;

                    case 'meta_serialized':
                        let serializedValue = {};
                        if ( this.currentSubscription.meta_value.hasOwnProperty(field.db_key) ) {
                            serializedValue = this.currentSubscription.meta_value[field.db_key];
                            serializedValue[field.serialize_key] = field.default;
                        } else {
                            serializedValue[field.serialize_key] = field.default;
                        }

                        this.currentSubscription.meta_value[field.db_key] = serializedValue;

                        break;
                }
            }
        },
        getValueFromField(field) {
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

            const subscription = this.currentSubscription;
            let requestUrl = '/wp-json/wpuf/v1/wpuf_subscription';

            if ( subscription.ID ) {
                requestUrl += '/' + subscription.ID;
            }

            const requestOptions = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
                body: JSON.stringify( {subscription} )
            };

            return fetch( requestUrl, requestOptions )
                .then( ( response ) => response.json() )
                .catch( ( error ) => {
                    console.log( error );
            } );
        },
        modifyCurrentSubscription ( key, value, serializeKey = null ) {
            if (this.currentSubscription === null) {
                this.setBlankSubscription();

                return;
            }

            if (serializeKey === null) {
                // if key is not found in currentSubscription, then it must be in meta_value
                if (this.currentSubscription.hasOwnProperty( key )) {
                    this.currentSubscription[key] = value;
                } else {
                    this.setMetaValue ( key, value );
                }

                return;
            }

            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return;
            }

            this.currentSubscription.meta_value[key][serializeKey] = value;
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
        },
        toggleDraft( subscription ) {
            subscription.edit_single_row = true;
            subscription.edit_row_name = 'post_status';
            subscription.edit_row_value = subscription.post_status === 'draft' ? 'publish' : 'draft'
            ;
            this.setCurrentSubscription( subscription );

            return this.updateSubscription();
        },
        deleteSubscription( id ) {
            const requestOptions = {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
            };

            return fetch( '/wp-json/wpuf/v1/wpuf_subscription/' + id, requestOptions )
                .then( ( response ) => response.json() )
                .catch( ( error ) => {
                    console.log( error );
                } );
        },
        setSubscriptionsByStatus( status, offset = 0 ) {
            const queryParams = { 'per_page': wpufSubscriptions.perPage, 'offset': offset, 'post_status': status };
            apiFetch( {
                path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription', queryParams ),
                method: 'GET',
                headers: {
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
            } )
            .then( ( response ) => {
                if (response.success) {
                    this.subscriptionList = response.subscriptions;
                    this.currentSubscriptionStatus = status;
                }
            } )
            .catch( ( error ) => {
                console.log( error );
            } );
        }
    }
} );
