<?php
/**
 * Test Google Gemini with WPUF System Prompt
 */

$API_KEY = 'AIzaSyB4CgVA1tpcJMHmP-2RgzBb7z9QKhkFRN0';

// Load system prompt
$system_prompt = file_get_contents(__DIR__ . '/includes/AI/system-prompt.md');

// Test scenarios
$tests = [
    [
        'name' => '✅ Valid: Contact Form',
        'prompt' => 'Create a contact form with name, email and message'
    ],
    [
        'name' => '❌ Invalid: Blog Post',
        'prompt' => 'Write me a blog post about WordPress'
    ],
    [
        'name' => '⚠️ Pro Field: Date Picker',
        'prompt' => 'Create an event registration form with date picker'
    ],
    [
        'name' => '✅ Modification: Make Required',
        'prompt' => 'Make the email field required',
        'context' => [
            'current_form' => [
                'fields' => [
                    ['id' => 1, 'name' => 'email', 'type' => 'email_address', 'required' => false]
                ]
            ]
        ]
    ]
];

function test_google_ai($prompt, $context = []) {
    global $API_KEY, $system_prompt;
    
    $model = 'gemini-1.5-flash';
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$API_KEY}";
    
    // Add context to system prompt if provided
    $full_prompt = $system_prompt;
    if (!empty($context)) {
        $full_prompt .= "\n\n## CURRENT CONTEXT\n" . json_encode($context, JSON_PRETTY_PRINT);
    }
    
    $full_prompt .= "\n\nUser request: " . $prompt;
    
    $body = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $full_prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2000,
            'responseMimeType' => 'application/json'
        ]
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $data['candidates'][0]['content']['parts'][0]['text'];
            return json_decode($content, true);
        }
    }
    
    return ['error' => true, 'message' => 'API request failed'];
}

echo "=== Testing WPUF AI Form Builder with Google Gemini ===\n\n";

foreach ($tests as $test) {
    echo $test['name'] . "\n";
    echo "Prompt: \"" . $test['prompt'] . "\"\n";
    
    $result = test_google_ai($test['prompt'], $test['context'] ?? []);
    
    if (isset($result['error']) && $result['error']) {
        echo "Response: ERROR - " . $result['message'] . "\n";
        if (isset($result['error_type'])) {
            echo "Type: " . $result['error_type'] . "\n";
        }
    } else if (isset($result['warning'])) {
        echo "Response: WARNING - " . $result['message'] . "\n";
        echo "Alternative: " . ($result['alternative_used'] ?? 'N/A') . "\n";
    } else if (isset($result['action'])) {
        if ($result['action'] === 'create') {
            echo "Response: CREATE FORM\n";
            echo "Title: " . ($result['form_title'] ?? 'Untitled') . "\n";
            echo "Fields: " . count($result['fields'] ?? []) . "\n";
        } else if ($result['action'] === 'modify') {
            echo "Response: MODIFY FORM\n";
            echo "Type: " . ($result['modification_type'] ?? 'unknown') . "\n";
            echo "Message: " . ($result['message'] ?? 'Modified') . "\n";
        }
    } else if (isset($result['form_title'])) {
        echo "Response: FORM CREATED\n";
        echo "Title: " . $result['form_title'] . "\n";
        echo "Fields: " . count($result['fields'] ?? []) . "\n";
        
        // Show field types
        if (isset($result['fields'])) {
            foreach ($result['fields'] as $field) {
                echo "  - " . $field['label'] . " (" . $field['type'] . ")\n";
            }
        }
    } else {
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
    echo "\n---\n\n";
}

echo "\n=== Test Complete ===\n";
echo "API Key: " . substr($API_KEY, 0, 10) . "...***\n";
echo "Model: gemini-1.5-flash (Free tier)\n";