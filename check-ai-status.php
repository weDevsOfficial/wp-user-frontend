<?php
/**
 * Diagnostic script to check AI Form Builder status
 * Run: wp eval-file wp-content/plugins/wp-user-frontend/check-ai-status.php
 */

echo "=== WPUF AI Form Builder Status Check ===\n\n";

// Check if settings exist
$settings = get_option('wpuf_ai', []);

if (empty($settings)) {
    echo "❌ No AI settings found\n";
    echo "Run setup-google-ai.php first\n";
    exit(1);
}

echo "✅ Settings found:\n";
echo "- Provider: " . ($settings['ai_provider'] ?? 'not set') . "\n";
echo "- Model: " . ($settings['ai_model'] ?? 'not set') . "\n";
echo "- API Key: " . (empty($settings['ai_api_key']) ? '❌ NOT SET' : '✅ Set (' . substr($settings['ai_api_key'], 0, 10) . '...***') . "\n\n";

// Check if AI files exist
$files_to_check = [
    'includes/AI/FormGenerator.php',
    'includes/AI/RestController.php',
    'includes/AI/AIClientLoader.php',
    'includes/AI_Manager.php',
];

echo "File Status:\n";
foreach ($files_to_check as $file) {
    $full_path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($full_path)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file (missing)\n";
    }
}

// Check REST API endpoint
echo "\nREST API Endpoints:\n";
$rest_url = rest_url('wpuf/v1/ai-form-builder/generate');
echo "- Generate: $rest_url\n";

// Test the form generator
echo "\n=== Quick Test ===\n";
if (!empty($settings['ai_api_key'])) {
    echo "Testing with {$settings['ai_provider']} provider...\n";

    // Load the necessary files
    require_once plugin_dir_path(__FILE__) . 'includes/AI/AIClientLoader.php';
    require_once plugin_dir_path(__FILE__) . 'includes/AI/FormGenerator.php';

    try {
        $generator = new \WeDevs\Wpuf\AI\FormGenerator();

        $result = $generator->generate_form('Create a contact form');

        if (isset($result['success']) && $result['success']) {
            echo "✅ AI provider works!\n";
            echo "Form title: " . ($result['form_title'] ?? 'N/A') . "\n";
            echo "Fields: " . count($result['fields'] ?? []) . "\n";
        } else {
            echo "❌ AI provider test failed\n";
        }

    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Skipping test - API key not configured\n";
}

echo "\n=== Next Steps ===\n";
if (empty($settings['ai_api_key'])) {
    echo "1. Get a free Google API key at: https://aistudio.google.com/\n";
    echo "2. Run: wp eval-file wp-content/plugins/wp-user-frontend/setup-google-ai.php\n";
} else {
    echo "1. Go to: " . admin_url('admin.php?page=wpuf-ai-form-builder') . "\n";
    echo "2. Try creating a form with a prompt\n";
    echo "3. Check browser console for any errors\n";
}