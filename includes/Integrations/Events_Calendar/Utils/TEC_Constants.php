<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Utils;

/**
 * TEC Constants
 *
 * Contains all TEC-specific constants used throughout the integration
 *
 * @since 4.1.9
 */
class TEC_Constants {

    /**
     * TEC date format constant
     *
     * @since 4.1.9
     */
    const TEC_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * TEC date fields that need special handling
     *
     * @since 4.1.9
     */
    const EVENT_DATE_FIELDS = [
        '_EventStartDate',
        '_EventEndDate',
        '_EventStartDateUTC',
        '_EventEndDateUTC',
    ];

    /**
     * TEC post types
     *
     * @since 4.1.9
     */
    const TEC_POST_TYPES = [
        'tribe_events',
    ];

    /**
     * TEC event meta fields
     *
     * @since 4.1.9
     */
    const EVENT_META_FIELDS = [
        '_EventStartDate',
        '_EventEndDate',
        '_EventStartDateUTC',
        '_EventEndDateUTC',
        '_EventAllDay',
        '_EventDuration',
        '_EventCost',
        '_EventCurrencySymbol',
        '_EventURL',
        '_EventTimezone',
        '_EventShowMap',
        '_EventShowMapLink',
    ];

    /**
     * TEC form template ID
     *
     * @since 4.1.9
     */
    const FORM_TEMPLATE_ID = 'post_form_template_events_calendar';

    /**
     * TEC minimum version for v6 compatibility
     *
     * @since 4.1.9
     */
    const TEC_V6_MIN_VERSION = '6.0.0';

    /**
     * TEC minimum version for v5 compatibility
     *
     * @since 4.1.9
     */
    const TEC_V5_MIN_VERSION = '5.0.0';

    /**
     * Default event duration in seconds (9 hours)
     *
     * @since 4.1.9
     */
    const DEFAULT_EVENT_DURATION = 32400;

    /**
     * All-day event duration in seconds (23:59:59)
     *
     * @since 4.1.9
     */
    const ALL_DAY_EVENT_DURATION = 86399;
}
