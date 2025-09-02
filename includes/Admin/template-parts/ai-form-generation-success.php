<?php
/**
 * AI Form Generation Success Template
 * 
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Sanitize and prepare variables early to avoid null warnings
$form_id = isset( $_GET['form_id'] ) && ! empty( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';
$form_title = isset( $_GET['form_title'] ) && ! empty( $_GET['form_title'] ) ? sanitize_text_field( wp_unslash( $_GET['form_title'] ) ) : 'Portfolio Submission';

// Load WordPress admin but hide header/footer  
require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<style>
    /* Custom styles for our content */
    .wpuf-success-content {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f9fafb;
        min-height: 100vh;
        padding: 20px;
    }

    /* Layout styles */
    .wpuf-success-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    /* Grid layout for side-by-side display */
    .wpuf-grid-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 10px;
    }
    
    @media (min-width: 1024px) {
        .wpuf-grid-container {
            grid-template-columns: 1fr 2fr;
        }
    }

    .wpuf-chat-box {
        width: 100%;
        height: auto;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 36px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    @media (min-width: 1024px) {
        .wpuf-chat-box {
            height: 1072px;
        }
    }

    .wpuf-form-preview {
        width: 100%;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        height: 1072px;
        position: relative;
    }
    

    /* Typography */
    .wpuf-success-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .wpuf-success-text {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    .wpuf-success-form-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Buttons */
    .wpuf-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: #059669;
        color: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid #059669;
        transition: background-color 0.2s;
    }

    .wpuf-btn-primary:hover {
        background: #047857;
        color: white;
        text-decoration: none;
    }

    .wpuf-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: white;
        color: #374151;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid #d1d5db;
        transition: background-color 0.2s;
    }

    .wpuf-btn-secondary:hover {
        background: #f9fafb;
        color: #374151;
        text-decoration: none;
    }

    /* Form elements */
    .wpuf-form-field {
        margin-bottom: 24px;
    }

    .wpuf-form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .wpuf-form-input {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
        color: #6b7280;
        width: 100%;
    }

    .wpuf-form-textarea {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
        color: #6b7280;
        width: 100%;
        min-height: 80px;
        resize: vertical;
    }
    
    /* Custom scrollbar styling for both chat and form */
    .wpuf-chat-scrollable::-webkit-scrollbar,
    .wpuf-form-scrollable::-webkit-scrollbar {
        width: 1px;
    }
    
    .wpuf-chat-scrollable::-webkit-scrollbar-track,
    .wpuf-form-scrollable::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 1px;
    }
    
    .wpuf-chat-scrollable::-webkit-scrollbar-thumb,
    .wpuf-form-scrollable::-webkit-scrollbar-thumb {
        background: #34D399;
        border-radius: 1px;
    }
    
    .wpuf-chat-scrollable::-webkit-scrollbar-thumb:hover,
    .wpuf-form-scrollable::-webkit-scrollbar-thumb:hover {
        background: #10B981;
    }
    
    /* Firefox scrollbar styling */
    .wpuf-chat-scrollable,
    .wpuf-form-scrollable {
        scrollbar-width: thin;
        scrollbar-color: #34D399 #f3f4f6;
    }
    
    /* Chat input focus style */
    .wpuf-chat-input:focus {
        border-color: #34D399 !important;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
    }
    
    /* Mobile responsiveness */
    @media (max-width: 782px) {
        #adminmenumain {
            margin-left: -190px;
        }
        
        #wpcontent {
            margin-left: 0 !important;
        }
    }
</style>

<div class="wpuf-ai-form-wrapper wpuf-mt-8" id="wpuf-ai-form-success">
    <div class="wpuf-ai-form-container">
        <div class="wpuf-ai-form-content wpuf-bg-white wpuf-rounded-lg">
            
            <!-- Header Section -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 10px; margin-bottom: 24px;">
                <!-- Left Side - Logo and Text -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="18" fill="#10B981"/>
                        <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                        <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                        <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                        <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                    </svg>
                    <div>
                        <h1 style="font-size: 24px; font-weight: 600; color: #1f2937; margin: 0;"><?php esc_html_e( 'AI Form Builder', 'wp-user-frontend' ); ?></h1>
                        <p style="font-size: 14px; color: #6b7280; margin: 0;"><?php esc_html_e( 'Generate forms instantly with AI assistance', 'wp-user-frontend' ); ?></p>
                    </div>
                </div>
                
                <!-- Right Side - Buttons -->
                <div style="display: flex; gap: 12px;">
                    <button style="width: 153px; height: 42px; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 6px; padding: 9px 17px 9px 17px; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: background-color 0.2s;">
                        <?php esc_html_e( 'Regenerate', 'wp-user-frontend' ); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.3523 7.79032H17.5128V7.78884M2.48682 16.3703V12.2098M2.48682 12.2098L6.64735 12.2098M2.48682 12.2098L5.13756 14.8622C5.963 15.6892 7.01055 16.3166 8.22034 16.6408C11.8879 17.6235 15.6577 15.447 16.6405 11.7794M3.35898 8.22068C4.3417 4.5531 8.11152 2.37659 11.7791 3.35932C12.9889 3.68348 14.0365 4.31091 14.8619 5.1379L17.5128 7.78884M17.5128 3.62982V7.78884" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button style="width: 170px; height: 42px; background: #059669; border: none; border-radius: 6px; padding: 9px 17px 9px 17px; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 14px; font-weight: 500; color: white; cursor: pointer; transition: background-color 0.2s;" @click="editForm">
                        <?php esc_html_e( 'Edit in Builder', 'wp-user-frontend' ); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="wpuf-grid-container">
                <!-- Left Side - Chat Box -->
                <div class="wpuf-chat-box">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 24px; border-bottom: 1px solid #f3f4f6;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.625 9.75C8.625 9.95711 8.45711 10.125 8.25 10.125C8.04289 10.125 7.875 9.95711 7.875 9.75C7.875 9.54289 8.04289 9.375 8.25 9.375C8.45711 9.375 8.625 9.54289 8.625 9.75ZM8.625 9.75H8.25M12.375 9.75C12.375 9.95711 12.2071 10.125 12 10.125C11.7929 10.125 11.625 9.95711 11.625 9.75C11.625 9.54289 11.7929 9.375 12 9.375C12.2071 9.375 12.375 9.54289 12.375 9.75ZM12.375 9.75H12M16.125 9.75C16.125 9.95711 15.9571 10.125 15.75 10.125C15.5429 10.125 15.375 9.95711 15.375 9.75C15.375 9.54289 15.5429 9.375 15.75 9.375C15.9571 9.375 16.125 9.54289 16.125 9.75ZM16.125 9.75H15.75M2.25 12.7593C2.25 14.3604 3.37341 15.754 4.95746 15.987C6.04357 16.1467 7.14151 16.27 8.25 16.3556V21L12.4335 16.8165C12.6402 16.6098 12.9193 16.4923 13.2116 16.485C15.1872 16.4361 17.1331 16.2678 19.0425 15.9871C20.6266 15.7542 21.75 14.3606 21.75 12.7595V6.74056C21.75 5.13946 20.6266 3.74583 19.0425 3.51293C16.744 3.17501 14.3926 3 12.0003 3C9.60776 3 7.25612 3.17504 4.95747 3.51302C3.37342 3.74593 2.25 5.13956 2.25 6.74064V12.7593Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <h2 class="wpuf-success-title"><?php esc_html_e( 'WPUF Form Generation Chat', 'wp-user-frontend' ); ?></h2>
                    </div>
                </div>
                
                <div class="wpuf-chat-scrollable" style="flex: 1; overflow-y: auto; padding-right: 8px;">
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Chat 1: User Message -->
                    <div style="display: flex; flex-direction: row-reverse; gap: 12px;">
                        <div style="flex: 1; min-width: 0; text-align: right;">
                            <div style="background: #ECFDF5; border: 1px solid #34D399; border-top-left-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; color: #1f2937;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">Create a contact form</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 2: AI Response -->
                    <div style="display: flex; gap: 12px;">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                        </svg>
                        <div style="flex: 1; min-width: 0;">
                            <div style="background: #FFFFFF; border: 1px solid #E5E7EB; border-top-right-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; gap: 16px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">I'll create a contact form for you. What fields would you like to include?</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 3: User Message -->
                    <div style="display: flex; flex-direction: row-reverse; gap: 12px;">
                        <div style="flex: 1; min-width: 0; text-align: right;">
                            <div style="background: #ECFDF5; border: 1px solid #34D399; border-top-left-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; color: #1f2937;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">Add name, email, phone, and message fields</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 4: AI Response -->
                    <div style="display: flex; gap: 12px;">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                        </svg>
                        <div style="flex: 1; min-width: 0;">
                            <div style="background: #FFFFFF; border: 1px solid #E5E7EB; border-top-right-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; gap: 16px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">Great! I'm adding those fields. Should the phone field be required?</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 5: User Message -->
                    <div style="display: flex; flex-direction: row-reverse; gap: 12px;">
                        <div style="flex: 1; min-width: 0; text-align: right;">
                            <div style="background: #ECFDF5; border: 1px solid #34D399; border-top-left-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; color: #1f2937;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">No, make it optional. Also add a subject dropdown</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 6: AI Response -->
                    <div style="display: flex; gap: 12px;">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                        </svg>
                        <div style="flex: 1; min-width: 0;">
                            <div style="background: #FFFFFF; border: 1px solid #E5E7EB; border-top-right-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; gap: 16px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">Perfect! I've added a subject dropdown with options: General Inquiry, Support, Sales, and Feedback. Phone is now optional.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 7: User Message -->
                    <div style="display: flex; flex-direction: row-reverse; gap: 12px;">
                        <div style="flex: 1; min-width: 0; text-align: right;">
                            <div style="background: #ECFDF5; border: 1px solid #34D399; border-top-left-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; color: #1f2937;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">{{ formTitle }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat 8: AI Response with buttons -->
                    <div style="display: flex; gap: 12px;">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                        </svg>
                        <div style="flex: 1; min-width: 0;">
                            <div style="background: #FFFFFF; border: 1px solid #E5E7EB; border-top-right-radius: 30px; border-bottom-right-radius: 30px; border-bottom-left-radius: 30px; padding: 12px 20px; margin-bottom: 4px; gap: 16px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0 0 8px 0;"><?php esc_html_e( 'Perfect! I\'ve created a Portfolio Submission form for you with the following fields:', 'wp-user-frontend' ); ?></p>
                                <ul style="font-size: 14px; margin: 8px 0; padding-left: 20px;">
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'First Name - Text input for personal identification', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'Email - Required field for communication', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'File Upload - For portfolio files (PDF, images)', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'Comment - Optional field for additional information', 'wp-user-frontend' ); ?></li>
                                </ul>
                                <p style="font-size: 14px; line-height: 1.5; margin: 0 0 12px 0;"><?php esc_html_e( 'The form is ready and you can customize it further in the form builder!', 'wp-user-frontend' ); ?></p>
                                <div style="display: flex; gap: 8px;">
                                    <button style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 6px; padding: 9px 13px; font-size: 14px; color: #374151; cursor: pointer; transition: background-color 0.2s;">
                                        <?php esc_html_e( 'Apply', 'wp-user-frontend' ); ?>
                                    </button>
                                    <button style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 6px; padding: 9px 13px; font-size: 14px; color: #374151; cursor: pointer; transition: background-color 0.2s;">
                                        <?php esc_html_e( 'Reject', 'wp-user-frontend' ); ?>
                                    </button>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                                <span style="font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 400; font-style: italic; font-size: 14px; line-height: 24px; color: #059669; text-align: right;"><?php esc_html_e( 'Successfully applied the instruction.', 'wp-user-frontend' ); ?></span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                <div style="padding-top: 24px; border-top: 1px solid #f3f4f6;">
                    <div style="position: relative; width: 100%; height: 148px;">
                        <textarea class="wpuf-chat-input" placeholder="<?php esc_attr_e( 'Type your message here...', 'wp-user-frontend' ); ?>" style="width: 100%; height: 100%; padding: 12px 60px 12px 16px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 14px; outline: none; background: #FFFFFF; resize: none; font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s;"></textarea>
                        <button style="position: absolute; right: 8px; bottom: 8px; width: 42px; height: 42px; background: #059669; border: none; border-radius: 21px; padding: 9px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s;">
                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.99972 10L1.2688 1.12451C7.88393 3.04617 14.0276 6.07601 19.4855 9.99974C14.0276 13.9235 7.884 16.9535 1.26889 18.8752L3.99972 10ZM3.99972 10L11.5 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form Preview -->
            <div class="wpuf-form-preview">
                <div style="padding-bottom: 24px; border-bottom: 1px solid #f3f4f6; text-align: center;">
                    <h3 style="font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 700; font-size: 30px; line-height: 36px; letter-spacing: 0; text-align: center; color: #000824; margin: 0 0 12px 0;">
                        {{ formTitle }}
                    </h3>
                    <p style="font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 400; font-size: 18px; line-height: 24px; letter-spacing: 0; text-align: center; color: #6B7280; margin: 0;"><?php esc_html_e( 'Please complete all information below', 'wp-user-frontend' ); ?></p>
                </div>
                
                <div class="wpuf-form-scrollable" style="flex: 1; overflow-y: auto; padding-right: 8px; margin-top: 24px;">
                    <div style="display: grid; gap: 24px;">
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'First Name', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'Enter your first name', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Email', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'Enter email address', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Select File Types', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input" style="position: relative;">
                            <?php esc_html_e( 'Select File Types', 'wp-user-frontend' ); ?>
                            <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'File Upload', 'wp-user-frontend' ); ?></label>
                        <div style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 6px; padding: 12px; text-align: center; display: grid; place-items: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px; color: #9ca3af;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C18.6569 10 20 11.3431 20 13V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V13C4 11.3431 5.34315 10 7 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 14V18M10 16H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span style="font-size: 12px; color: #6b7280; line-height: 1.3;"><?php esc_html_e( 'Only JPEG, PNG and PDF files and max size of (025*300 or larger recommended, up to 5MB each)', 'wp-user-frontend' ); ?></span>
                        </div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Comment', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-textarea"><?php esc_html_e( 'Write here your Comment', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Last Name', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'Enter your last name', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Phone Number', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( '+1 (555) 000-0000', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Company Name', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'Enter your company name', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Job Title', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'Enter your job title', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Website URL', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'https://example.com', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'LinkedIn Profile', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input"><?php esc_html_e( 'https://linkedin.com/in/yourprofile', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Years of Experience', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input" style="position: relative;">
                            <?php esc_html_e( 'Select years', 'wp-user-frontend' ); ?>
                            <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Skills', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-textarea"><?php esc_html_e( 'List your key skills...', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Portfolio Description', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-textarea"><?php esc_html_e( 'Describe your portfolio projects...', 'wp-user-frontend' ); ?></div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Additional Documents', 'wp-user-frontend' ); ?></label>
                        <div style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 6px; padding: 12px; text-align: center; display: grid; place-items: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px; color: #9ca3af;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C18.6569 10 20 11.3431 20 13V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V13C4 11.3431 5.34315 10 7 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 14V18M10 16H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span style="font-size: 12px; color: #6b7280; line-height: 1.3;"><?php esc_html_e( 'Drop files here or click to upload', 'wp-user-frontend' ); ?></span>
                        </div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'Availability', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-input" style="position: relative;">
                            <?php esc_html_e( 'Select availability', 'wp-user-frontend' ); ?>
                            <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="wpuf-form-field">
                        <label class="wpuf-form-label"><?php esc_html_e( 'References', 'wp-user-frontend' ); ?></label>
                        <div class="wpuf-form-textarea"><?php esc_html_e( 'Provide references if available...', 'wp-user-frontend' ); ?></div>
                    </div>
                    </div>
                </div>
                
                <div style="padding-top: 24px; border-top: 1px solid #f3f4f6;">
                    <button style="width: 100%; height: 50px; background: #059669; border: none; border-radius: 6px; padding: 13px 25px 13px 25px; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 16px; font-weight: 600; color: white; cursor: pointer; transition: background-color 0.2s;" @click="editForm">
                        <?php esc_html_e( 'Edit with Builder', 'wp-user-frontend' ); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Vue === 'undefined') {
        return;
    }
    
    new Vue({
        el: '#wpuf-ai-form-success',
        
        data: {
            formId: '<?php echo esc_js( $form_id ); ?>',
            formTitle: '<?php echo esc_js( $form_title ); ?>'
        },
        
        methods: {
            editForm: function() {
                if (this.formId) {
                    window.location.href = 'admin.php?page=wpuf-post-forms&action=edit&id=' + this.formId;
                } else {
                    window.location.href = 'admin.php?page=wpuf-post-forms';
                }
            },
            
            previewForm: function() {
                if (this.formId) {
                    window.open('?wpuf_form_preview=' + this.formId, '_blank');
                }
            },
            
            viewAllForms: function() {
                window.location.href = 'admin.php?page=wpuf-post-forms';
            },
            
            createAnother: function() {
                window.location.href = 'admin.php?page=wpuf-ai-form-builder';
            }
        }
    });
});
</script>

<?php
// Include WordPress admin footer
require_once ABSPATH . 'wp-admin/admin-footer.php';
?>