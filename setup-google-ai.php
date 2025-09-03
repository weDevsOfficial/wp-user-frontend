<?php
/**
 * Quick setup script for Google Gemini in WordPress
 * Run: wp eval-file wp-content/plugins/wp-user-frontend/setup-google-ai.php
 */

// REPLACE THIS WITH YOUR ACTUAL API KEY
$api_key = 'YOUR_GOOGLE_API_KEY_HERE';

if ($api_key === 'YOUR_GOOGLE_API_KEY_HERE') {
    echo "⚠️  Please edit this file and add your Google API key first!\n";
    echo "Get one at: https://aistudio.google.com/\n";
    exit(1);
}

// Set up the AI settings
$settings = [
    'ai_provider' => 'google',
    'ai_model' => 'gemini-1.5-flash',
    'ai_api_key' => $api_key
];

// Update WordPress option
update_option('wpuf_ai', $settings);

// Verify it was saved
$saved = get_option('wpuf_ai');

if ($saved && $saved['ai_api_key'] === $api_key) {
    echo "✅ Google Gemini configured successfully!\n\n";
    echo "Settings saved:\n";
    echo "- Provider: Google\n";
    echo "- Model: gemini-1.5-flash (Free tier)\n";
    echo "- API Key: " . substr($api_key, 0, 10) . "...***\n\n";
    echo "Now you can:\n";
    echo "1. Go to WP Admin → User Frontend → AI Form Builder\n";
    echo "2. Try creating forms with prompts like:\n";
    echo "   - 'Create a contact form'\n";
    echo "   - 'Build a job application form'\n";
    echo "   - 'Make an event registration form'\n";
} else {
    echo "❌ Failed to save settings\n";
}