<?php
/**
 * Subscriptions Template to Display list of subscriptions packages
 *
 * @version 2.8.8
 *
 * @var WPUF_Subscription
 * @var $packs            array
 * @var $pack_order       array
 * @var $args             array
 * @var $details_meta
 * @var $current_pack
 */
do_action( 'wpuf_before_subscription_listing', $packs );

if ( $packs ) {
    echo wp_kses_post( '<ul class="wpuf_packs wpuf-grid wpuf-grid-cols-1 md:wpuf-grid-cols-2 lg:wpuf-grid-cols-3 wpuf-gap-4 wpuf-max-w-5xl wpuf-mx-auto wpuf-px-4 wpuf-items-stretch wpuf-mt-6">' );

    if ( isset( $args['include'] ) && $args['include'] != '' ) {
        for ( $i = 0; $i < count( $pack_order ); $i++ ) {
            foreach ( $packs as $pack ) {
                if (  (int) $pack->ID == $pack_order[$i] ) {
                    $class = 'wpuf-pack-' . $pack->ID; ?>
                    <li class="<?php echo esc_attr( $class ); ?> wpuf-h-full">
                        <?php $subscription->pack_details( $pack, $details_meta, isset( $current_pack['pack_id'] ) ? $current_pack['pack_id'] : '' ); ?>
                    </li>
                    <?php
                }
            }
        }
    } else {
        foreach ( $packs as $pack ) {
            $class = 'wpuf-pack-' . $pack->ID; ?>
            <li class="<?php echo esc_attr( $class ); ?> wpuf-h-full">
                <?php $subscription->pack_details( $pack, $details_meta, isset( $current_pack['pack_id'] ) ? $current_pack['pack_id'] : '' ); ?>
            </li>
            <?php
        }
    }
    echo wp_kses_post( '</ul>' );
    ?>
    <script>
    (function() {
        // Use event delegation to handle all toggle clicks
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize once
            if (window.wpufFeaturesInitialized) return;
            window.wpufFeaturesInitialized = true;
            
            // Add event listener to the document
            document.addEventListener('click', function(e) {
                // Check if clicked element has the toggle features data attribute
                if (e.target && e.target.hasAttribute('data-wpuf-toggle-features')) {
                    e.preventDefault();
                    
                    const packId = e.target.getAttribute('data-wpuf-pack-id');
                    if (!packId) return;
                    
                    // Get the specific pack's elements using the unique pack ID
                    const featuresList = document.getElementById('wpuf-features-list-' + packId);
                    const seeMoreBtn = document.getElementById('wpuf-see-more-btn-' + packId);
                    const seeLessBtn = document.getElementById('wpuf-see-less-btn-' + packId);
                    
                    if (!featuresList || !seeMoreBtn || !seeLessBtn) {
                        console.warn('Elements not found for pack ID:', packId);
                        return;
                    }
                    
                    // Only get expandable features from this specific pack
                    const expandableFeatures = featuresList.querySelectorAll('.wpuf-expandable-feature');
                    
                    // Toggle visibility based on current state
                    const isExpanded = e.target.getAttribute('data-expanded') === 'true';
                    
                    if (isExpanded) {
                        // Currently expanded, collapse it
                        expandableFeatures.forEach(function(feature) {
                            feature.classList.add('wpuf-hidden');
                        });
                        seeMoreBtn.classList.remove('wpuf-hidden');
                        seeLessBtn.classList.add('wpuf-hidden');
                    } else {
                        // Currently collapsed, expand it
                        expandableFeatures.forEach(function(feature) {
                            feature.classList.remove('wpuf-hidden');
                        });
                        seeMoreBtn.classList.add('wpuf-hidden');
                        seeLessBtn.classList.remove('wpuf-hidden');
                    }
                }
            });
        });
    })();
    </script>
    <?php
}

do_action( 'wpuf_after_subscription_listing', $packs );
