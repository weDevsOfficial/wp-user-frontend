<script setup>
import {__} from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';
import {computed, watch} from 'vue';

const subscriptionStore = useSubscriptionStore();
const currentSubscription = subscriptionStore.currentSubscription;

const isRecurring = computed(() => {
    return currentSubscription.meta_value.recurring_pay === 'on' || currentSubscription.meta_value.recurring_pay === 'yes';
});

const getBillingAmountText = computed(() => {
    if (parseFloat( currentSubscription.meta_value.billing_amount ) === 0) {
        return __( 'Free', 'wp-user-frontend' );
    } else {
        if ( isRecurring.value ) {
            const cyclePeriod = currentSubscription.meta_value.cycle_period === '' ? __( 'day', 'wp-user-frontend' ) : currentSubscription.meta_value.cycle_period;
            const expireAfter = currentSubscription.meta_value._billing_cycle_number !== '0' ? ' ' + currentSubscription.meta_value._billing_cycle_number + ' ' : '';

            return wpufSubscriptions.currencySymbol + currentSubscription.meta_value.billing_amount + ' <span class="wpuf-text-sm wpuf-text-gray-500">per ' + expireAfter + ' ' + cyclePeriod + '(s)</span>';
        }

        return wpufSubscriptions.currencySymbol + currentSubscription.meta_value.billing_amount;
    }
});

const billingAmount = getBillingAmountText;
</script>
<template>
    <div class="wpuf-mt-4 wpuf-border wpuf-border-gray-200">
        <dl class="wpuf-mx-auto wpuf-grid bg-gray-900/5 wpuf-grid-cols-4 wpuf-border-b-2 wpuf-border-dashed wpuf-bg-white wpuf-p-2">
            <div class="wpuf-flex wpuf-col-span-2 wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-py-2 wpuf-px-6" :title="'id: ' + currentSubscription.ID">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Plan', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">
                    {{ currentSubscription.post_title }}
                </dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-px-4 wpuf-py-2">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Payment', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900" v-html="subscriptionStore.getReadableBillingAmount(currentSubscription, true)"></dd>
            </div>
            <div v-if="isRecurring" class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-px-4 wpuf-py-5">
                <dt class="wpuf-text-sm wpuf-italic wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500 wpuf-flex wpuf-items-center wpuf-justify-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 19C20 19.5523 20.4477 20 21 20C21.5523 20 22 19.5523 22 19L20 19ZM21 15.375L22 15.375L22 14.375H21V15.375ZM12 21L12 22L12 21ZM4.06195 13.0013C3.99361 12.4532 3.49394 12.0644 2.9459 12.1327C2.39786 12.201 2.00898 12.7007 2.07732 13.2488L4.06195 13.0013ZM20.3458 15.375L20.3458 14.375L20.3458 14.375L20.3458 15.375ZM17.375 14.375C16.8227 14.375 16.375 14.8227 16.375 15.375C16.375 15.9273 16.8227 16.375 17.375 16.375L17.375 14.375ZM4.00001 5.00002C4.00001 4.44773 3.55229 4.00002 3.00001 4.00002C2.44772 4.00002 2.00001 4.44773 2.00001 5.00002L4.00001 5.00002ZM3.00001 8.62502L2.00001 8.62502L2.00001 9.62502H3.00001V8.62502ZM3.65421 8.62502L3.65421 9.62502L3.65421 9.62502L3.65421 8.62502ZM12 3.00002L12 2.00002L12 3.00002ZM6.62501 9.62502C7.17729 9.62502 7.62501 9.1773 7.62501 8.62502C7.62501 8.07273 7.17729 7.62502 6.62501 7.62502L6.62501 9.62502ZM19.9381 10.9988C20.0064 11.5468 20.5061 11.9357 21.0541 11.8673C21.6022 11.799 21.991 11.2993 21.9227 10.7513L19.9381 10.9988ZM12.8552 9.58595C13.1788 10.0335 13.804 10.134 14.2515 9.81034C14.699 9.48673 14.7995 8.86159 14.4759 8.41404L12.8552 9.58595ZM12.5 7C12.5 6.44771 12.0523 6 11.5 6C10.9477 6 10.5 6.44771 10.5 7H12.5ZM10.5 17C10.5 17.5523 10.9477 18 11.5 18C12.0523 18 12.5 17.5523 12.5 17L10.5 17ZM10.1448 14.414C9.82121 13.9665 9.19606 13.866 8.74852 14.1896C8.30098 14.5133 8.20051 15.1384 8.52412 15.5859L10.1448 14.414ZM22 19L22 15.375L20 15.375L20 19L22 19ZM12 20C7.92115 20 4.55392 16.9466 4.06195 13.0013L2.07732 13.2488C2.69257 18.1827 6.89973 22 12 22L12 20ZM19.4189 14.9998C18.2313 17.9335 15.3558 20 12 20L12 22C16.1983 22 19.79 19.4132 21.2727 15.7502L19.4189 14.9998ZM21 14.375H20.3458V16.375H21V14.375ZM20.3458 14.375L17.375 14.375L17.375 16.375L20.3458 16.375L20.3458 14.375ZM2.00001 5.00002L2.00001 8.62502L4.00001 8.62502L4.00001 5.00002L2.00001 5.00002ZM4.58115 9.00023C5.76867 6.06656 8.6442 4.00002 12 4.00002L12 2.00002C7.80171 2.00002 4.21 4.58686 2.72728 8.2498L4.58115 9.00023ZM3.00001 9.62502H3.65421V7.62502H3.00001V9.62502ZM3.65421 9.62502L6.62501 9.62502L6.62501 7.62502L3.65421 7.62502L3.65421 9.62502ZM12 4.00002C16.0789 4.00001 19.4461 7.05347 19.9381 10.9988L21.9227 10.7513C21.3074 5.81736 17.1003 2.00001 12 2.00002L12 4.00002ZM11.5 11C10.4518 11 10 10.3556 10 10H8C8 11.8535 9.78676 13 11.5 13V11ZM10 10C10 9.64441 10.4518 9 11.5 9V7C9.78676 7 8 8.14644 8 10H10ZM11.5 9C12.1534 9 12.6379 9.28548 12.8552 9.58595L14.4759 8.41404C13.8286 7.51891 12.6973 7 11.5 7V9ZM11.5 13C12.5482 13 13 13.6444 13 14H15C15 12.1464 13.2132 11 11.5 11V13ZM10.5 7V8H12.5V7H10.5ZM10.5 16L10.5 17L12.5 17L12.5 16L10.5 16ZM11.5 15C10.8466 15 10.3621 14.7145 10.1448 14.414L8.52412 15.5859C9.17138 16.4811 10.3027 17 11.5 17L11.5 15ZM13 14C13 14.3556 12.5482 15 11.5 15V17C13.2132 17 15 15.8535 15 14H13Z" fill="rgb(107 114 128)"/>
                    </svg>
                    &nbsp;&nbsp;{{ __( 'Recurring', 'wp-user-frontend' )}}
                </dt>
            </div>
        </dl>
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 bg-gray-900/5 wpuf-bg-white wpuf-p-2">
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-bg-white wpuf-px-4 wpuf-py-2">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Subscribers', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-flex wpuf-items-center wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 10.8C13.9882 10.8 15.6 9.18822 15.6 7.2C15.6 5.21177 13.9882 3.6 12 3.6C10.0118 3.6 8.4 5.21177 8.4 7.2C8.4 9.18822 10.0118 10.8 12 10.8Z" fill="#0F172A"/>
                        <path d="M3.6 21.6C3.6 16.9608 7.36081 13.2 12 13.2C16.6392 13.2 20.4 16.9608 20.4 21.6H3.6Z" fill="#0F172A"/>
                    </svg>
                    &nbsp;&nbsp;{{ currentSubscription.subscribers }}
                </dd>
            </div>
        </dl>
    </div>
</template>
