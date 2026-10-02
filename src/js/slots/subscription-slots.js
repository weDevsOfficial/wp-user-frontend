/**
 * DESCRIPTION: Central SlotFill definitions for subscription extensibility
 * DESCRIPTION: Pro and third-party plugins use Fill components to inject UI at these Slot points
 */
import { createSlotFill } from '@wordpress/components';

// Form-level slots
export const SubscriptionFormFooter = createSlotFill( 'WpufSubscriptionFormFooter' );
export const SubscriptionFormSidebar = createSlotFill( 'WpufSubscriptionFormSidebar' );

// Section/Tab-level slots
export const SubscriptionTabContent = createSlotFill( 'WpufSubscriptionTabContent' );
export const SubscriptionAfterSubsection = createSlotFill( 'WpufSubscriptionAfterSubsection' );

// List-level slots
export const SubscriptionListActions = createSlotFill( 'WpufSubscriptionListActions' );
export const SubscriptionBoxFooter = createSlotFill( 'WpufSubscriptionBoxFooter' );
