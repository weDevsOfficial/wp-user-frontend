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
    },
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },
        updateSubscription() {
            if ( this.currentSubscription === null ) {
                return false;
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
        modifySubscription ( key, value ) {
            this.currentSubscription[key] = value;
        },
        setMetaValue ( key, value ) {
            this.currentSubscription.meta_value[key] = value;
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
        validateFields( mode = 'edit' ) {
            this.resetErrors();

            if ( mode === 'quickEdit' ) {
                const planName = this.currentSubscription.post_title;
                const date = new Date(this.currentSubscription.post_date);
                const isPrivate = this.currentSubscription.post_status === 'private';

                console.log(planName);

                if ( planName === '' ) {
                    this.setError( 'planName', __( 'This field is required', 'wp-user-frontend' ) );
                }

                // error if plan name contains #. PayPal doesn't allow # in package name
                if ( planName.includes('#') ) {
                    this.setError( 'planName', __( '# is not supported in plan name', 'wp-user-frontend' ) );
                }
            }

            return !this.hasError();

            const fields = this.fieldNames;
            const subscription = this.currentSubscription;

            // fields.forEach( ( field ) => {
            //     // if ( !subscription[field] ) {
            //     //     this.setError( field, 'This field is required' );
            //     // }
            // } );



            return false;

            // return !this.hasError();
        }
    }
} )
