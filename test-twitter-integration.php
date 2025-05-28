<?php declare(strict_types=1); 
/**
 * Twitter Field Integration Test
 * 
 * This file tests if the Twitter field has been properly integrated
 * into both the new and old field manager systems.
 */

// Include WordPress core
require_once('../../../wp-load.php');

// Test Field Manager Integration
echo "<h2>Twitter Field Manager Integration Test</h2>\n";

// Test New Field Manager
echo "<h3>1. New Field Manager (Admin/Forms/Field_Manager)</h3>\n";
try {
    $new_field_manager = new WeDevs\Wpuf\Admin\Forms\Field_Manager();
    $fields = $new_field_manager->get_fields();
    
    if (isset($fields['twitter_url'])) {
        echo "✅ Twitter field (twitter_url) found in new field manager\n";
        echo "Field class: " . get_class($fields['twitter_url']) . "\n";
        // Note: Properties are protected, can't access directly in test
    } else {
        echo "❌ Twitter field NOT found in new field manager\n";
    }
    
    // Test field groups
    $field_groups = $new_field_manager->get_field_groups();
    $twitter_in_custom_fields = false;
    
    foreach ($field_groups as $group) {
        if ($group['id'] === 'custom-fields') {
            if (in_array('twitter_url', $group['fields'])) {
                echo "✅ Twitter field found in Custom Fields group\n";
                $twitter_in_custom_fields = true;
            }
            break;
        }
    }
    
    if (!$twitter_in_custom_fields) {
        echo "❌ Twitter field NOT found in Custom Fields group\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing new field manager: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Old Field Manager
echo "<h3>2. Old Field Manager (class-field-manager)</h3>\n";
try {
    $old_field_manager = new WPUF_Field_Manager();
    $fields = $old_field_manager->get_js_settings();
    
    if (isset($fields['twitter_url'])) {
        echo "✅ Twitter field (twitter_url) found in old field manager\n";
        echo "Field settings available: " . print_r(array_keys($fields['twitter_url']), true) . "\n";
    } else {
        echo "❌ Twitter field NOT found in old field manager\n";
    }
    
    // Test field groups
    $field_groups = $old_field_manager->get_field_groups();
    $twitter_in_custom_fields = false;
    
    foreach ($field_groups as $group) {
        if ($group['id'] === 'custom-fields') {
            if (in_array('twitter_url', $group['fields'])) {
                echo "✅ Twitter field found in Custom Fields group\n";
                $twitter_in_custom_fields = true;
            }
            break;
        }
    }
    
    if (!$twitter_in_custom_fields) {
        echo "❌ Twitter field NOT found in Custom Fields group\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing old field manager: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Class Existence
echo "<h3>3. Class Existence Test</h3>\n";

if (class_exists('WeDevs\Wpuf\Fields\Form_Field_Twitter')) {
    echo "✅ New Twitter field class exists: WeDevs\Wpuf\Fields\Form_Field_Twitter\n";
} else {
    echo "❌ New Twitter field class NOT found\n";
}

if (class_exists('WPUF_Form_Field_Twitter')) {
    echo "✅ Legacy Twitter field class exists: WPUF_Form_Field_Twitter\n";
} else {
    echo "❌ Legacy Twitter field class NOT found\n";
}

echo "\n";

// Test File Existence
echo "<h3>4. File Existence Test</h3>\n";

$files_to_check = [
    'includes/Fields/Form_Field_Twitter.php',
    'includes/Fields/class-field-twitter.php',
    'assets/js-templates/form-components.php',
    'assets/js/wpuf-form-builder-components.js',
    'assets/js/frontend-form.js',
    'assets/css/wpuf.css'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ File exists: $file\n";
    } else {
        echo "❌ File missing: $file\n";
    }
}

echo "\n";

// Test Vue Template
echo "<h3>5. Vue Template Test</h3>\n";
$form_components_content = file_get_contents(__DIR__ . '/assets/js-templates/form-components.php');
if (strpos($form_components_content, 'tmpl-wpuf-form-twitter_url') !== false) {
    echo "✅ Twitter URL template found in form-components.php\n";
} else {
    echo "❌ Twitter URL template NOT found in form-components.php\n";
}

// Test Vue Component
$vue_components_content = file_get_contents(__DIR__ . '/assets/js/wpuf-form-builder-components.js');
if (strpos($vue_components_content, 'form-twitter_url') !== false) {
    echo "✅ Twitter URL Vue component found in wpuf-form-builder-components.js\n";
} else {
    echo "❌ Twitter URL Vue component NOT found in wpuf-form-builder-components.js\n";
}

echo "\n<h3>Integration Summary</h3>\n";
echo "If all tests show ✅, the Twitter field should now appear in the form builder.\n";
echo "If any tests show ❌, there may be remaining integration issues.\n\n";
echo "To see the field in action:\n";
echo "1. Go to WordPress Admin → User Frontend → Post Forms\n";
echo "2. Create or edit a form\n";
echo "3. Look for 'Social Field – X (formerly Twitter)' in the Custom Fields section\n";
echo "4. Drag and drop it to your form\n";
?>
