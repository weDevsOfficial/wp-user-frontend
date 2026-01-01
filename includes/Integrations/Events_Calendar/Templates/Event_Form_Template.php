<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Templates;

use DateTime;
use DateTimeZone;
use WeDevs\Wpuf\Admin\Forms\Form_Template;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Constants;

/**
 * The Events Calendar Integration Template
 *
 * @since 4.1.9
 */
class Event_Form_Template extends Form_Template {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->enabled       = class_exists( 'Tribe__Events__Main' );
        $this->title         = __( 'The Events Calendar', 'wp-user-frontend' );
        $this->description   = __(
            'Form for creating events. The Events Calendar plugin is required.', 'wp-user-frontend'
        );
        $this->image         = WPUF_ASSET_URI . '/images/templates/post.svg';
        $this->form_fields   = $this->get_form_fields();
        $this->form_settings = $this->get_form_settings();
    }

    /**
     * Get form fields
     *
     * @since 4.1.9
     *
     * @return array
     */
    public function get_form_fields() {
        $form_fields = [
            [
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => __( 'Event Title', 'wp-user-frontend' ),
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => __( 'Please enter your event title', 'wp-user-frontend' ),
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid('wpuf_', true),
                'is_new'     => true
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_content',
                'required'            => 'yes',
                'label'               => __( 'Event details', 'wp-user-frontend' ),
                'name'                => 'post_content',
                'is_meta'             => 'no',
                'help'                => __( 'Write the full description of your event', 'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '5',
                'cols'                => '25',
                'placeholder'         => '',
                'default'             => '',
                'rich'                => 'yes',
                'insert_image'        => 'yes',
                'word_restriction'    => '',
                'wpuf_cond'           => $this->conditionals,
                'text_editor_control' => [],
                'id'                   => uniqid('wpuf_', true),
                'is_new'               => true
            ],
            [
                'input_type' => 'date',
                'template'   => 'date_field',
                'required'   => 'yes',
                'label'      => __( 'Event Start', 'wp-user-frontend' ),
                'name'       => '_EventStartDate',
                'is_meta'    => 'yes',
                'help'       => '',
                'width'      => 'large',
                'format'     => 'yy-mm-dd',
                'time'       => 'yes',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Event Start UTC', 'wp-user-frontend' ),
                'name'       => '_EventStartDateUTC',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'default'    => $this->get_utc_time(),
                'css'        => 'wpuf_hidden_field',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Event End UTC', 'wp-user-frontend' ),
                'name'       => '_EventEndDateUTC',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'default'    => $this->get_utc_time( '+2 hours' ),
                'css'        => 'wpuf_hidden_field',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'date',
                'template'   => 'date_field',
                'required'   => 'yes',
                'label'      => __( 'Event End', 'wp-user-frontend' ),
                'name'       => '_EventEndDate',
                'is_meta'    => 'yes',
                'help'       => '',
                'width'      => 'large',
                'format'     => 'yy-mm-dd',
                'time'       => 'yes',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'checkbox',
                'template'   => 'checkbox_field',
                'required'   => 'no',
                'label'      => __( 'All Day Event', 'wp-user-frontend' ),
                'name'       => '_EventAllDay',
                'is_meta'    => 'yes',
                'options'    => [
                    '1' => __( 'All Day Event', 'wp-user-frontend' ),
                ],
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'url',
                'template'   => 'website_url',
                'required'   => 'no',
                'label'      => __( 'Event Website', 'wp-user-frontend' ),
                'name'       => '_EventURL',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'size'       => 40,
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Currency Symbol', 'wp-user-frontend' ),
                'name'       => '_EventCurrencySymbol',
                'is_meta'    => 'yes',
                'size'       => 40,
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Cost', 'wp-user-frontend' ),
                'name'       => '_EventCost',
                'is_meta'    => 'yes',
                'wpuf_cond'  => $this->conditionals,
                'id'         => uniqid('wpuf_', true),
                'is_new'    => true
            ],
            [
                'input_type'   => 'image_upload',
                'template'     => 'featured_image',
                'count'        => '1',
                'required'     => 'no',
                'label'        => __( 'Featured Image', 'wp-user-frontend' ),
                'button_label' => __( 'Featured Image', 'wp-user-frontend' ),
                'name'         => 'featured_image',
                'is_meta'      => 'no',
                'help'         => __( 'Upload the main image of your event', 'wp-user-frontend' ),
                'css'          => '',
                'max_size'     => '1024',
                'wpuf_cond'    => $this->conditionals,
                'id'           => uniqid('wpuf_', true),
                'is_new'      => true
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_excerpt',
                'required'            => 'no',
                'label'               => __( 'Short Description', 'wp-user-frontend' ),
                'name'                => 'post_excerpt',
                'is_meta'             => 'no',
                'help'                => __( 'Provide a short description of this event (optional)',
                    'wp-user-frontend' ),
                'css'                 => '',
                'rows'                => '5',
                'cols'                => '25',
                'placeholder'         => '',
                'default'             => '',
                'rich'                => 'no',
                'wpuf_cond'           => $this->conditionals,
                'text_editor_control' => [],
                'id'                   => uniqid('wpuf_', true),
                'is_new'               => true
            ],
            [
                'input_type'  => 'text',
                'template'    => 'post_tags',
                'required'    => 'no',
                'label'       => __( 'Event Tags', 'wp-user-frontend' ),
                'name'        => 'tags',
                'is_meta'     => 'no',
                'help'        => __( 'Separate tags with commas.', 'wp-user-frontend' ),
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals,
                'id'          => uniqid('wpuf_', true),
                'is_new'     => true
            ],
        ];

        /**
         * Opportunity to modify TEC form fields before they're used
         *
         * This filter allows developers to add, modify, or remove form fields from
         * the Events Calendar form template. Useful for custom event fields,
         * integration with other plugins, or custom field types.
         *
         * @since 4.1.9
         *
         * @param array $form_fields The array of form field definitions
         * @param Event_Form_Template $this The form template instance
         */
        $form_fields = apply_filters( 'wpuf_tec_form_fields', $form_fields, $this );

        return $form_fields;
    }

    /**
     * Get UTC time
     *
     * @since 4.1.9
     *
     * @return string
     */
    private function get_utc_time( $offset = '0 hours' ) {
        $local = new DateTime( current_time( 'Y-m-d H:i:s' ), new DateTimeZone( wp_timezone_string() ) );
        $utc   = clone $local;

        $utc->setTimezone( new DateTimeZone( 'UTC' ) );
        $utc->modify( $offset );

        return $utc->format('Y-m-d H:i:s');
    }

    /**
     * Get form settings
     *
     * @since 4.1.9
     *
     * @return array
     */
    public function get_form_settings() {
        return [
            'post_type'        => 'tribe_events',
            'post_status'      => 'publish',
            'default_cat'      => '-1',
            'guest_post'       => 'false',
            'message_restrict' => __( 'This page is restricted. Please Log in / Register to view this page.',
                'wp-user-frontend' ),
            'redirect_to'      => 'post',
            'comment_status'   => 'open',
            'submit_text'      => __( 'Create Event', 'wp-user-frontend' ),
            'edit_post_status' => 'publish',
            'edit_redirect_to' => 'same',
            'update_message'   => sprintf(
                // translators: %1$s is opening link tag, %2$s is closing link tag
                __(
                    'Event has been updated successfully. %1$sView event%2$s',
                    'wp-user-frontend'
                ),

                '<a target="_blank" href="{link}">',
                    '</a>'
            ),
            'edit_url'         => '',
            'update_text'      => __( 'Update Event', 'wp-user-frontend' ),
            'form_template'    => 'post_form_template_events_calendar',
            'notification'     => [
                'new'          => 'on',
                'new_to'       => get_option( 'admin_email' ),
                'new_subject'  => 'New event has been created',
                'new_body'     => 'Hi,
A new event has been created in your site {sitename} ({siteurl}).

Here are the details:
Event Title: {post_title}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}',
                'edit'         => 'on',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => 'An event has been updated',
                'edit_body'    => 'Hi Admin,
The event "{post_title}" has been updated.

Here are the event details:
Event Title: {post_title}
Event Start: {custom__EventStartDate}
Event End: {custom__EventEndDate}
Event Website: {custom__EventURL}
Event Cost: {custom__EventCost}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Event URL: {permalink}
Edit URL: {editlink}'
            ],
        ];
    }
}
