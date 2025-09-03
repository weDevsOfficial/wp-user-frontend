<?php
/**
 * Test script for Google Gemini AI integration
 * 
 * Usage: 
 * 1. Add your API key below
 * 2. Run: php test-google-gemini.php
 */

// Your Google Gemini API key
$API_KEY = 'YOUR_GOOGLE_GEMINI_API_KEY_HERE';

// Test endpoint
$model = 'gemini-1.5-flash';
$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$API_KEY}";

// System prompt for form generation
$system_prompt = 'You are an expert form builder assistant. Generate a JSON structure for a form based on the user request. Return ONLY valid JSON with this exact structure:
{
    "form_title": "Form Title",
    "form_description": "Brief description",
    "fields": [
        {
            "id": 1,
            "type": "text_field",
            "label": "Field Label",
            "name": "field_name",
            "required": true,
            "placeholder": "Placeholder text"
        }
    ],
    "settings": {
        "submit_button_text": "Submit",
        "success_message": "Thank you for your submission!"
    }
}

Use these field types: text_field, email_address, textarea_field, dropdown_field, radio_field, checkbox_field';

// Test prompt
$user_prompt = "Create a simple contact form with name, email, and message fields";

// Build request
$request_body = [
    'contents' => [
        [
            'parts' => [
                ['text' => $system_prompt . "\n\nUser request: " . $user_prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 2000,
        'responseMimeType' => 'application/json'
    ]
];

echo "Testing Google Gemini API...\n";
echo "Endpoint: $endpoint\n\n";

// Make the request
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $http_code\n\n";

if ($http_code === 200) {
    $data = json_decode($response, true);
    
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        echo "✅ SUCCESS! API is working.\n\n";
        echo "Response:\n";
        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Pretty print the JSON
        $form_data = json_decode($content, true);
        if ($form_data) {
            echo json_encode($form_data, JSON_PRETTY_PRINT) . "\n";
            
            echo "\n📝 Form Details:\n";
            echo "Title: " . ($form_data['form_title'] ?? 'N/A') . "\n";
            echo "Fields: " . count($form_data['fields'] ?? []) . "\n";
        } else {
            echo $content . "\n";
        }
    } else {
        echo "❌ Unexpected response format:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ ERROR: Request failed\n";
    $error_data = json_decode($response, true);
    if (isset($error_data['error'])) {
        echo "Error: " . $error_data['error']['message'] . "\n";
        echo "Status: " . $error_data['error']['status'] . "\n";
        
        if (strpos($error_data['error']['message'], 'API key not valid') !== false) {
            echo "\n⚠️  Please check your API key!\n";
            echo "Get one at: https://aistudio.google.com/\n";
        }
    } else {
        echo "Response: $response\n";
    }
}

echo "\n---\n";
echo "To use in WordPress:\n";
echo "1. Go to WP Admin → User Frontend → Settings → AI\n";
echo "2. Set Provider: Google\n";
echo "3. Set Model: gemini-1.5-flash\n";
echo "4. Set API Key: (your key)\n";
echo "5. Save settings\n";