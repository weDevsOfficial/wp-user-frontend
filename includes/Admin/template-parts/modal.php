<?php use WeDevs\Wpuf\Free\Pro_Prompt; ?>
<div class="wpuf-form-template-modal wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] wpuf-bg-gray-100 wpuf-hidden">
    <div class="wpuf-relative wpuf-mx-auto wpuf-px-4 wpuf-py-10">
        <button
            class="wpuf-absolute wpuf-right-4 wpuf-top-4 wpuf-text-gray-400 hover:wpuf-text-gray-600 focus:wpuf-outline-none wpuf-close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Header -->
        <div class="wpuf-mb-10 wpuf-text-center">
            <h1 class="wpuf-text-2xl wpuf-font-bold wpuf-text-gray-800">Select a Post Form Template</h1>
            <p class="wpuf-text-gray-600">Select from a pre-defined template or from a blank form</p>
        </div>

        <!-- Templates Grid -->
        <div class="wpuf-grid wpuf-gap-6 wpuf-grid-cols-3">
            <!-- Blank Form -->
            <div
                class="wpuf-flex wpuf-h-48 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border wpuf-bg-white wpuf-shadow-sm wpuf-transition wpuf-duration-200 hover:wpuf-shadow-lg">
                <h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">Blank Form</h2>
            </div>

            <!-- Post Form -->
            <div
                class="wpuf-flex wpuf-h-48 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border wpuf-bg-white wpuf-shadow-sm wpuf-transition wpuf-duration-200 hover:wpuf-shadow-lg">
                <h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">Post Form</h2>
            </div>

            <!-- WooCommerce Product Form -->
            <div
                class="wpuf-flex wpuf-h-48 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border wpuf-bg-white wpuf-shadow-sm wpuf-transition wpuf-duration-200 hover:wpuf-shadow-lg">
                <h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">WooCommerce Product Form</h2>
            </div>

            <!-- EDD Download Form -->
            <div
                class="wpuf-flex wpuf-h-48 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border wpuf-bg-white wpuf-shadow-sm wpuf-transition wpuf-duration-200 hover:wpuf-shadow-lg">
                <h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">EDD Download Form</h2>
            </div>

            <!-- The Event Calendar Form -->
            <div
                class="wpuf-flex wpuf-h-48 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border wpuf-bg-white wpuf-shadow-sm wpuf-transition wpuf-duration-200 hover:wpuf-shadow-lg">
                <h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">The Event Calendar Form</h2>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    ( function ( $ ) {
        var popup = {
            init: function () {
                $( '.wrap' ).on( 'click', 'a.page-title-action.add-form', this.openModal );
                $( '.wpuf-form-template-modal .wpuf-close-btn' ).on( 'click', $.proxy( this.closeModal, this ) );

                $( 'body' ).on( 'keydown', $.proxy( this.onEscapeKey, this ) );
            },

            openModal: function ( e ) {
                e.preventDefault();

                $( '.wpuf-form-template-modal' ).show();
                $( '#wpbody-content .wrap' ).hide();
            },

            onEscapeKey: function ( e ) {
                if (27 === e.keyCode) {
                    this.closeModal( e );
                }
            },

            closeModal: function ( e ) {
                if (typeof e !== 'undefined') {
                    e.preventDefault();
                }

                $( '.wpuf-form-template-modal' ).hide();
                $( '#wpbody-content .wrap' ).show();
            }
        };

        $( function () {
            popup.init();
        } );

    } )( jQuery );
</script>
