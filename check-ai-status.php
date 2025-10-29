<?php
/**
 * Diagnostic script to check AI Form Builder status
 * Run: wp eval-file wp-content/plugins/wp-user-frontend/check-ai-status.php
 */

// Guard against direct web access
if (!defined('ABSPATH') && !defined('WP_CLI')) {
    exit('This script must be run through WordPress or WP-CLI.');
}

echo "=== WPUF AI Form Builder Status Check ===\n\n";

// Check if settings exist
if (function_exists('get_option')) {
    $settings = get_option('wpuf_ai', []);
} else {
    echo "❌ WordPress functions not available\n";
    exit(1);
}

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
if (function_exists('get_rest_url')) {
    $rest_url = get_rest_url(null, 'wpuf/v1/ai-form-builder/generate');
    echo "- Generate: $rest_url\n";
} else {
    echo "- Generate: (WordPress REST API not available)\n";
}

// Test the form generator
echo "\n=== Quick Test ===\n";
if (!empty($settings['ai_api_key'])) {
    echo "Testing with {$settings['ai_provider']} provider...\n";

    // Load Composer autoloader
    $autoload = __DIR__ . '/vendor/autoload.php';
    if ( file_exists( $autoload ) ) {
        require_once $autoload;
    }

    try {
        // Ensure WordPress database is available
        if (!function_exists('get_option')) {
            throw new Exception('WordPress functions not available');
        }

        $generator = new \WeDevs\Wpuf\AI\FormGenerator();

        $result = $generator->generate_form('Create a contact form');

        if (isset($result['success']) && $result['success']) {
            echo "✅ AI provider works!\n";
            echo "Form title: " . ($result['form_title'] ?? 'N/A') . "\n";
            
            // Query wpuf_fields table for accurate field count
            global $wpdb;
            $field_count = 0;
            if ($wpdb) {
                // Try wpuf_fields table first (if exists)
                $table_name = $wpdb->prefix . 'wpuf_fields';
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                
                if ($table_exists && isset($result['form_id'])) {
                    $field_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name WHERE form_id = %d",
                        $result['form_id']
                    ));
                } else {
                    // Fallback to counting fields from result
                    $field_count = count($result['fields'] ?? []);
                }
            } else {
                $field_count = count($result['fields'] ?? []);
            }
            
            echo "Fields: $field_count\n";
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
    // Guard admin_url() call
    if (function_exists('admin_url')) {
        echo "1. Go to: " . admin_url('admin.php?page=wpuf-ai-form-builder') . "\n";
    } else {
        echo "1. Go to: WordPress Admin > WPUF > AI Form Builder\n";
    }
    echo "2. Try creating a form with a prompt\n";
    echo "3. Check browser console for any errors\n";
}