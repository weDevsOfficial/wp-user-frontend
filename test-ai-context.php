<?php
/**
 * Test AI Form Builder with Context Management
 * 
 * This demonstrates how the AI should handle different types of user prompts
 */

require_once 'includes/AI/FormGenerator.php';

use WeDevs\Wpuf\AI\FormGenerator;

// Test scenarios
$test_scenarios = [
    [
        'name' => 'Valid Form Creation',
        'prompt' => 'Create a contact form with name, email, and message fields',
        'context' => [],
        'expected' => 'success'
    ],
    [
        'name' => 'Form Modification - Make Field Required',
        'prompt' => 'Make the email field required',
        'context' => [
            'form_id' => 'form_123',
            'current_form' => [
                'fields' => [
                    ['id' => 1, 'name' => 'name', 'type' => 'text_field', 'required' => false],
                    ['id' => 2, 'name' => 'email', 'type' => 'email_address', 'required' => false],
                    ['id' => 3, 'name' => 'message', 'type' => 'textarea_field', 'required' => false]
                ]
            ]
        ],
        'expected' => 'modify'
    ],
    [
        'name' => 'Invalid Request - Non-Form',
        'prompt' => 'Write me a blog post about WordPress',
        'context' => [],
        'expected' => 'error'
    ],
    [
        'name' => 'Invalid Request - Programming Help',
        'prompt' => 'How do I install WordPress?',
        'context' => [],
        'expected' => 'error'
    ],
    [
        'name' => 'Add Field to Existing Form',
        'prompt' => 'Add a phone number field',
        'context' => [
            'form_id' => 'form_123',
            'current_form' => [
                'fields' => [
                    ['id' => 1, 'name' => 'name', 'type' => 'text_field'],
                    ['id' => 2, 'name' => 'email', 'type' => 'email_address']
                ]
            ]
        ],
        'expected' => 'modify'
    ],
    [
        'name' => 'Change Settings',
        'prompt' => 'Change the submit button text to "Send Message"',
        'context' => [
            'form_id' => 'form_123',
            'current_form' => [
                'settings' => [
                    'submit_button_text' => 'Submit'
                ]
            ]
        ],
        'expected' => 'modify'
    ],
    [
        'name' => 'Ambiguous Request',
        'prompt' => 'Make it required',
        'context' => [
            'form_id' => 'form_123',
            'current_form' => [
                'fields' => [
                    ['id' => 1, 'name' => 'name', 'type' => 'text_field'],
                    ['id' => 2, 'name' => 'email', 'type' => 'email_address']
                ]
            ]
        ],
        'expected' => 'error'
    ]
];

// Test with Google Gemini
echo "=== AI Form Builder Context Testing ===\n\n";

$generator = new FormGenerator();

foreach ($test_scenarios as $scenario) {
    echo "Test: {$scenario['name']}\n";
    echo "Prompt: {$scenario['prompt']}\n";
    
    $options = [
        'conversation_context' => $scenario['context'],
        'session_id' => 'test_session_' . time()
    ];
    
    try {
        $result = $generator->generate_form($scenario['prompt'], $options);
        
        if (isset($result['error']) && $result['error']) {
            if ($scenario['expected'] === 'error') {
                echo "✅ PASS - Error correctly returned\n";
                echo "   Error Type: " . ($result['error_type'] ?? 'unknown') . "\n";
                echo "   Message: " . $result['message'] . "\n";
            } else {
                echo "❌ FAIL - Unexpected error\n";
                echo "   Message: " . $result['message'] . "\n";
            }
        } else if (isset($result['action'])) {
            if ($result['action'] === 'modify' && $scenario['expected'] === 'modify') {
                echo "✅ PASS - Modification action detected\n";
                echo "   Type: " . ($result['modification_type'] ?? 'unknown') . "\n";
                echo "   Target: " . ($result['target'] ?? 'unknown') . "\n";
            } else if ($result['action'] === 'create' && $scenario['expected'] === 'success') {
                echo "✅ PASS - Form created successfully\n";
                echo "   Title: " . ($result['form_title'] ?? 'Untitled') . "\n";
                echo "   Fields: " . count($result['fields'] ?? []) . "\n";
            } else {
                echo "❓ Result - Action: " . $result['action'] . "\n";
            }
        } else if ($scenario['expected'] === 'success') {
            // Check if it's a form creation response
            if (isset($result['form_title']) && isset($result['fields'])) {
                echo "✅ PASS - Form created\n";
                echo "   Title: " . $result['form_title'] . "\n";
                echo "   Fields: " . count($result['fields']) . "\n";
            } else {
                echo "❌ FAIL - Invalid response structure\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n---\n\n";
}

// Example of proper conversation flow
echo "=== Proper Conversation Flow Example ===\n\n";

// 1. Initial form creation
echo "User: Create a contact form\n";
$result1 = $generator->generate_form('Create a contact form', ['conversation_context' => []]);
echo "AI: Created form with " . count($result1['fields'] ?? []) . " fields\n\n";

// 2. Modification request
echo "User: Make the email field required\n";
$context2 = [
    'form_id' => 'form_123',
    'current_form' => $result1
];
$result2 = $generator->generate_form('Make the email field required', ['conversation_context' => $context2]);
echo "AI: " . ($result2['message'] ?? 'Modified form') . "\n\n";

// 3. Add new field
echo "User: Add a phone number field\n";
$context3 = [
    'form_id' => 'form_123',
    'current_form' => $result1,
    'modifications' => [$result2]
];
$result3 = $generator->generate_form('Add a phone number field', ['conversation_context' => $context3]);
echo "AI: " . ($result3['message'] ?? 'Added field') . "\n\n";

// 4. Invalid request
echo "User: Write me a blog post\n";
$result4 = $generator->generate_form('Write me a blog post', ['conversation_context' => $context3]);
if (isset($result4['error'])) {
    echo "AI: " . $result4['message'] . "\n";
}