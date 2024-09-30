import {defineStore} from 'pinia';
import {reactive, ref} from 'vue';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( {
        subscriptionList: ref( [] ),
        isUpdating: ref( false ),
        isSubscriptionLoading: ref( false ),
        isDirty: ref( false ),
        isUnsavedPopupOpen: ref( false ),
        currentSubscriptionStatus: ref( 'all' ),
        currentSubscriptionCopy: ref( null ),
        currentSubscription: ref( null ),
        errors: reactive( {} ),
        updateError: reactive( {
            status: false,
            message: '',
        } ),
        allCount: ref( {} ),
        taxonomyRestriction: ref( {} ),
        currentPageNumber: ref( 1 ),
    } ),
    getters: {
        fieldNames: () => {
            const sections = wpufSubscriptions.fields;
            const names = [];

            for (const section in sections) {
                if (!sections.hasOwnProperty( section )) {
                    continue;
                }
                for (const subsection in sections[section]) {
                    if (!sections[section].hasOwnProperty( subsection )) {
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
                if (!sections.hasOwnProperty( section )) {
                    continue;
                }
                for (const subsection in sections[section]) {
                    if (!sections[section].hasOwnProperty( subsection )) {
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
        setCurrentSubscriptionCopy() {
            this.currentSubscriptionCopy = this.subscription;
        },
        setBlankSubscription() {
            this.currentSubscription = {};
            this.currentSubscription.meta_value = {};

            for (const field of this.fields) {
                if (field.hasOwnProperty('type') && field.type === 'inline') {
                    for (const innerField in field.fields) {
                        this.populateDefaultValue( field.fields[innerField] );
                    }
                } else {
                    this.populateDefaultValue( field );
                }
            }
        },
        populateDefaultValue( field ) {
            switch (field.db_type) {
                case 'post':
                    this.currentSubscription[field.db_key] = field.default;
                    break;

                case 'meta':
                    this.currentSubscription.meta_value[field.db_key] = field.default;
                    break;

                case 'meta_serialized':
                    let serializedValue = {};
                    if (this.currentSubscription.meta_value.hasOwnProperty( field.db_key )) {
                        serializedValue = this.currentSubscription.meta_value[field.db_key];
                        serializedValue[field.serialize_key] = field.default;
                    } else {
                        serializedValue[field.serialize_key] = field.default;
                    }

                    this.currentSubscription.meta_value[field.db_key] = serializedValue;

                    break;
            }
        },
        getValueFromField( field ) {
            switch (field.type) {
                case 'input-text':
                case 'input-number':
                case 'textarea':
                case 'switcher':
                case 'select':
                    return document.querySelector( '#' + field.id ).value;
                case 'time-date':

                    return document.querySelector( '#dp-input-' + field.id ).value;
                default:

                    return '';
            }
        },
        async updateSubscription() {
            if (this.currentSubscription === null) {
                return false;
            }

            this.isUpdating = true;

            let allTaxonomies = [];

            for (const [key, taxonomy] of Object.entries( this.taxonomyRestriction )) {
                allTaxonomies = allTaxonomies.concat( taxonomy );
            }

            const taxonomyIntValue = allTaxonomies.map( ( item ) => parseInt( item ) );

            const uniqueTaxonomies = [...new Set( taxonomyIntValue )];

            // custom meta key for taxonomy restriction
            this.setMetaValue( '_sub_allowed_term_ids', uniqueTaxonomies );

            const subscription = this.currentSubscription;
            let requestUrl = '/wp-json/wpuf/v1/wpuf_subscription';

            if (subscription.ID) {
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

            this.isDirty = false;

            return fetch( requestUrl, requestOptions )
            .then( ( response ) => response.json() )
            .catch( ( error ) => {
                this.setError('fetch', 'An error occurred while updating the subscription.');
            } ).finally( () => {
                this.isUpdating = false;
            } );
        },
        modifyCurrentSubscription( key, value, serializeKey = null ) {
            if (this.currentSubscription === null) {
                this.setBlankSubscription();

                return;
            }

            this.isDirty = true;

            if (serializeKey === null) {
                // if key is not found in currentSubscription, then it must be in meta_value
                if (this.currentSubscription.hasOwnProperty( key )) {
                    this.currentSubscription[key] = value;
                } else {
                    this.setMetaValue( key, value );
                }

                return;
            }

            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return;
            }

            if ( (typeof this.currentSubscription.meta_value[key] === 'string') && key === 'additional_cpt_options' ) {
                this.currentSubscription.meta_value[key] = {};
            }

            this.currentSubscription.meta_value[key][serializeKey] = value;
        },
        getMetaValue( key ) {
            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return '';
            }

            return this.currentSubscription.meta_value[key];
        },
        setMetaValue( key, value ) {
            this.currentSubscription.meta_value[key] = value;

            this.isDirty = true;
        },
        getSerializedMetaValue( key, serializeKey ) {
            if (!this.currentSubscription.meta_value.hasOwnProperty( key )) {
                return '';
            }

            const serializedValue = this.getMetaValue( key );

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
        resetErrors() {
            this.errors = {};
        },
        hasError() {
            for (const item in this.errors) {
                if (this.errors[item]) {
                    return true;
                }
            }

            return false;
        },
        validateQuickEdit() {
            const planName = this.currentSubscription.post_title;

            if (planName === '') {
                this.setError( 'planName', __( 'This field is required', 'wp-user-frontend' ) );
            }

            // error if plan name contains #. PayPal doesn't allow # in package name
            if (planName.includes( '#' )) {
                this.setError( 'planName', __( '# is not supported in plan name', 'wp-user-frontend' ) );
            }
        },
        validateEdit() {
            const subscription = this.currentSubscription;

            const fields = wpufSubscriptions.fields;

            for (const section in fields) {
                if (!fields.hasOwnProperty( section )) {
                    continue;
                }

                for (const subsection in fields[section]) {
                    if (!fields[section].hasOwnProperty( subsection )) {
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
                                value = '';
                                break;
                        }

                        // error if plan name contains #. PayPal doesn't allow # in package name
                        if ( fieldData.id === 'plan-name' && value.includes( '#' )) {
                            this.setError( field, __( '# is not supported in plan name', 'wp-user-frontend' ) );
                        }

                        if (fieldData.is_required && value === '') {
                            this.setError( field, __( fieldData.label + ' is required', 'wp-user-frontend' ) );
                        }
                    }
                }
            }
        },
        validateFields( mode = 'update' ) {
            this.resetErrors();

            switch (mode) {
                case 'quickEdit':
                    this.validateQuickEdit();
                    break;
                default:
                    this.validateEdit();
                    break;
            }

            return !this.hasError();
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
        changeSubscriptionStatus( subscription ) {
            subscription.edit_single_row = true;

            this.setCurrentSubscription( subscription );

            return this.updateSubscription();
        },
        async setSubscriptionsByStatus( status, offset = 0 ) {
            this.isSubscriptionLoading = true;

            const queryParams = {'per_page': wpufSubscriptions.perPage, 'offset': offset, 'post_status': status};
            return apiFetch( {
                path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription', queryParams ),
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
            } )
            .then( ( response ) => {
                if (response.success) {
                    this.currentSubscriptionStatus = status;
                    this.subscriptionList = response.subscriptions;
                }

                return response;
            } )
            .catch( ( error ) => {
                console.log( error );
            } )
            .finally( () => {
                this.isSubscriptionLoading = false;
            });
        },
        async getSubscriptionCount( status = 'all' ) {
            let path = '/wp-json/wpuf/v1/wpuf_subscription/count';

            if (status !== 'all') {
                path += '/' + status;
            }

            return apiFetch( {
                path: addQueryArgs( path ),
                method: 'GET',
                headers: {
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
            } )
            .then( ( response ) => {
                if (response.success) {
                    this.allCount = response.count;
                }
            } )
            .catch( ( error ) => {
                console.log( error );
            } );
        },
        getReadableBillingAmount( subscription, returnAsHtml = false ) {
            if (this.isRecurring( subscription )) {
                const cyclePeriod = subscription.meta_value.cycle_period === '' ? __( 'day', 'wp-user-frontend' ) : subscription.meta_value.cycle_period;
                const expireAfter = (parseInt( subscription.meta_value._billing_cycle_number ) === 0 || parseInt( subscription.meta_value._billing_cycle_number ) === 1) ? '' : ' ' + subscription.meta_value._billing_cycle_number + ' ';

                if (returnAsHtml) {
                    return wpufSubscriptions.currencySymbol + subscription.meta_value.billing_amount + ' <span class="wpuf-text-sm wpuf-text-gray-500">per ' + expireAfter + ' ' + cyclePeriod + '(s)</span>';
                } else {
                    return wpufSubscriptions.currencySymbol + subscription.meta_value.billing_amount + ' per ' + expireAfter + ' ' + cyclePeriod + '(s)';
                }
            } else {
                if (parseInt( subscription.meta_value.billing_amount ) === 0 || subscription.meta_value.billing_amount === '') {
                    return __( 'Free', 'wp-user-frontend' );
                } else {
                    return wpufSubscriptions.currencySymbol + subscription.meta_value.billing_amount;
                }
            }
        },
        isRecurring( subscription ) {
            return subscription.meta_value.recurring_pay === 'on' || subscription.meta_value.recurring_pay === 'yes'
        }
    }
} );
