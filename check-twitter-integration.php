<?php declare(strict_types=1); 
/**
 * Simple Twitter Field Integration Check
 * Tests file existence and basic integration without WordPress bootstrap
 */

echo "=== Twitter Field Integration Check ===\n\n";

// Check if files exist
$required_files = [
    'includes/Fields/Form_Field_Twitter.php' => 'New Twitter field class',
    'includes/Fields/class-field-twitter.php' => 'Legacy Twitter field class',
    'assets/js-templates/form-components.php' => 'Vue templates',
    'assets/js/wpuf-form-builder-components.js' => 'Vue components',
    'assets/js/frontend-form.js' => 'Frontend validation',
    'assets/css/wpuf.css' => 'CSS styling'
];

echo "1. File Existence Check:\n";
foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $file ($description)\n";
    } else {
        echo "❌ $file ($description) - MISSING\n";
    }
}

echo "\n2. Content Verification:\n";

// Check if twitter_url is in custom fields
$field_manager_path = 'includes/Admin/Forms/Field_Manager.php';
if (file_exists($field_manager_path)) {
    $content = file_get_contents($field_manager_path);
    if (strpos($content, "'twitter_url'") !== false) {
        echo "✅ twitter_url found in new field manager custom fields\n";
    } else {
        echo "❌ twitter_url NOT found in new field manager custom fields\n";
    }
}

$old_field_manager_path = 'includes/class-field-manager.php';
if (file_exists($old_field_manager_path)) {
    $content = file_get_contents($old_field_manager_path);
    if (strpos($content, "'twitter_url'") !== false) {
        echo "✅ twitter_url found in old field manager custom fields\n";
    } else {
        echo "❌ twitter_url NOT found in old field manager custom fields\n";
    }
}

// Check Vue template
$form_components_path = 'assets/js-templates/form-components.php';
if (file_exists($form_components_path)) {
    $content = file_get_contents($form_components_path);
    if (strpos($content, 'tmpl-wpuf-form-twitter_url') !== false) {
        echo "✅ Twitter Vue template found in form-components.php\n";
    } else {
        echo "❌ Twitter Vue template NOT found in form-components.php\n";
    }
}

// Check Vue component
$vue_components_path = 'assets/js/wpuf-form-builder-components.js';
if (file_exists($vue_components_path)) {
    $content = file_get_contents($vue_components_path);
    if (strpos($content, "'form-twitter_url'") !== false || strpos($content, '"form-twitter_url"') !== false) {
        echo "✅ Twitter Vue component found in wpuf-form-builder-components.js\n";
    } else {
        echo "❌ Twitter Vue component NOT found in wpuf-form-builder-components.js\n";
    }
}

// Check frontend validation
$frontend_js_path = 'assets/js/frontend-form.js';
if (file_exists($frontend_js_path)) {
    $content = file_get_contents($frontend_js_path);
    if (strpos($content, 'isValidTwitterURL') !== false) {
        echo "✅ Twitter validation function found in frontend-form.js\n";
    } else {
        echo "❌ Twitter validation function NOT found in frontend-form.js\n";
    }
}

// Check CSS styling
$css_path = 'assets/css/wpuf.css';
if (file_exists($css_path)) {
    $content = file_get_contents($css_path);
    if (strpos($content, 'wpuf-twitter-field') !== false) {
        echo "✅ Twitter field CSS styling found in wpuf.css\n";
    } else {
        echo "❌ Twitter field CSS styling NOT found in wpuf.css\n";
    }
}

echo "\n3. Class Registration Check:\n";

// Check new field manager registration
if (file_exists('includes/Admin/Forms/Field_Manager.php')) {
    $content = file_get_contents('includes/Admin/Forms/Field_Manager.php');
    if (strpos($content, "'twitter_url' => new Form_Field_Twitter()") !== false) {
        echo "✅ Twitter field registered in new field manager\n";
    } else {
        echo "❌ Twitter field NOT registered in new field manager\n";
    }
}

// Check old field manager registration
if (file_exists('includes/class-field-manager.php')) {
    $content = file_get_contents('includes/class-field-manager.php');
    if (strpos($content, "'twitter_url' => new WPUF_Form_Field_Twitter()") !== false) {
        echo "✅ Twitter field registered in old field manager\n";
    } else {
        echo "❌ Twitter field NOT registered in old field manager\n";
    }
}

echo "\n=== Summary ===\n";
echo "If all items show ✅, the Twitter field integration is complete.\n";
echo "The field should now appear in the WordPress admin form builder under 'Custom Fields'.\n";
echo "Field name: 'Social Field – X (formerly Twitter)'\n";
echo "Field type: twitter_url\n\n";

echo "To test in WordPress:\n";
echo "1. Go to WP Admin → User Frontend → Post Forms\n";
echo "2. Create or edit a form\n";
echo "3. Look for the Twitter field in the Custom Fields panel\n";
echo "4. Drag it to your form and configure it\n";
?>
