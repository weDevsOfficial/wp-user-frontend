<?php

namespace WeDevs\Wpuf\Lib\AI;

/**
 * Mock Provider for AI Form Builder Testing
 * 
 * Provides predefined form responses for testing without external API calls
 * Supports all the new prompt templates from the UI
 * 
 * @since 1.0.0
 */
class MockProvider {

    /**
     * Mock form responses for different form types
     *
     * @var array
     */
    private $mock_responses = [
        'paid_guest_post' => [
            'form_title' => 'Paid Guest Post Submission',
            'form_description' => 'Submit your guest post for publication on our platform',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Author Name',
                    'name' => 'author_name',
                    'required' => true,
                    'placeholder' => 'Enter your full name'
                ],
                [
                    'id' => 2,
                    'type' => 'email',
                    'label' => 'Author Email',
                    'name' => 'author_email',
                    'required' => true,
                    'placeholder' => 'your.email@example.com'
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'label' => 'Article Title',
                    'name' => 'article_title',
                    'required' => true,
                    'placeholder' => 'Enter your article title'
                ],
                [
                    'id' => 4,
                    'type' => 'textarea',
                    'label' => 'Article Content',
                    'name' => 'article_content',
                    'required' => true,
                    'placeholder' => 'Enter your complete article content...'
                ],
                [
                    'id' => 5,
                    'type' => 'select',
                    'label' => 'Content Category',
                    'name' => 'content_category',
                    'required' => true,
                    'options' => [
                        'technology' => 'Technology',
                        'business' => 'Business',
                        'lifestyle' => 'Lifestyle',
                        'health' => 'Health',
                        'other' => 'Other'
                    ]
                ],
                [
                    'id' => 6,
                    'type' => 'number',
                    'label' => 'Proposed Payment Amount ($)',
                    'name' => 'payment_amount',
                    'required' => true,
                    'placeholder' => '100'
                ],
                [
                    'id' => 7,
                    'type' => 'checkbox',
                    'label' => 'I agree to the publication guidelines',
                    'name' => 'guidelines_agreement',
                    'required' => true
                ]
            ]
        ],

        'portfolio_submission' => [
            'form_title' => 'Portfolio Submission',
            'form_description' => 'Submit your portfolio for review and potential collaboration',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'required' => true,
                    'placeholder' => 'Enter your full name'
                ],
                [
                    'id' => 2,
                    'type' => 'email',
                    'label' => 'Email Address',
                    'name' => 'email',
                    'required' => true,
                    'placeholder' => 'your@email.com'
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'label' => 'Portfolio Title',
                    'name' => 'portfolio_title',
                    'required' => true,
                    'placeholder' => 'My Creative Portfolio'
                ],
                [
                    'id' => 4,
                    'type' => 'select',
                    'label' => 'Skills/Expertise',
                    'name' => 'skills',
                    'required' => true,
                    'options' => [
                        'web_design' => 'Web Design',
                        'graphic_design' => 'Graphic Design',
                        'photography' => 'Photography',
                        'writing' => 'Writing',
                        'development' => 'Development'
                    ]
                ],
                [
                    'id' => 5,
                    'type' => 'file',
                    'label' => 'Portfolio Files',
                    'name' => 'portfolio_files',
                    'required' => true,
                    'allowed_types' => 'pdf,jpg,png,gif,doc,docx'
                ],
                [
                    'id' => 6,
                    'type' => 'textarea',
                    'label' => 'Project Description',
                    'name' => 'project_description',
                    'required' => true,
                    'placeholder' => 'Describe your portfolio and key projects...'
                ],
                [
                    'id' => 7,
                    'type' => 'text',
                    'label' => 'Years of Experience',
                    'name' => 'experience_years',
                    'required' => false,
                    'placeholder' => '5'
                ]
            ]
        ],

        'classified_ads' => [
            'form_title' => 'Classified Ad Submission',
            'form_description' => 'Post your classified advertisement',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Ad Title',
                    'name' => 'ad_title',
                    'required' => true,
                    'placeholder' => 'Enter a catchy title for your ad'
                ],
                [
                    'id' => 2,
                    'type' => 'textarea',
                    'label' => 'Ad Description',
                    'name' => 'ad_description',
                    'required' => true,
                    'placeholder' => 'Describe your item or service in detail...'
                ],
                [
                    'id' => 3,
                    'type' => 'select',
                    'label' => 'Category',
                    'name' => 'category',
                    'required' => true,
                    'options' => [
                        'electronics' => 'Electronics',
                        'vehicles' => 'Vehicles',
                        'real_estate' => 'Real Estate',
                        'jobs' => 'Jobs',
                        'services' => 'Services',
                        'other' => 'Other'
                    ]
                ],
                [
                    'id' => 4,
                    'type' => 'number',
                    'label' => 'Price ($)',
                    'name' => 'price',
                    'required' => true,
                    'placeholder' => '100'
                ],
                [
                    'id' => 5,
                    'type' => 'text',
                    'label' => 'Location',
                    'name' => 'location',
                    'required' => true,
                    'placeholder' => 'City, State'
                ],
                [
                    'id' => 6,
                    'type' => 'email',
                    'label' => 'Contact Email',
                    'name' => 'contact_email',
                    'required' => true,
                    'placeholder' => 'your@email.com'
                ],
                [
                    'id' => 7,
                    'type' => 'text',
                    'label' => 'Contact Phone',
                    'name' => 'contact_phone',
                    'required' => false,
                    'placeholder' => '(555) 123-4567'
                ],
                [
                    'id' => 8,
                    'type' => 'file',
                    'label' => 'Ad Images',
                    'name' => 'ad_images',
                    'required' => false,
                    'allowed_types' => 'jpg,jpeg,png,gif'
                ]
            ]
        ],

        'coupon_submission' => [
            'form_title' => 'Coupon Submission',
            'form_description' => 'Submit your coupon or discount offer',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Business Name',
                    'name' => 'business_name',
                    'required' => true,
                    'placeholder' => 'Enter your business name'
                ],
                [
                    'id' => 2,
                    'type' => 'text',
                    'label' => 'Coupon Title',
                    'name' => 'coupon_title',
                    'required' => true,
                    'placeholder' => 'e.g., 50% Off All Items'
                ],
                [
                    'id' => 3,
                    'type' => 'textarea',
                    'label' => 'Coupon Description',
                    'name' => 'coupon_description',
                    'required' => true,
                    'placeholder' => 'Describe your offer in detail...'
                ],
                [
                    'id' => 4,
                    'type' => 'text',
                    'label' => 'Discount Amount/Percentage',
                    'name' => 'discount_amount',
                    'required' => true,
                    'placeholder' => '50% or $20'
                ],
                [
                    'id' => 5,
                    'type' => 'date',
                    'label' => 'Expiration Date',
                    'name' => 'expiration_date',
                    'required' => true
                ],
                [
                    'id' => 6,
                    'type' => 'textarea',
                    'label' => 'Terms and Conditions',
                    'name' => 'terms_conditions',
                    'required' => true,
                    'placeholder' => 'List any restrictions or conditions...'
                ],
                [
                    'id' => 7,
                    'type' => 'email',
                    'label' => 'Business Contact Email',
                    'name' => 'business_email',
                    'required' => true,
                    'placeholder' => 'business@example.com'
                ]
            ]
        ],

        'real_estate_listing' => [
            'form_title' => 'Real Estate Property Listing',
            'form_description' => 'List your property for sale or rent',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Property Title',
                    'name' => 'property_title',
                    'required' => true,
                    'placeholder' => 'Beautiful 3BR House in Downtown'
                ],
                [
                    'id' => 2,
                    'type' => 'textarea',
                    'label' => 'Property Description',
                    'name' => 'property_description',
                    'required' => true,
                    'placeholder' => 'Describe the property features and location...'
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'label' => 'Address',
                    'name' => 'property_address',
                    'required' => true,
                    'placeholder' => '123 Main St, City, State'
                ],
                [
                    'id' => 4,
                    'type' => 'select',
                    'label' => 'Property Type',
                    'name' => 'property_type',
                    'required' => true,
                    'options' => [
                        'house' => 'House',
                        'apartment' => 'Apartment',
                        'condo' => 'Condo',
                        'townhouse' => 'Townhouse',
                        'land' => 'Land'
                    ]
                ],
                [
                    'id' => 5,
                    'type' => 'number',
                    'label' => 'Price ($)',
                    'name' => 'price',
                    'required' => true,
                    'placeholder' => '350000'
                ],
                [
                    'id' => 6,
                    'type' => 'number',
                    'label' => 'Bedrooms',
                    'name' => 'bedrooms',
                    'required' => true,
                    'placeholder' => '3'
                ],
                [
                    'id' => 7,
                    'type' => 'number',
                    'label' => 'Bathrooms',
                    'name' => 'bathrooms',
                    'required' => true,
                    'placeholder' => '2'
                ],
                [
                    'id' => 8,
                    'type' => 'number',
                    'label' => 'Square Footage',
                    'name' => 'square_footage',
                    'required' => false,
                    'placeholder' => '1500'
                ],
                [
                    'id' => 9,
                    'type' => 'file',
                    'label' => 'Property Images',
                    'name' => 'property_images',
                    'required' => true,
                    'allowed_types' => 'jpg,jpeg,png,gif'
                ],
                [
                    'id' => 10,
                    'type' => 'email',
                    'label' => 'Contact Email',
                    'name' => 'contact_email',
                    'required' => true,
                    'placeholder' => 'agent@realty.com'
                ]
            ]
        ],

        'news_press_release' => [
            'form_title' => 'News/Press Release Submission',
            'form_description' => 'Submit your news or press release for publication',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Headline',
                    'name' => 'headline',
                    'required' => true,
                    'placeholder' => 'Enter an attention-grabbing headline'
                ],
                [
                    'id' => 2,
                    'type' => 'textarea',
                    'label' => 'Article Content',
                    'name' => 'article_content',
                    'required' => true,
                    'placeholder' => 'Write your news article or press release...'
                ],
                [
                    'id' => 3,
                    'type' => 'text',
                    'label' => 'Author/Organization',
                    'name' => 'author_organization',
                    'required' => true,
                    'placeholder' => 'Your name or organization'
                ],
                [
                    'id' => 4,
                    'type' => 'email',
                    'label' => 'Contact Email',
                    'name' => 'contact_email',
                    'required' => true,
                    'placeholder' => 'press@company.com'
                ],
                [
                    'id' => 5,
                    'type' => 'date',
                    'label' => 'Publication Date',
                    'name' => 'publication_date',
                    'required' => true
                ],
                [
                    'id' => 6,
                    'type' => 'select',
                    'label' => 'Category',
                    'name' => 'news_category',
                    'required' => true,
                    'options' => [
                        'business' => 'Business',
                        'technology' => 'Technology',
                        'health' => 'Health',
                        'politics' => 'Politics',
                        'sports' => 'Sports',
                        'entertainment' => 'Entertainment'
                    ]
                ],
                [
                    'id' => 7,
                    'type' => 'file',
                    'label' => 'Media Attachments',
                    'name' => 'media_attachments',
                    'required' => false,
                    'allowed_types' => 'jpg,jpeg,png,pdf,doc,docx'
                ]
            ]
        ],

        'product_listing' => [
            'form_title' => 'Product Listing',
            'form_description' => 'List your product for sale',
            'fields' => [
                [
                    'id' => 1,
                    'type' => 'text',
                    'label' => 'Product Name',
                    'name' => 'product_name',
                    'required' => true,
                    'placeholder' => 'Enter product name'
                ],
                [
                    'id' => 2,
                    'type' => 'textarea',
                    'label' => 'Product Description',
                    'name' => 'product_description',
                    'required' => true,
                    'placeholder' => 'Describe your product features and benefits...'
                ],
                [
                    'id' => 3,
                    'type' => 'select',
                    'label' => 'Product Category',
                    'name' => 'product_category',
                    'required' => true,
                    'options' => [
                        'electronics' => 'Electronics',
                        'clothing' => 'Clothing',
                        'home_garden' => 'Home & Garden',
                        'books' => 'Books',
                        'toys' => 'Toys & Games',
                        'health' => 'Health & Beauty'
                    ]
                ],
                [
                    'id' => 4,
                    'type' => 'number',
                    'label' => 'Price ($)',
                    'name' => 'product_price',
                    'required' => true,
                    'placeholder' => '29.99'
                ],
                [
                    'id' => 5,
                    'type' => 'number',
                    'label' => 'Quantity Available',
                    'name' => 'quantity',
                    'required' => true,
                    'placeholder' => '10'
                ],
                [
                    'id' => 6,
                    'type' => 'file',
                    'label' => 'Product Images',
                    'name' => 'product_images',
                    'required' => true,
                    'allowed_types' => 'jpg,jpeg,png,gif'
                ],
                [
                    'id' => 7,
                    'type' => 'text',
                    'label' => 'SKU/Model Number',
                    'name' => 'sku',
                    'required' => false,
                    'placeholder' => 'PROD-12345'
                ],
                [
                    'id' => 8,
                    'type' => 'textarea',
                    'label' => 'Shipping Information',
                    'name' => 'shipping_info',
                    'required' => false,
                    'placeholder' => 'Shipping costs, delivery time, etc...'
                ],
                [
                    'id' => 9,
                    'type' => 'email',
                    'label' => 'Seller Contact Email',
                    'name' => 'seller_email',
                    'required' => true,
                    'placeholder' => 'seller@example.com'
                ]
            ]
        ]
    ];

    /**
     * Generate form based on prompt
     * 
     * @param string $prompt User prompt
     * @param string $session_id Optional session ID for conversation continuity
     * @return array Generated form data
     */
    public function generateForm($prompt, $session_id = '') {
        // Simulate AI processing delay
        sleep(1);

        // Convert prompt to lowercase for matching
        $prompt_lower = strtolower($prompt);

        $response = null;

        // Match prompt to appropriate form template
        if (strpos($prompt_lower, 'paid guest post') !== false || 
            strpos($prompt_lower, 'guest post') !== false) {
            $response = $this->mock_responses['paid_guest_post'];
        } elseif (strpos($prompt_lower, 'portfolio') !== false) {
            $response = $this->mock_responses['portfolio_submission'];
        } elseif (strpos($prompt_lower, 'classified') !== false || 
                  strpos($prompt_lower, 'classified ad') !== false) {
            $response = $this->mock_responses['classified_ads'];
        } elseif (strpos($prompt_lower, 'coupon') !== false) {
            $response = $this->mock_responses['coupon_submission'];
        } elseif (strpos($prompt_lower, 'real estate') !== false || 
                  strpos($prompt_lower, 'property') !== false) {
            $response = $this->mock_responses['real_estate_listing'];
        } elseif (strpos($prompt_lower, 'news') !== false || 
                  strpos($prompt_lower, 'press release') !== false) {
            $response = $this->mock_responses['news_press_release'];
        } elseif (strpos($prompt_lower, 'product') !== false || 
                  strpos($prompt_lower, 'product listing') !== false) {
            $response = $this->mock_responses['product_listing'];
        } else {
            // Default fallback response
            $response = [
                'form_title' => 'Custom Form',
                'form_description' => 'Form generated based on: ' . substr($prompt, 0, 100),
                'fields' => [
                    [
                        'id' => 1,
                        'type' => 'text',
                        'label' => 'Full Name',
                        'name' => 'full_name',
                        'required' => true,
                        'placeholder' => 'Enter your full name'
                    ],
                    [
                        'id' => 2,
                        'type' => 'email',
                        'label' => 'Email Address',
                        'name' => 'email',
                        'required' => true,
                        'placeholder' => 'your@email.com'
                    ],
                    [
                        'id' => 3,
                        'type' => 'textarea',
                        'label' => 'Additional Details',
                        'name' => 'details',
                        'required' => false,
                        'placeholder' => 'Please provide additional information...'
                    ]
                ]
            ];
        }

        // Convert to WPUF template format
        $wpuf_fields = $this->convertToWPUFFormat($response['fields']);
        $form_settings = $this->getDefaultFormSettings($response['form_title']);

        // Add metadata and WPUF format
        $response['session_id'] = $session_id ?: uniqid('wpuf_ai_session_');
        $response['response_id'] = uniqid('mock_resp_');
        $response['provider'] = 'mock';
        $response['processing_time'] = '1.2s';
        $response['generated_at'] = current_time('mysql');
        $response['success'] = true;
        
        // Add WPUF specific data
        $response['wpuf_fields'] = $wpuf_fields;
        $response['form_settings'] = $form_settings;
        $response['metadata'] = [
            'prompt_length' => strlen($prompt),
            'field_count' => count($response['fields']),
            'wpuf_format' => true
        ];

        return $response;
    }

    /**
     * Generate unique field ID for WPUF compatibility
     *
     * @param string $name Field name
     * @param int $index Field index
     * @return int
     */
    private function generateFieldId($name, $index) {
        // Generate a consistent unique ID based on field name and index
        // WPUF typically uses integer IDs, so we'll create one
        $hash = crc32($name . '_' . $index);
        // Ensure it's a positive integer and not too large
        return abs($hash) % 999999999;
    }

    /**
     * Convert fields to WPUF template format
     *
     * @param array $fields
     * @return array
     */
    private function convertToWPUFFormat($fields) {
        $wpuf_fields = [];
        
        foreach ($fields as $index => $field) {
            $wpuf_field = [
                'input_type' => $this->mapToWPUFInputType($field['type']),
                'template' => $this->mapToWPUFTemplate($field['type']),
                'required' => $field['required'] ? 'yes' : 'no',
                'label' => $field['label'],
                'name' => $field['name'],
                'is_meta' => $this->shouldBeMeta($field['name']) ? 'yes' : 'no',
                'help' => $field['help_text'] ?? '',
                'css' => '',
                'placeholder' => $field['placeholder'] ?? '',
                'default' => $field['default'] ?? '',
                'wpuf_cond' => $this->getDefaultConditionals(),
                'wpuf_visibility' => $this->getDefaultVisibility(),
                'width' => 'large' // Default width like WPUF templates
                // Note: 'id' will be set automatically by WPUF when loading from child posts
            ];

            // Add field-specific properties
            $this->addFieldSpecificProperties($wpuf_field, $field);
            
            $wpuf_fields[] = $wpuf_field;
        }

        return $wpuf_fields;
    }

    /**
     * Get default WPUF conditionals (matching WPUF's actual format)
     *
     * @return array
     */
    private function getDefaultConditionals() {
        return [
            'condition_status' => 'no',
            'cond_field' => [],
            'cond_operator' => ['='],
            'cond_option' => ['- Select -'],
            'cond_logic' => 'all'
        ];
    }

    /**
     * Get default WPUF visibility settings (matching WPUF's actual format)
     *
     * @return array
     */
    private function getDefaultVisibility() {
        return [
            'selected' => 'everyone',
            'choices' => []
        ];
    }

    /**
     * Map field type to WPUF input type
     *
     * @param string $type
     * @return string
     */
    private function mapToWPUFInputType($type) {
        $mapping = [
            // Free WPUF fields  
            'text' => 'text',
            'email' => 'email',
            'textarea' => 'textarea',
            'select' => 'select',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'url' => 'url',
            
            // Pro WPUF fields (matching Field_File.php input_type)
            'file' => 'file_upload',
            'number' => 'numeric_text',
            'date' => 'date',
            'time' => 'time',
            'tel' => 'text',
            'phone' => 'text',
            'country' => 'select',
            'address' => 'address_field',
            'map' => 'google_map',
            'rating' => 'ratings',
            
            // Free image fields
            'image' => 'image_upload',
            'featured_image' => 'image_upload'
        ];

        return $mapping[$type] ?? 'text';
    }

    /**
     * Map field type to WPUF template
     *
     * @param string $type
     * @return string
     */
    private function mapToWPUFTemplate($type) {
        $mapping = [
            // Free WPUF fields (from Field_Manager.php)
            'text' => 'text_field',
            'email' => 'email_address', 
            'textarea' => 'textarea_field',
            'select' => 'dropdown_field',
            'radio' => 'radio_field',
            'checkbox' => 'checkbox_field',
            'url' => 'website_url',
            
            // Pro WPUF fields (from Fields_Manager.php)
            'file' => 'file_upload',               // Pro field
            'number' => 'numeric_text_field',      // Pro field  
            'date' => 'date_field',                // Pro field
            'time' => 'time_field',                // Pro field
            'tel' => 'phone_field',                // Pro field
            'phone' => 'phone_field',              // Pro field
            'country' => 'country_list_field',     // Pro field
            'address' => 'address_field',          // Pro field
            'map' => 'google_map',                 // Pro field
            'rating' => 'ratings',                 // Pro field
            
            // Free image fields
            'image' => 'image_upload',             // Free field
            'featured_image' => 'featured_image',  // Free field
        ];

        return $mapping[$type] ?? 'text_field';
    }

    /**
     * Determine if field should be meta
     *
     * @param string $name
     * @return bool
     */
    private function shouldBeMeta($name) {
        $post_fields = ['post_title', 'post_content', 'post_excerpt', 'post_tags', 'post_category'];
        return !in_array($name, $post_fields);
    }

    /**
     * Add field-specific properties
     *
     * @param array &$wpuf_field
     * @param array $field
     */
    private function addFieldSpecificProperties(&$wpuf_field, $field) {
        $type = $field['type'];

        switch ($type) {
            case 'textarea':
                $wpuf_field['rows'] = '5';
                $wpuf_field['cols'] = '25';
                $wpuf_field['rich'] = 'no';
                $wpuf_field['restriction_to'] = 'max';
                $wpuf_field['restriction_type'] = 'character';
                $wpuf_field['text_editor_control'] = [];
                if ($wpuf_field['name'] === 'post_content') {
                    $wpuf_field['rich'] = 'yes';
                    $wpuf_field['insert_image'] = 'yes';
                }
                break;

            case 'select':
            case 'radio':
            case 'checkbox':
                $wpuf_field['first'] = __('- Select -', 'wp-user-frontend');
                $wpuf_field['options'] = [];
                if (isset($field['options'])) {
                    if (is_array($field['options'])) {
                        foreach ($field['options'] as $key => $value) {
                            if (is_string($key)) {
                                $wpuf_field['options'][$key] = $value;
                            } else {
                                $wpuf_field['options'][$value] = $value;
                            }
                        }
                    }
                }
                
                // Special handling for taxonomy fields
                if ($wpuf_field['name'] === 'category' || $wpuf_field['name'] === 'post_category') {
                    $wpuf_field['input_type'] = 'taxonomy';
                    $wpuf_field['template'] = 'taxonomy';
                    $wpuf_field['type'] = 'select';
                    $wpuf_field['orderby'] = 'name';
                    $wpuf_field['order'] = 'ASC';
                    $wpuf_field['exclude_type'] = 'exclude';
                    $wpuf_field['exclude'] = [];
                    $wpuf_field['woo_attr'] = 'no';
                    $wpuf_field['woo_attr_vis'] = 'no';
                    $wpuf_field['show_inline'] = false;
                }
                break;

            case 'file':
                $wpuf_field['max_size'] = '1024';
                $wpuf_field['count'] = '1';
                
                // Set proper file extensions and templates based on field name/type
                if (strpos($wpuf_field['name'], 'image') !== false || strpos(strtolower($wpuf_field['label']), 'image') !== false) {
                    // Image upload field
                    $wpuf_field['input_type'] = 'image_upload';
                    $wpuf_field['template'] = 'image_upload';
                    $wpuf_field['extension'] = ['images'];
                    if ($wpuf_field['name'] === 'featured_image') {
                        $wpuf_field['template'] = 'featured_image';
                        $wpuf_field['button_label'] = __('Featured Image', 'wp-user-frontend');
                    }
                } else {
                    // Regular file upload (Pro field)
                    $wpuf_field['input_type'] = 'file_upload';
                    $wpuf_field['template'] = 'file_upload';
                    $wpuf_field['extension'] = ['images', 'pdf', 'office'];
                }
                break;

            case 'number':
                $wpuf_field['step_text_field'] = '1';
                $wpuf_field['min_value_field'] = '0';
                $wpuf_field['max_value_field'] = '';
                break;

            case 'text':
            case 'email':
            case 'url':
            case 'tel':
                $wpuf_field['size'] = '40';
                if ($wpuf_field['name'] === 'post_title') {
                    $wpuf_field['restriction_to'] = 'max';
                    $wpuf_field['restriction_type'] = 'character';
                } elseif ($wpuf_field['name'] === 'tags' || $wpuf_field['name'] === 'post_tags') {
                    $wpuf_field['template'] = 'post_tags';
                }
                break;
        }
    }

    /**
     * Get default form settings
     *
     * @param string $title
     * @return array
     */
    private function getDefaultFormSettings($title) {
        return [
            'post_type' => 'post',
            'post_status' => 'publish',
            'default_cat' => '-1',
            'post_permission' => '-1',
            'guest_post' => 'false',
            'message_restrict' => __('This page is restricted. Please {login} / {register} to view this page.', 'wp-user-frontend'),
            'redirect_to' => 'post',
            'comment_status' => 'open',
            'submit_text' => sprintf(__('Create %s', 'wp-user-frontend'), $title),
            'submit_button_cond' => [
                'condition_status' => 'no',
                'cond_logic' => 'any',
                'conditions' => [
                    [
                        'name' => '',
                        'operator' => '=',
                        'option' => ''
                    ]
                ]
            ],
            'edit_post_status' => 'publish',
            'edit_redirect_to' => 'same',
            'update_message' => sprintf(__('%s has been updated successfully. <a target="_blank" href="{link}">View %s</a>', 'wp-user-frontend'), $title, strtolower($title)),
            'edit_url' => '',
            'update_text' => sprintf(__('Update %s', 'wp-user-frontend'), $title),
            'form_template' => 'ai_generated_form',
            'notification' => [
                'new' => 'on',
                'new_to' => get_option('admin_email', ''),
                'new_subject' => sprintf(__('New %s has been created', 'wp-user-frontend'), strtolower($title)),
                'new_body' => sprintf(__('Hi,
A new %s has been created in your site {sitename} ({siteurl}).

Here is the details:
Title: {post_title}
Description: {post_content}
Author: {author}
URL: {permalink}
Edit URL: {editlink}', 'wp-user-frontend'), strtolower($title)),
                'edit' => 'off',
                'edit_to' => get_option('admin_email', ''),
                'edit_subject' => sprintf(__('%s has been edited', 'wp-user-frontend'), $title),
                'edit_body' => sprintf(__('Hi,
The %s "{post_title}" has been updated

Here is the details:
Title: {post_title}
Description: {post_content}
Author: {author}
URL: {permalink}
Edit URL: {editlink}', 'wp-user-frontend'), strtolower($title))
            ]
        ];
    }
}