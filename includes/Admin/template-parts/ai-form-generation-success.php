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

// Remove admin notices
remove_all_actions( 'admin_notices' );
remove_all_actions( 'all_admin_notices' );

// Load WordPress admin but hide header/footer  
require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<style>
    
    /* Adjust body for no admin bar */
    html.wp-toolbar {
        padding-top: 0 !important;
    }
    
    body.wp-admin {
        margin: 0;
        padding: 0;
    }
    
    /* Adjust main content area to account for sidebar */
    #wpcontent {
        padding: 0 !important;
        margin-top: 0 !important;
    }
    
    #wpbody-content {
        padding: 20px !important;
        padding-bottom: 80px !important; /* Increased to account for fixed footer */
    }
    
    /* Hide notices */
    .notice, .error, .updated {
        display: none !important;
    }
    
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
    }
    
    @media (min-width: 1024px) {
        .wpuf-grid-container {
            grid-template-columns: 1fr 2fr;
        }
    }

    .wpuf-chat-box {
        width: 100%;
        height: auto;
        background: white;
        border: 1px solid #e5e7eb;
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
            
            <div class="wpuf-grid-container">
                <!-- Left Side - Chat Box -->
                <div class="wpuf-chat-box">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 24px; border-bottom: 1px solid #f3f4f6;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg style="width: 20px; height: 20px; color: #6b7280;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 12H16M8 16H13M7 20L3 16V4C3 3.44772 3.44772 3 4 3H20C20.5523 3 21 3.44772 21 4V16C21 16.5523 20.5523 17 20 17H7.58579C7.21071 17 6.85196 17.1464 6.58579 17.4126L7 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <h2 class="wpuf-success-title"><?php esc_html_e( 'Form Generation Chat', 'wp-user-frontend' ); ?></h2>
                    </div>
                    <span style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #059669; font-weight: 500;">
                        <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                        <?php esc_html_e( 'Completed', 'wp-user-frontend' ); ?>
                    </span>
                </div>
                
                <div style="flex: 1; display: flex; flex-direction: column; gap: 24px; overflow-y: auto; padding-right: 8px;">
                    <!-- User Message -->
                    <div style="display: flex; flex-direction: row-reverse; gap: 12px;">
                        <div style="width: 28px; height: 28px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 16px; height: 16px; color: white;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <div style="flex: 1; min-width: 0; text-align: right;">
                            <div style="background: #3b82f6; color: white; border-radius: 16px; padding: 12px 16px; margin-bottom: 4px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;">{{ formTitle }}</p>
                            </div>
                            <span style="font-size: 12px; color: #6b7280;"><?php esc_html_e( 'Just now', 'wp-user-frontend' ); ?></span>
                        </div>
                    </div>
                    
                    <!-- AI Response -->
                    <div style="display: flex; gap: 12px;">
                        <div style="width: 28px; height: 28px; background: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 16px; height: 16px; color: white;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1.5V3.5L21 9ZM3 5.5L9 11H3V5.5ZM3 19V13H9L15 19H3ZM21 19C21 19 21 19 21 19L15 13.5V15.5L21 19Z"/>
                            </svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 12px 16px; margin-bottom: 4px;">
                                <p style="font-size: 14px; line-height: 1.5; margin: 0 0 8px 0;"><?php esc_html_e( 'Perfect! I\'ve created a Portfolio Submission form for you with the following fields:', 'wp-user-frontend' ); ?></p>
                                <ul style="font-size: 14px; margin: 8px 0; padding-left: 20px;">
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'First Name - Text input for personal identification', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'Email - Required field for communication', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'File Upload - For portfolio files (PDF, images)', 'wp-user-frontend' ); ?></li>
                                    <li style="margin-bottom: 4px;"><?php esc_html_e( 'Comment - Optional field for additional information', 'wp-user-frontend' ); ?></li>
                                </ul>
                                <p style="font-size: 14px; line-height: 1.5; margin: 0;"><?php esc_html_e( 'The form is ready and you can customize it further in the form builder!', 'wp-user-frontend' ); ?></p>
                            </div>
                            <span style="font-size: 12px; color: #6b7280;"><?php esc_html_e( 'Just now', 'wp-user-frontend' ); ?></span>
                        </div>
                    </div>
                </div>
                
                <div style="padding-top: 24px; border-top: 1px solid #f3f4f6; display: grid; gap: 24px;">
                    <div style="display: grid; grid-template-columns: auto 1fr; align-items: center; gap: 16px; padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;">
                        <div style="width: 32px; height: 32px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 16px; height: 16px; color: #059669;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0 0 4px 0;"><?php esc_html_e( 'Form Generated Successfully!', 'wp-user-frontend' ); ?></h3>
                            <p style="font-size: 14px; color: #6b7280; margin: 0;"><?php esc_html_e( 'Your AI-generated form is ready to use', 'wp-user-frontend' ); ?></p>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <a href="#" class="wpuf-btn-primary" @click="editForm">
                            <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            <?php esc_html_e( 'Edit in Builder', 'wp-user-frontend' ); ?>
                        </a>
                        <a href="#" class="wpuf-btn-secondary" @click="previewForm">
                            <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <?php esc_html_e( 'Preview Form', 'wp-user-frontend' ); ?>
                        </a>
                    </div>
                    
                    <div style="display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 14px;">
                        <a href="#" style="color: #6b7280; text-decoration: none;" @click="viewAllForms">
                            <?php esc_html_e( 'View All Forms', 'wp-user-frontend' ); ?>
                        </a>
                        <span style="color: #d1d5db;">•</span>
                        <a href="#" style="color: #6b7280; text-decoration: none;" @click="createAnother">
                            <?php esc_html_e( 'Create Another Form', 'wp-user-frontend' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form Preview -->
            <div class="wpuf-form-preview">
                <div style="display: grid; grid-template-columns: auto 1fr auto; align-items: start; gap: 12px; margin-bottom: 24px;">
                    <div style="width: 28px; height: 28px; background: #059669; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 16px; height: 16px; color: white;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M7 8H17M7 12H17M7 16H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 class="wpuf-success-form-title">
                            {{ formTitle }}
                        </h3>
                        <p class="wpuf-success-text"><?php esc_html_e( 'Please complete all information below', 'wp-user-frontend' ); ?></p>
                    </div>
                    <span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">
                        <?php esc_html_e( 'Draft', 'wp-user-frontend' ); ?>
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280;">
                        <svg style="width: 14px; height: 14px; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280;">
                        <svg style="width: 14px; height: 14px; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?php esc_html_e( 'AI Generated', 'wp-user-frontend' ); ?></span>
                    </div>
                </div>
                
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