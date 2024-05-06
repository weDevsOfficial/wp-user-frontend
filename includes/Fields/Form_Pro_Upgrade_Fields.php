<?php

namespace WeDevs\Wpuf\Fields;

class Form_Pro_Upgrade_Fields {
    private $fields = [];
    public function __construct() {
        $this->fields['action_hook']           = new Form_Field_Hook();
        $this->fields['address_field']         = new Form_Field_Address();
        $this->fields['repeat_field']          = new Form_Field_Repeat();
        $this->fields['country_list_field']    = new Form_Field_Country();
        $this->fields['date_field']            = new Form_Field_Date();
        $this->fields['embed']                 = new Form_Field_Embed();
        $this->fields['file_upload']           = new Form_Field_File();
        $this->fields['google_map']            = new Form_Field_GMap();
        $this->fields['numeric_text_field']    = new Form_Field_Numeric();
        $this->fields['ratings']               = new Form_Field_Rating();
        $this->fields['really_simple_captcha'] = new Form_Field_Really_Simple_Captcha();
        $this->fields['shortcode']             = new Form_Field_Shortcode();
        $this->fields['step_start']            = new Form_Field_Step();
        $this->fields['toc']                   = new Form_Field_Toc();
        $this->fields['math_captcha']          = new Form_Field_Math_Captcha();
        $this->fields['qr_code']               = new Form_Field_QR_Code();
    }

    public function get_fields() {
        return $this->fields;
    }
}

/**
 * Address Field Class
 */
class Form_Field_Address extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Address Field', 'wp-user-frontend' );
        $this->input_type = 'address_field';
        $this->icon       = 'address-card-o';
    }
}

/**
 * Country Field Class
 */
class Form_Field_Country extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Country List', 'wp-user-frontend' );
        $this->input_type = 'country_list_field';
        $this->icon       = 'globe';
    }
}

/**
 * Date Field Class
 */
class Form_Field_Date extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Date / Time', 'wp-user-frontend' );
        $this->input_type = 'date_field';
        $this->icon       = 'calendar-o';
    }
}

class Form_Field_Embed extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Embed', 'wp-user-frontend' );
        $this->input_type = 'embed';
        $this->icon       = 'address-card-o';
    }
}

/**
 * File Field Class
 */
class Form_Field_File extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'File Upload', 'wp-user-frontend' );
        $this->input_type = 'file_upload';
        $this->icon       = 'upload';
    }
}

/**
 * Text Field Class
 */
class Form_Field_GMap extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Google Map', 'wp-user-frontend' );
        $this->input_type = 'google_map';
        $this->icon       = 'map-marker';
    }
}

/**
 * Text Field Class
 */
class Form_Field_Hook extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Action Hook', 'wp-user-frontend' );
        $this->input_type = 'action_hook';
        $this->icon       = 'anchor';
    }
}

/**
 * Numeric Field Class
 */
class Form_Field_Numeric extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Numeric Field', 'wp-user-frontend' );
        $this->input_type = 'numeric_text_field';
        $this->icon       = 'hashtag';
    }
}

/**
 * Rating Field Class
 */
class Form_Field_Rating extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Ratings', 'wp-user-frontend' );
        $this->input_type = 'ratings';
        $this->icon       = 'star-half-o';
    }
}

/**
 * Rating Field Class
 */
class Form_Field_Linear_Scale extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Linear Scale', 'wp-user-frontend' );
        $this->input_type = 'linear_scale';
        $this->icon       = 'ellipsis-h';
    }
}

/**
 * Checkbox Grids Field Class
 */
class Form_Field_Checkbox_Grid extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Checkbox Grid', 'wp-user-frontend' );
        $this->input_type = 'checkbox_grid';
        $this->icon       = 'th';
    }
}

/**
 * Multiple Choice Grids Field Class
 */
class Form_Field_Multiple_Choice_Grid extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Multiple Choice Grid', 'wp-user-frontend' );
        $this->input_type = 'multiple_choice_grid';
        $this->icon       = 'braille';
    }
}

/**
 * Repeat Field Class
 */
class Form_Field_Repeat extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Repeat Field', 'wp-user-frontend' );
        $this->input_type = 'repeat_field';
        $this->icon       = 'text-width';
    }
}

/**
 * Really Simple Captcha Field Class
 */
class Form_Field_Really_Simple_Captcha extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Really Simple Captcha', 'wp-user-frontend' );
        $this->input_type = 'shortcode';
        $this->icon       = '';
    }
}

/**
 * Shortcode Field Class
 */
class Form_Field_Shortcode extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Shortcode', 'wp-user-frontend' );
        $this->input_type = 'shortcode';
        $this->icon       = 'calendar-o';
    }
}

/**
 * Step Field Class
 */
class Form_Field_Step extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Step Start', 'wp-user-frontend' );
        $this->input_type = 'step_start';
        $this->icon       = 'step-forward';
    }
}

/**
 * TOC Field Class
 */
class Form_Field_Toc extends Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Terms & Conditions', 'wp-user-frontend' );
        $this->input_type = 'toc';
        $this->icon       = 'file-text';
    }
}

/**
 * Math Capctha Class
 */
class Form_Field_Math_Captcha extends Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Math Captcha', 'wp-user-frontend' );
        $this->input_type = 'math_captcha';
        $this->icon       = 'hashtag';
    }
}

/**
 * QR Code Class
 */
class Form_Field_QR_Code extends Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'QR Code', 'wp-user-frontend' );
        $this->input_type = 'qr_code';
        $this->icon       = 'address-card-o';
    }
}
