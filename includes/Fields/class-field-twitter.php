<?php

/**
 * Twitter Field Class (Legacy Compatibility)
 * 
 * This class provides compatibility with the old WPUF field manager system
 * by wrapping the new namespaced Twitter field class
 */

use WeDevs\Wpuf\Fields\Form_Field_Twitter as NewTwitterField;

class WPUF_Form_Field_Twitter extends NewTwitterField {
    // This class extends the new Twitter field to maintain backward compatibility
    // No additional methods needed as the parent class handles everything
}
?>
