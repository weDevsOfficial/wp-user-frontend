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
    // Define the toggle function only once for all subscription packs
    if (typeof wpufToggleFeatures === 'undefined') {
        function wpufToggleFeatures(clickedButton, packId) {
            // Get the specific pack's elements using the pack ID
            const featuresList = document.getElementById('wpuf-features-list-' + packId);
            const seeMoreBtn = document.getElementById('wpuf-see-more-btn-' + packId);
            const seeLessBtn = document.getElementById('wpuf-see-less-btn-' + packId);
            
            if (!featuresList || !seeMoreBtn || !seeLessBtn) {
                return; // Exit if elements not found
            }
            
            // Only get expandable features from this specific pack
            const expandableFeatures = featuresList.querySelectorAll('.wpuf-expandable-feature');
            
            // Check if we're currently showing all features (see more button is hidden)
            const isShowingAll = seeMoreBtn.classList.contains('wpuf-hidden');
            
            if (isShowingAll) {
                // Currently showing all, hide extra features
                expandableFeatures.forEach(function(feature) {
                    feature.classList.add('wpuf-hidden');
                });
                seeMoreBtn.classList.remove('wpuf-hidden');
                seeLessBtn.classList.add('wpuf-hidden');
            } else {
                // Currently showing limited, show all features
                expandableFeatures.forEach(function(feature) {
                    feature.classList.remove('wpuf-hidden');
                });
                seeMoreBtn.classList.add('wpuf-hidden');
                seeLessBtn.classList.remove('wpuf-hidden');
            }
        }
    }
    </script>
    <?php
}

do_action( 'wpuf_after_subscription_listing', $packs );
