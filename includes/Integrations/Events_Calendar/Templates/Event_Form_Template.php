<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Templates;

use DateTime;
use DateTimeZone;
use WeDevs\Wpuf\Admin\Forms\Form_Template;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Constants;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper;

/**
 * The Events Calendar Integration Template
 *
 * @since WPUF_SINCE
 */
class Event_Form_Template extends Form_Template {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->enabled     = TEC_Helper::is_tec_active();
        $this->title       = __( 'The Events Calendar', 'wp-user-frontend' );
        $this->description = __( 'Form for creating events. The Events Calendar plugin is required.', 'wp-user-frontend' );
        $this->image       = WPUF_ASSET_URI . '/images/templates/post.svg';
        $this->form_fields = $this->get_form_fields();
        $this->form_settings = $this->get_form_settings();
    }

    /**
     * Get form fields
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_form_fields() {
        return [
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
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Event Start', 'wp-user-frontend' ),
                'name'       => '_EventStartDate',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'format'     => 'yy-mm-dd',
                'time'       => 'yes',
                'default'    => current_time( 'Y-m-d H:i:s' ),
                'css'        => 'wpuf_hidden_field',
                'wpuf_cond'  => $this->conditionals,
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
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'yes',
                'label'      => __( 'Event End', 'wp-user-frontend' ),
                'name'       => '_EventEndDate',
                'is_meta'    => 'yes',
                'width'      => 'large',
                'format'     => 'yy-mm-dd',
                'time'       => 'yes',
                'default'    => date( 'Y-m-d H:i:s', strtotime( '+2 hours', current_time( 'timestamp' ) ) ),
                'css'        => 'wpuf_hidden_field',
                'wpuf_cond'  => $this->conditionals,
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
            ],
            [
                'input_type' => 'select',
                'template'   => 'dropdown_field',
                'required'   => 'no',
                'label'      => __( 'Venue', 'wp-user-frontend' ),
                'name'       => '_EventVenueID',
                'is_meta'    => 'yes',
                'options'    => $this->get_venue_options(),
                'wpuf_cond'  => $this->conditionals,
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Create New Venue', 'wp-user-frontend' ),
                'name'       => 'venue_name',
                'is_meta'    => 'yes',
                'help'       => __( 'Leave empty to use existing venue above', 'wp-user-frontend' ),
                'css'        => '',
                'placeholder' => __( 'Enter venue name', 'wp-user-frontend' ),
                'default'    => '',
                'size'       => '40',
                'wpuf_cond'  => $this->conditionals,
            ],
            [
                'input_type' => 'select',
                'template'   => 'dropdown_field',
                'required'   => 'no',
                'label'      => __( 'Organizer', 'wp-user-frontend' ),
                'name'       => '_EventOrganizerID',
                'is_meta'    => 'yes',
                'options'    => $this->get_organizer_options(),
                'wpuf_cond'  => $this->conditionals,
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Create New Organizer', 'wp-user-frontend' ),
                'name'       => 'organizer_name',
                'is_meta'    => 'yes',
                'help'       => __( 'Leave empty to use existing organizer above', 'wp-user-frontend' ),
                'css'        => '',
                'placeholder' => __( 'Enter organizer name', 'wp-user-frontend' ),
                'default'    => '',
                'size'       => '40',
                'wpuf_cond'  => $this->conditionals,
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
            ],
            [
                'input_type' => 'text',
                'template'   => 'text_field',
                'required'   => 'no',
                'label'      => __( 'Cost', 'wp-user-frontend' ),
                'name'       => '_EventCost',
                'is_meta'    => 'yes',
                'wpuf_cond'  => $this->conditionals,
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
            ],
            [
                'input_type'          => 'textarea',
                'template'            => 'post_excerpt',
                'required'            => 'no',
                'label'               => __( 'Excerpt', 'wp-user-frontend' ),
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
            ],
        ];
    }

    /**
     * Get UTC time
     *
     * @since WPUF_SINCE
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
     * Get venue options for select field
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_venue_options() {
        $venues = TEC_Helper::get_all_venues();
        $options = [ '' => __( 'Select a venue', 'wp-user-frontend' ) ];

        if ( ! empty( $venues ) ) {
            foreach ( $venues as $venue ) {
                // Handle both WP_Post objects and arrays
                if ( is_object( $venue ) && isset( $venue->ID, $venue->post_title ) ) {
                    $options[ $venue->ID ] = $venue->post_title;
                } elseif ( is_array( $venue ) && isset( $venue['ID'], $venue['post_title'] ) ) {
                    $options[ $venue['ID'] ] = $venue['post_title'];
                }
            }
        }

        return $options;
    }

    /**
     * Get organizer options for select field
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_organizer_options() {
        $organizers = TEC_Helper::get_all_organizers();
        $options = [ '' => __( 'Select an organizer', 'wp-user-frontend' ) ];

        if ( ! empty( $organizers ) ) {
            foreach ( $organizers as $organizer ) {
                // Handle both WP_Post objects and arrays
                if ( is_object( $organizer ) && isset( $organizer->ID, $organizer->post_title ) ) {
                    $options[ $organizer->ID ] = $organizer->post_title;
                } elseif ( is_array( $organizer ) && isset( $organizer['ID'], $organizer['post_title'] ) ) {
                    $options[ $organizer['ID'] ] = $organizer['post_title'];
                }
            }
        }

        return $options;
    }

    /**
     * Get form settings
     *
     * @since WPUF_SINCE
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
                __(
                    'Event has been updated successfully. %sView event%s',
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

Here is the details:
Event Title: {post_title}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}',
                'edit'         => 'off',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => 'Post has been edited',
                'edit_body'    => 'Hi,
The event "{post_title}" has been updated

Here is the details:
Event Title: {post_title}
Description: {post_content}
Short Description: {post_excerpt}
Author: {author}
Post URL: {permalink}
Edit URL: {editlink}'
            ],
        ];
    }
}
