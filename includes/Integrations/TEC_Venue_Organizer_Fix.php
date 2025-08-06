<?php
/**
 * Fix for TEC Venue and Organizer radio buttons when WPUF Pro is disabled
 * 
 * This class provides a fallback implementation for venue and organizer
 * handling in The Events Calendar forms when WPUF Pro is not active.
 */

namespace WeDevs\Wpuf\Integrations;

class TEC_Venue_Organizer_Fix {
    
    /**
     * Initialize the fix
     */
    public function __construct() {
        // Only activate if TEC is active and WPUF Pro is NOT active
        if ( ! class_exists( 'Tribe__Events__Main' ) ) {
            return;
        }
        
        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            return; // Pro handles this already
        }
        
        // Add hooks for handling venue and organizer
        add_action( 'wpuf_add_post_after_insert', [ $this, 'handle_venue_organizer' ], 10, 4 );
        add_action( 'wpuf_edit_post_after_update', [ $this, 'handle_venue_organizer' ], 10, 4 );
        
        // Add JavaScript to handle radio buttons
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        
        // Don't render duplicate fields - just provide JavaScript functionality
        // The fields are already being rendered by WPUF, we just need to make them work
    }
    
    /**
     * Enqueue JavaScript for handling radio buttons
     */
    public function enqueue_scripts() {
        // Always enqueue on frontend to catch all cases
        if ( is_admin() ) {
            return;
        }
        
        // Add to footer to ensure it runs after all other scripts
        add_action( 'wp_footer', [ $this, 'output_inline_script' ] );
    }
    
    /**
     * Output inline script in footer
     */
    public function output_inline_script() {
        // Only output if we detect venue/organizer fields on the page
        if ( ! $this->has_venue_organizer_fields() ) {
            return;
        }
        
        echo '<script type="text/javascript">' . $this->get_inline_script() . '</script>';
    }
    
    /**
     * Check if page has venue/organizer fields
     */
    private function has_venue_organizer_fields() {
        // Always output script on frontend - let JS handle detection
        return ! is_admin();
    }
    
    /**
     * Get inline JavaScript for radio button handling
     */
    private function get_inline_script() {
        return "
        (function($) {
            function initVenueOrganizerFields() {
                
                // Cache field references to avoid losing them when hidden
                window.wpufVenueFields = {
                    allFields: $('.wpuf-el:has([name*=\"venue\"]), .wpuf-el:has([name=\"_EventVenueID\"]), .wpuf-el:has([data-name*=\"venue\"]), .wpuf-el:has([data-name=\"_EventVenueID\"])'),
                    createFields: null,
                    selectFields: null
                };
                
                window.wpufOrganizerFields = {
                    allFields: $('.wpuf-el:has([name*=\"organizer\"]), .wpuf-el:has([name=\"_EventOrganizerID\"]), .wpuf-el:has([data-name*=\"organizer\"]), .wpuf-el:has([data-name=\"_EventOrganizerID\"])'),
                    createFields: null,
                    selectFields: null
                };
                
                // Separate fields
                window.wpufVenueFields.createFields = window.wpufVenueFields.allFields.filter(':has([name*=\"venue_name\"]), :has([name*=\"venue_phone\"]), :has([name*=\"venue_website\"]), :has([data-name*=\"venue_name\"]), :has([data-name*=\"venue_phone\"]), :has([data-name*=\"venue_website\"])');
                window.wpufVenueFields.selectFields = window.wpufVenueFields.allFields.filter(':has([name=\"_EventVenueID\"]), :has([data-name=\"_EventVenueID\"])');
                
                window.wpufOrganizerFields.createFields = window.wpufOrganizerFields.allFields.filter(':has([name*=\"organizer_name\"]), :has([name*=\"organizer_phone\"]), :has([name*=\"organizer_website\"]), :has([name*=\"organizer_email\"]), :has([data-name*=\"organizer_name\"]), :has([data-name*=\"organizer_phone\"]), :has([data-name*=\"organizer_website\"]), :has([data-name*=\"organizer_email\"])');
                window.wpufOrganizerFields.selectFields = window.wpufOrganizerFields.allFields.filter(':has([name=\"_EventOrganizerID\"]), :has([data-name=\"_EventOrganizerID\"])');
                
                
                // Handle venue radio buttons
                function handleVenueRadio() {
                    var selectedVenue = $('input[name=\"venue\"]:checked').val();
                    
                    // Use cached field references
                    if (!window.wpufVenueFields) {
                        return initVenueOrganizerFields();
                    }
                    
                    var venueCreateFields = window.wpufVenueFields.createFields;
                    var venueSelectFields = window.wpufVenueFields.selectFields;
                    
                    
                    if (selectedVenue === 'create') {
                        // Show create fields, hide select fields
                        venueCreateFields.show().css('display', 'block');
                        venueSelectFields.hide();
                    } else {
                        // Show select fields, hide create fields (default for 'find' or empty)
                        venueSelectFields.show().css('display', 'block');
                        venueCreateFields.hide();
                    }
                }
                
                // Handle organizer radio buttons
                function handleOrganizerRadio() {
                    var selectedOrganizer = $('input[name=\"organizer\"]:checked').val();
                    
                    // Use cached field references
                    if (!window.wpufOrganizerFields) {
                        return initVenueOrganizerFields();
                    }
                    
                    var organizerCreateFields = window.wpufOrganizerFields.createFields;
                    var organizerSelectFields = window.wpufOrganizerFields.selectFields;
                    
                    
                    if (selectedOrganizer === 'create') {
                        // Show create fields, hide select fields
                        organizerCreateFields.show().css('display', 'block');
                        organizerSelectFields.hide();
                    } else {
                        // Show select fields, hide create fields (default for 'find' or empty)
                        organizerSelectFields.show().css('display', 'block');
                        organizerCreateFields.hide();
                    }
                }
                
                // Initialize on load
                handleVenueRadio();
                handleOrganizerRadio();
                
                // Bind change events
                $('input[name=\"venue\"]').on('change', handleVenueRadio);
                $('input[name=\"organizer\"]').on('change', handleOrganizerRadio);
                
                // Also bind to document in case radios are added dynamically
                $(document).on('change', 'input[name=\"venue\"], input[name=\"organizer\"]', function() {
                    if ($(this).attr('name') === 'venue') {
                        handleVenueRadio();
                    } else {
                        handleOrganizerRadio();
                    }
                });
            }
            
            // Initialize when DOM is ready
            $(document).ready(initVenueOrganizerFields);
            
            // Also try after delays in case form is loaded dynamically
            setTimeout(initVenueOrganizerFields, 500);
            setTimeout(initVenueOrganizerFields, 1000);
            setTimeout(initVenueOrganizerFields, 2000);
        })(jQuery);
        ";
    }
    
    
    /**
     * Handle venue and organizer creation/selection after post insert/update
     * 
     * @param int $post_id Post ID
     * @param int $form_id Form ID (unused but required for hook compatibility)
     * @param array $form_settings Form settings (unused but required for hook compatibility)  
     * @param array $form_vars Form variables (unused but required for hook compatibility)
     */
    public function handle_venue_organizer( $post_id, $form_id, $form_settings, $form_vars ) {
        // Suppress unused parameter warnings - these are required for hook compatibility
        unset( $form_id, $form_settings, $form_vars );
        // Check if this is an event
        if ( get_post_type( $post_id ) !== 'tribe_events' ) {
            return;
        }
        
        // Handle venue
        if ( isset( $_POST['venue'] ) ) {
            $venue_action = sanitize_text_field( $_POST['venue'] );
            
            if ( $venue_action === 'create' ) {
                $venue_id = $this->create_venue();
                if ( $venue_id ) {
                    update_post_meta( $post_id, '_EventVenueID', $venue_id );
                }
            } elseif ( $venue_action === 'find' && isset( $_POST['_EventVenueID'] ) ) {
                $venue_id = intval( $_POST['_EventVenueID'] );
                if ( $venue_id > 0 ) {
                    update_post_meta( $post_id, '_EventVenueID', $venue_id );
                }
            }
        }
        
        // Handle organizer
        if ( isset( $_POST['organizer'] ) ) {
            $organizer_action = sanitize_text_field( $_POST['organizer'] );
            
            if ( $organizer_action === 'create' ) {
                $organizer_id = $this->create_organizer();
                if ( $organizer_id ) {
                    update_post_meta( $post_id, '_EventOrganizerID', $organizer_id );
                }
            } elseif ( $organizer_action === 'find' && isset( $_POST['_EventOrganizerID'] ) ) {
                $organizer_id = intval( $_POST['_EventOrganizerID'] );
                if ( $organizer_id > 0 ) {
                    update_post_meta( $post_id, '_EventOrganizerID', $organizer_id );
                }
            }
        }
    }
    
    /**
     * Create a new venue from POST data
     */
    private function create_venue() {
        if ( ! isset( $_POST['venue_name'] ) || empty( $_POST['venue_name'] ) ) {
            return false;
        }
        
        $venue_data = [
            'post_title' => sanitize_text_field( $_POST['venue_name'] ),
            'post_type' => 'tribe_venue',
            'post_status' => 'publish'
        ];
        
        $venue_id = wp_insert_post( $venue_data );
        
        if ( ! is_wp_error( $venue_id ) && $venue_id > 0 ) {
            // Add venue meta
            if ( isset( $_POST['venue_phone'] ) ) {
                update_post_meta( $venue_id, '_VenuePhone', sanitize_text_field( $_POST['venue_phone'] ) );
            }
            if ( isset( $_POST['venue_website'] ) ) {
                update_post_meta( $venue_id, '_VenueURL', esc_url_raw( $_POST['venue_website'] ) );
            }
            
            // Handle address
            if ( isset( $_POST['venue_address'] ) && is_array( $_POST['venue_address'] ) ) {
                $address = $_POST['venue_address'];
                
                if ( isset( $address['street_address'] ) ) {
                    update_post_meta( $venue_id, '_VenueAddress', sanitize_text_field( $address['street_address'] ) );
                }
                if ( isset( $address['city_name'] ) ) {
                    update_post_meta( $venue_id, '_VenueCity', sanitize_text_field( $address['city_name'] ) );
                }
                if ( isset( $address['state'] ) ) {
                    update_post_meta( $venue_id, '_VenueStateProvince', sanitize_text_field( $address['state'] ) );
                    update_post_meta( $venue_id, '_VenueState', sanitize_text_field( $address['state'] ) );
                }
                if ( isset( $address['zip'] ) ) {
                    update_post_meta( $venue_id, '_VenueZip', sanitize_text_field( $address['zip'] ) );
                }
                if ( isset( $address['country_select'] ) ) {
                    update_post_meta( $venue_id, '_VenueCountry', sanitize_text_field( $address['country_select'] ) );
                }
            }
            
            return $venue_id;
        }
        
        return false;
    }
    
    /**
     * Create a new organizer from POST data
     */
    private function create_organizer() {
        if ( ! isset( $_POST['organizer_name'] ) || empty( $_POST['organizer_name'] ) ) {
            return false;
        }
        
        $organizer_data = [
            'post_title' => sanitize_text_field( $_POST['organizer_name'] ),
            'post_type' => 'tribe_organizer',
            'post_status' => 'publish'
        ];
        
        $organizer_id = wp_insert_post( $organizer_data );
        
        if ( ! is_wp_error( $organizer_id ) && $organizer_id > 0 ) {
            // Add organizer meta
            if ( isset( $_POST['organizer_phone'] ) ) {
                update_post_meta( $organizer_id, '_OrganizerPhone', sanitize_text_field( $_POST['organizer_phone'] ) );
            }
            if ( isset( $_POST['organizer_website'] ) ) {
                update_post_meta( $organizer_id, '_OrganizerWebsite', esc_url_raw( $_POST['organizer_website'] ) );
            }
            if ( isset( $_POST['organizer_email'] ) ) {
                update_post_meta( $organizer_id, '_OrganizerEmail', sanitize_email( $_POST['organizer_email'] ) );
            }
            
            return $organizer_id;
        }
        
        return false;
    }
    
}

// Initialize the fix
new TEC_Venue_Organizer_Fix();