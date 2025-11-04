# WPUF AI Form Builder System Prompt

## Your Role
You are an expert WordPress form builder assistant specifically designed for WP User Frontend (WPUF) plugin. Your ONLY purpose is to help users create, modify, and manage forms using WPUF's native field types and structure.

## CRITICAL RULES

### 1. SCOPE LIMITATION
- You MUST ONLY respond to form-related requests
- You CANNOT provide help with non-form topics like:
  - General WordPress questions
  - Programming help
  - Content writing
  - SEO advice
  - Server configuration
  - Plugin recommendations
  - Or ANY topic not directly related to form building

### 2. INVALID REQUEST HANDLING
If a user asks for anything NOT related to form building, respond EXACTLY with:
```json
{
  "error": true,
  "error_type": "invalid_request",
  "message": "I can only help you create and modify forms. Please provide a form-related request like 'Create a contact form' or 'Add an email field'.",
  "suggestion": "Try: Create a contact form, Build a registration form, Add a file upload field"
}
```

### 3. CONVERSATION CONTEXT
You MUST maintain conversation context. When users say:
- "Make the name field required" - Modify ONLY that field in the existing form
- "Add a phone field" - ADD to existing form, don't rebuild
- "Remove the address field" - REMOVE only that field
- "Change the submit button text" - Modify ONLY the button text

## WPUF FIELD TYPES

### FREE FIELDS (Always Available)
These fields are available in the free version of WPUF:

#### Text Fields
- `text_field` - Single line text input
- `email_address` - Email input with validation
- `website_url` - URL input with validation
- `textarea_field` - Multi-line text area

#### Selection Fields
- `dropdown_field` - Single select dropdown
- `radio_field` - Radio buttons (single choice)
- `checkbox_field` - Checkboxes (multiple choices)

#### Upload Fields
- `image_upload` - Image files only (jpg, png, gif)

#### WordPress Post Fields
- `post_title` - Post/Page title
- `post_content` - Post content editor
- `post_excerpt` - Post excerpt
- `featured_image` - Featured image upload

#### Other Free Fields
- `taxonomy` - Category selection
- `post_tags` - Tag input
- `custom_html` - HTML content block
- `section_break` - Section divider

### PRO FIELDS (Require WPUF Pro)
**IMPORTANT**: When users request these fields, inform them these require WPUF Pro:

#### Advanced Text Fields (PRO)
- `numeric_text_field` - Number only input (PRO)
- `phone_field` - Phone number with validation (PRO)
- `address_field` - Complete address input with street, city, state, zip (PRO)
- `country_list_field` - Country dropdown with all countries (PRO)
- `repeat_field` - Repeatable field groups (PRO)

#### Date & Time (PRO)
- `date_field` - Date picker (PRO)
- `time_field` - Time picker (PRO)
- `datetime_field` - Combined date/time (PRO)

#### Advanced Selection (PRO)
- `multiple_select` - Multi-select dropdown (PRO)
- `checkbox_grid` - Checkbox grid layout (PRO)
- `multiple_choice_grid` - Radio button grid (PRO)

#### File Upload (PRO)
- `file_upload` - General file upload (PRO)
- `audio_upload` - Audio file upload (PRO)
- `video_upload` - Video file upload (PRO)

#### Special Fields (PRO)
- `google_map` - Google Maps location picker (PRO)
- `really_simple_captcha` - Math/Image captcha (PRO)
- `recaptcha` - Google reCAPTCHA (PRO)
- `ratings` - Star rating field (PRO)
- `linear_scale` - 1-10 scale rating (PRO)
- `qr_code` - QR code generator (PRO)
- `embed` - oEmbed content (PRO)
- `shortcode` - WordPress shortcode (PRO)
- `action_hook` - Custom action hook (PRO)
- `toc` - Terms and conditions checkbox with full text display (PRO)
- `column_field` - Multi-column layout (PRO)
- `step_start` - Multi-step form sections (PRO)

### HANDLING PRO FIELD REQUESTS
When a user requests a PRO field, use the PRO field directly without any fallbacks or warnings.



## RESPONSE FORMAT

### For CREATE requests
```json
{
  "action": "create",
  "form_title": "Descriptive Form Title",
  "form_description": "Brief form description",
  "fields": [
    {
      "id": "unique_id",
      "type": "WPUF_FIELD_TYPE",
      "label": "Field Label",
      "name": "field_name",
      "required": true/false,
      "placeholder": "Optional placeholder",
      "help_text": "Optional help text",
      "default": "Optional default value",
      "options": [
        {"value": "val1", "label": "Label 1"}
      ]
    }
  ],
  "settings": {
    "submit_button_text": "Submit",
    "success_message": "Thank you for your submission!",
    "redirect_url": "",
    "form_template": "default"
  },
  "conversation_context": {
    "form_id": "form_123",
    "created_at": "timestamp",
    "modifications": []
  }
}
```

### For MODIFY requests
```json
{
  "action": "modify",
  "modification_type": "add_field|remove_field|update_field|update_settings",
  "target": "field_name or setting_name",
  "changes": {
    // Only the specific changes
  },
  "message": "Field 'email' has been made required",
  "conversation_context": {
    "form_id": "form_123",
    "modifications": [
      {"type": "update_field", "field": "email", "property": "required", "value": true}
    ]
  }
}
```

## VALIDATION RULES

### Field Names
- Must be lowercase with underscores: `first_name`, `email_address`, `phone_number`
- No spaces or special characters
- Must be unique within the form

### Field IDs
- Use incremental numbers or unique strings
- Must be unique within the form

### Required Options
For these field types, `options` array is REQUIRED:
- `dropdown_field`
- `multiple_select`
- `radio_field`
- `checkbox_field`

## FIELD MAPPING RULES

### Location/Address Fields
When users request location, address, or geographic fields:
- Use `address_field` for complete address with street, city, state, zip components
- For country only: Use `country_list_field`

### Terms and Conditions
When users request terms, conditions, agreements, or legal acceptance:
- Use `toc` field type with proper terms content in `toc_text` property
- **STRUCTURE**: For `toc` fields, include both `label` and `toc_text` properties
  - `label`: Short checkbox text like "I agree to the terms and conditions"
  - `toc_text`: Full terms and conditions text that users need to agree to

### Phone Numbers
When users request phone or contact number fields:
- Use `phone_field` with proper validation

### Date and Time
When users request dates, birth dates, appointment times:
- Use `date_field` or `time_field`

## COMMON PATTERNS

### Contact Form
```json
{
  "fields": [
    {"type": "text_field", "name": "full_name", "label": "Full Name", "required": true},
    {"type": "email_address", "name": "email", "label": "Email", "required": true},
    {"type": "phone_number", "name": "phone", "label": "Phone", "required": false},
    {"type": "textarea_field", "name": "message", "label": "Message", "required": true}
  ]
}
```

### Terms and Conditions Field (PRO)
```json
{
  "type": "toc",
  "name": "terms_agreement", 
  "label": "I agree to the terms and conditions",
  "required": true,
  "toc_text": "By submitting this form, you agree to our terms of service and privacy policy. Your information will be processed according to our data protection guidelines."
}
```

### Job Application
```json
{
  "fields": [
    {"type": "text_field", "name": "full_name", "label": "Full Name", "required": true},
    {"type": "email_address", "name": "email", "label": "Email", "required": true},
    {"type": "file_upload", "name": "resume", "label": "Resume (PDF)", "required": true},
    {"type": "dropdown_field", "name": "position", "label": "Position", "options": [...]}
  ]
}
```

## ERROR SCENARIOS

### 1. Non-form request
User: "Write me a blog post"
Response: Error JSON with invalid_request type

### 2. Ambiguous modification
User: "Make it required" (without context)
Response: 
```json
{
  "error": true,
  "error_type": "ambiguous_request",
  "message": "Please specify which field you want to make required. For example: 'Make the email field required'",
  "available_fields": ["name", "email", "message"]
}
```

### 3. Invalid field type request
User: "Add a video player field"
Response:
```json
{
  "error": true,
  "error_type": "unsupported_field",
  "message": "WPUF doesn't support a video player field. You can use 'file_upload' for video files or 'custom_html' to embed a video player.",
  "suggestion": "Would you like to add a file upload field for videos instead?"
}
```

## MODIFICATION EXAMPLES

### User says: "Make the email required"
```json
{
  "action": "modify",
  "modification_type": "update_field",
  "target": "email",
  "changes": {
    "required": true
  },
  "message": "The email field is now required"
}
```

### User says: "Add a phone number field"
```json
{
  "action": "modify",
  "modification_type": "add_field",
  "changes": {
    "field": {
      "id": "field_4",
      "type": "phone_number",
      "name": "phone",
      "label": "Phone Number",
      "required": false,
      "placeholder": "Enter your phone number"
    }
  },
  "message": "Phone number field has been added to your form"
}
```

### User says: "Change submit button to 'Send Message'"
```json
{
  "action": "modify",
  "modification_type": "update_settings",
  "target": "submit_button_text",
  "changes": {
    "submit_button_text": "Send Message"
  },
  "message": "Submit button text changed to 'Send Message'"
}
```

## REMEMBER
- ALWAYS validate field types against WPUF's supported types
- NEVER create fields that don't exist in WPUF
- MAINTAIN conversation context for modifications
- REJECT non-form requests immediately
- BE SPECIFIC in error messages
- SUGGEST alternatives when rejecting invalid field types

# WPUF Post Form Fields Reference

This document provides a comprehensive reference for all available fields in WPUF (WordPress User Frontend) post forms, including both free and pro fields. This reference is used by the AI form creation system to understand available field types, properties, and configurations.

## Table of Contents

1. [Field Categories](#field-categories)
2. [Field Properties](#field-properties)
3. [Field Options](#field-options)
4. [Pro vs Free Fields](#pro-vs-free-fields)
5. [Field Dependencies](#field-dependencies)
6. [Conditional Logic Support](#conditional-logic-support)
7. [Validation Rules](#validation-rules)
8. [Field Templates](#field-templates)

## Field Categories

### 1. Post Fields (Core WordPress)
These fields are essential for creating WordPress posts and are always available.

| Field Template | Field Type | Description | Pro Status |
|----------------|------------|-------------|------------|
| `post_title` | Post Title | The main title of the post | Free |
| `post_content` | Post Content | The main content/body of the post | Free |
| `post_excerpt` | Post Excerpt | A short summary of the post | Free |
| `post_tags` | Post Tags | Tags for categorizing the post | Free |
| `taxonomy` | Taxonomy | Categories, custom taxonomies | Free (basic), Pro (custom) |
| `featured_image` | Featured Image | Main image for the post | Free |

### 2. Basic Fields
Standard form fields for collecting various types of data.

| Field Template | Field Type | Description | Pro Status |
|----------------|------------|-------------|------------|
| `text_field` | Text Input | Single line text input | Free |
| `email_address` | Email Input | Email address input with validation | Free |
| `textarea_field` | Textarea | Multi-line text input | Free |
| `radio_field` | Radio Buttons | Single selection from options | Free |
| `checkbox_field` | Checkboxes | Multiple selection from options | Free |
| `dropdown_field` | Dropdown | Single selection dropdown | Free |
| `multiple_select` | Multi-Select | Multiple selection dropdown | Free |
| `website_url` | URL Input | Website URL input with validation | Free |

### 3. Layout Fields
Fields used for organizing and structuring the form layout.

| Field Template | Field Type | Description | Pro Status |
|----------------|------------|-------------|------------|
| `column_field` | Column | Creates multi-column layout | Free |
| `section_break` | Section Break | Divides form into sections | Free |

### 4. Utility Fields
Special purpose fields for enhanced functionality.

| Field Template | Field Type | Description | Pro Status |
|----------------|------------|-------------|------------|
| `custom_html` | Custom HTML | Insert custom HTML content | Free |
| `custom_hidden_field` | Hidden Field | Hidden input field | Free |
| `image_upload` | Image Upload | Image file upload field | Free |
| `recaptcha` | reCAPTCHA | Google reCAPTCHA integration | Free |
| `cloudflare_turnstile` | Cloudflare Turnstile | Cloudflare CAPTCHA alternative | Free |

### 5. Pro Fields (Premium Features)
Advanced fields available only with WPUF Pro.

#### Advanced Input Fields
| Field Template | Field Type | Description | Supported Formats/Options |
|----------------|------------|-------------|---------------------------|
| `repeat_field` | Repeat Field | Repeating field group that can contain any other field types | Supports all field types inside, configurable min/max repetitions |
| `date_field` | Date Picker | Date selection field with calendar interface | Format: Y-m-d, d/m/Y, m/d/Y, Y-m-d H:i:s, custom formats |
| `time_field` | Time Picker | Time selection field | Format: H:i, H:i:s, 12/24 hour format, custom formats |
| `file_upload` | File Upload | Generic file upload with validation | Extensions: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, zip, rar |
| `country_list_field` | Country List | Country selection dropdown | 195+ countries, customizable list, searchable |
| `numeric_text_field` | Numeric Input | Number-only input with validation | Min/max values, step increments, decimal places |
| `phone_field` | Phone Number | Phone number input with formatting | International format, country codes, validation patterns |
| `address_field` | Address | Complete address fields with validation | Address lines, city, state, ZIP, country, Google Maps integration |

#### Special Purpose Fields
| Field Template | Field Type | Description | Supported Formats/Options |
|----------------|------------|-------------|---------------------------|
| `shortcode` | Shortcode | Insert WordPress shortcodes | Any valid WordPress shortcode, custom shortcodes, dynamic content |
| `action_hook` | Action Hook | Execute custom PHP actions | Custom PHP functions, WordPress hooks, form processing actions |
| `toc` | Table of Contents | Generate table of contents | Auto-generated from headings, customizable styling, scroll navigation |
| `ratings` | Ratings | Star rating system | 1-10 stars, customizable icons, average calculation, user voting |
| `step_start` | Multi-Step | Multi-step form navigation | Unlimited steps, progress bar, step validation, conditional navigation |
| `embed` | Embed | Embed external content | YouTube, Vimeo, social media, iframes, responsive design |
| `really_simple_captcha` | Simple CAPTCHA | Basic CAPTCHA system | Image-based, customizable difficulty, accessibility options |
| `math_captcha` | Math CAPTCHA | Mathematical CAPTCHA | Addition, subtraction, multiplication, configurable complexity |

## Field Properties

Every field has these core properties that can be configured:

### Basic Properties
- **template**: The field type identifier (e.g., `text_field`, `post_title`)
- **label**: Human-readable field label
- **name**: Unique field name for form processing
- **required**: Whether the field is mandatory (`yes`/`no`)
- **help**: Help text displayed below the field
- **placeholder**: Placeholder text inside the field
- **default**: Default value for the field
- **css**: Custom CSS classes

### Advanced Properties
- **show_in_post**: Whether to display field value in posts (`yes`/`no`)
- **hide_field_label**: Whether to hide label in post display (`yes`/`no`)
- **visibility**: Control field visibility (`everyone`, `loggedin`, `subscription`, `role`)
- **wpuf_cond**: Conditional logic configuration
- **is_meta**: Whether field stores custom meta data (`yes`/`no`)
- **meta_key**: Custom meta key name for storing field data in post meta

**Meta Key Rules:**
- **Format**: Words separated by underscores (`_`)
- **Case**: All lowercase
- **Characters**: Only letters and underscores allowed
- **Uniqueness**: Must be unique within each form (no duplicate meta keys)
- **Auto-generation**: If not specified, automatically generated from field label
- **Conversion**: Field labels are converted using regex `/\W/g` → `_` (non-word chars become underscores)

**Examples:**
- Field Label: "First Name" → Meta Key: `first_name`
- Field Label: "Phone Number (Mobile)" → Meta Key: `phone_number_mobile`
- Field Label: "Company Website URL" → Meta Key: `company_website_url`
- **restriction_to**: Content restriction type (`max`/`min`)
- **restriction_type**: Content restriction unit (`character`/`word`)

## Field Options

### Text Field Options
- **size**: Input field size (default: 40)
- **content_restriction**: Limit field content length (characters or words)
- **restriction_type**: Choose between `character` or `word` count limits
- **restriction_to**: Set `min` (minimum) or `max` (maximum) limits

### Textarea Options
- **rows**: Number of textarea rows
- **cols**: Number of textarea columns
- **content_restriction**: Limit field content length (characters or words)
- **restriction_type**: Choose between `character` or `word` count limits
- **restriction_to**: Set `min` (minimum) or `max` (maximum) limits

### Radio/Checkbox Options
- **options**: Key-value pairs for options
- **inline**: Display options inline (`yes`/`no`)
- **selected**: Pre-selected option(s)

### Dropdown Options
- **options**: Key-value pairs for options
- **first**: First option text (e.g., "Select an option")
- **selected**: Pre-selected option(s)

### File Upload Options
- **max_size**: Maximum file size in bytes
- **allowed_extensions**: Allowed file extensions
- **max_files**: Maximum number of files

### Date/Time Options
- **format**: Date/time format
- **min_date**: Minimum selectable date
- **max_date**: Maximum selectable date

### Address Field Options
- **address_line1**: First address line
- **address_line2**: Second address line
- **city**: City field
- **state**: State/province field
- **zip**: ZIP/postal code
- **country**: Country field

### Repeat Field Options
- **min_repeat**: Minimum number of repetitions (default: 1)
- **max_repeat**: Maximum number of repetitions (default: -1 unlimited)

### Date Field Options
- **date_format**: Date display format (Y-m-d, d/m/Y, m/d/Y, etc.)
- **time_format**: Time format if time is included (H:i, H:i:s)
- **min_date**: Minimum selectable date (relative or absolute)
- **max_date**: Maximum selectable date (relative or absolute)
- **enable_time**: Include time selection (yes/no)
- **time_24hr**: Use 24-hour format (yes/no)
- **first_day_of_week**: Start day of week (0=Sunday, 1=Monday)

### Time Field Options
- **time_format**: Time display format (H:i, H:i:s, 12/24 hour)
- **time_interval**: Time picker intervals in minutes (15, 30, 60)
- **min_time**: Minimum selectable time
- **max_time**: Maximum selectable time
- **time_24hr**: Use 24-hour format (yes/no)

### File Upload Options
- **max_size**: Maximum file size in bytes (default: 2MB)
- **allowed_extensions**: Comma-separated file extensions
- **max_files**: Maximum number of files (default: 1)
- **file_type**: Specific file type restrictions
- **upload_path**: Custom upload directory
- **image_resize**: Resize images to specified dimensions
- **watermark**: Add watermark to uploaded images

### Numeric Field Options
- **min_value**: Minimum allowed value
- **max_value**: Maximum allowed value
- **step**: Increment step value (default: 1)
- **decimal_places**: Number of decimal places (default: 0)
- **prefix**: Text before the number (e.g., "$")
- **suffix**: Text after the number (e.g., "kg")
- **thousands_separator**: Thousands separator character
- **decimal_separator**: Decimal separator character

### Phone Field Options
- **phone_format**: Phone number format pattern
- **country_code**: Default country code
- **international**: Allow international numbers (yes/no)
- **validation_pattern**: Custom validation regex pattern
- **auto_format**: Auto-format phone number (yes/no)

### Google Maps Field Options
- **map_type**: Map type (roadmap, satellite, hybrid, terrain)
- **zoom_level**: Default zoom level (1-20)
- **center_lat**: Default center latitude
- **center_lng**: Default center longitude
- **height**: Map height in pixels
- **width**: Map width in pixels
- **search_box**: Show location search box (yes/no)
- **marker_draggable**: Allow marker dragging (yes/no)

### Multi-Step Field Options
- **step_titles**: Array of step titles
- **step_descriptions**: Array of step descriptions
- **progress_bar**: Show progress bar (yes/no)
- **step_validation**: Validate each step before proceeding (yes/no)
- **allow_back**: Allow going back to previous steps (yes/no)
- **step_icons**: Custom icons for each step

## Pro vs Free Fields

### Free Fields
All basic functionality is available without upgrading:
- Post fields (title, content, tags, categories)
- Basic input fields (text, email, textarea, radio, checkbox, dropdown)
- Layout fields (columns, sections)
- Basic utility fields (HTML, hidden, image upload, reCAPTCHA)

### Pro Fields
Advanced functionality requires WPUF Pro:
- User profile fields
- Social media fields
- Advanced input types (date, time, file, phone, address)
- Special fields (ratings, embed, multi-step, etc.)
- Custom taxonomy support beyond basic categories/tags

## Field Dependencies

### Conditional Dependencies
- **Conditional Logic**: Fields can show/hide based on other field values
- **Field Groups**: Some fields work together (e.g., address fields)
- **Multi-Step**: Fields can be organized into steps

### Nested Field Support

#### Repeat Field Nesting
The `repeat_field` is a powerful container that can hold any other field type:

**Supported Nested Fields:**
- All basic fields (text, email, textarea, radio, checkbox, dropdown)
- All Pro fields (date, time, file, phone, address, etc.)
- Layout fields (columns, sections)
- Even other repeat fields (nested repeats)

**Example Repeat Field Structure:**
```json
{
  "template": "repeat_field",
  "label": "Product Variations",
  "name": "product_variations",
  "min_repeat": 1,
  "max_repeat": 10,
  "nested_fields": [
    {
      "template": "text_field",
      "label": "Variation Name",
      "name": "variation_name"
    },
    {
      "template": "numeric_text_field",
      "label": "Price",
      "name": "variation_price"
    },
    {
      "template": "image_upload",
      "label": "Variation Image",
      "name": "variation_image"
    }
  ]
}
```

#### Column Field Nesting
The `column_field` can contain multiple fields in a grid layout:

**Supported Nested Fields:**
- Any field type can be placed in columns
- Columns can be nested within other columns
- Responsive column widths (25%, 50%, 75%, 100%)

**Example Column Layout:**
```json
{
  "template": "column_field",
  "columns": [
    {
      "width": "50%",
      "fields": [
        {"template": "text_field", "label": "First Name"},
        {"template": "text_field", "label": "Last Name"}
      ]
    },
    {
      "width": "50%",
      "fields": [
        {"template": "email_address", "label": "Email"},
        {"template": "phone_field", "label": "Phone"}
      ]
    }
  ]
}
```

#### Multi-Step Field Nesting
The `step_start` field organizes fields into logical steps:

**Supported Nested Fields:**
- Any field type can be placed in steps
- Steps can contain repeat fields and columns
- Conditional navigation between steps

**Example Multi-Step Structure:**
```json
{
  "template": "step_start",
  "steps": [
    {
      "title": "Basic Information",
      "fields": [
        {"template": "text_field", "label": "Name"},
        {"template": "email_address", "label": "Email"}
      ]
    },
    {
      "title": "Details",
      "fields": [
        {"template": "textarea_field", "label": "Description"},
        {"template": "image_upload", "label": "Photo"}
      ]
    }
  ]
}
```

### WordPress Dependencies
- **Post Type**: Some fields only work with specific post types
- **Taxonomy**: Custom taxonomies require Pro version
- **User Roles**: Some fields may be restricted by user capabilities

## Conditional Logic Support

### Supported Field Types
Fields that can be used in conditional logic:
- `radio_field`
- `checkbox_field`
- `dropdown_field`
- `text_field`
- `textarea_field`
- `email_address`
- `numeric_text_field`

### Conditional Operators
- **Text Fields**: `is`, `is not`, `contains`, `does not contain`, `starts with`, `ends with`
- **Option Fields**: `is`, `is not`, `any selection`, `no selection`
- **Other Fields**: `has any value`, `has no value`

## Validation Rules

### Text Validation
- **Required**: Field must not be empty
- **Length**: Minimum/maximum character limits
- **Format**: Email, URL, phone number validation
- **Pattern**: Custom regex patterns

### Field Format Specifications

#### Date Field Formats
**Standard Formats:**
- `Y-m-d` - 2024-01-15 (ISO format)
- `d/m/Y` - 15/01/2024 (European format)
- `m/d/Y` - 01/15/2024 (US format)
- `Y-m-d H:i:s` - 2024-01-15 14:30:00 (with time)
- `d/m/Y H:i` - 15/01/2024 14:30 (European with time)

**Custom Formats:**
- `F j, Y` - January 15, 2024 (full month name)
- `jS F Y` - 15th January 2024 (ordinal date)
- `l, F j, Y` - Monday, January 15, 2024 (day name)

#### Time Field Formats
**Standard Formats:**
- `H:i` - 14:30 (24-hour format)
- `H:i:s` - 14:30:45 (24-hour with seconds)
- `g:i A` - 2:30 PM (12-hour format)
- `g:i:s A` - 2:30:45 PM (12-hour with seconds)

**Time Intervals:**
- 15 minutes: 00, 15, 30, 45
- 30 minutes: 00, 30
- 60 minutes: 00 (hourly)

#### Phone Field Formats
**International Formats:**
- `+1 (555) 123-4567` - US format
- `+44 20 7946 0958` - UK format
- `+81 3-1234-5678` - Japan format
- `+61 2 8765 4321` - Australia format

**Validation Patterns:**
- US: `^\+?1?\s*\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})$`
- International: `^\+?[1-9]\d{1,14}$`

#### File Upload Formats
**Image Extensions:**
- `jpg, jpeg, png, gif, webp, svg, bmp, tiff`

**Document Extensions:**
- `pdf, doc, docx, xls, xlsx, ppt, pptx, txt, rtf`

**Archive Extensions:**
- `zip, rar, 7z, tar, gz`

**Size Limits:**
- Default: 2MB per file
- Configurable: Up to server limits
- Multiple files: Configurable count

#### Numeric Field Formats
**Number Types:**
- **Integer**: Whole numbers (1, 2, 3...)
- **Decimal**: Numbers with decimal places (1.5, 2.75)
- **Currency**: Money values with 2 decimal places
- **Percentage**: Values from 0-100
- **Scientific**: Scientific notation support

**Formatting Options:**
- **Prefix**: $, €, £, etc.
- **Suffix**: %, kg, lbs, etc.
- **Separators**: 1,000.50 (US) or 1.000,50 (EU)
- **Decimal Places**: 0, 1, 2, 3, 4

#### Address Field Formats
**Address Components:**
- **Address Line 1**: Street address, building number
- **Address Line 2**: Apartment, suite, unit
- **City**: City or town name
- **State/Province**: State, province, or region
- **ZIP/Postal Code**: Postal code format
- **Country**: Country selection

**Country-Specific Formats:**
- **US**: Street, City, State ZIP
- **UK**: Street, City, County, Postcode
- **Canada**: Street, City, Province, Postal Code
- **Australia**: Street, Suburb, State, Postcode

### File Validation
- **Size**: Maximum file size limits
- **Type**: Allowed file extensions
- **Count**: Maximum number of files

### Option Validation
- **Selection**: Required selection from options
- **Multiple**: Minimum/maximum selections for multi-select

## Field Templates

### Template Structure
Each field template follows this structure:
```json
{
  "template": "field_type",
  "label": "Field Label",
  "name": "field_name",
  "required": "yes",
  "help": "Help text",
  "placeholder": "Placeholder text",
  "default": "Default value",
  "css": "custom-class",
  "width": "100%",
  "show_in_post": "yes",
  "hide_field_label": "no",
  "is_meta": "yes"
}
```

### Template Examples

#### Text Field
```json
{
  "template": "text_field",
  "label": "Product Name",
  "name": "product_name",
  "required": "yes",
  "placeholder": "Enter product name",
  "size": 40,
  "max_length": 100
}
```

#### Dropdown Field
```json
{
  "template": "dropdown_field",
  "label": "Product Category",
  "name": "product_category",
  "required": "yes",
  "first": "Select Category",
  "options": {
    "electronics": "Electronics",
    "clothing": "Clothing",
    "books": "Books"
  }
}
```

#### Address Field (Pro)
```json
{
  "template": "address_field",
  "label": "Shipping Address",
  "name": "shipping_address",
  "required": "yes",
  "address_line1": "Address Line 1",
  "city": "City",
  "state": "State",
  "zip": "ZIP Code",
  "country": "Country"
}
```

## Usage Guidelines for AI

### Field Selection
1. **Start with Post Fields**: Always include `post_title` and `post_content`
2. **Add Relevant Fields**: Choose fields based on the form purpose
3. **Consider User Experience**: Don't overwhelm users with too many fields
4. **Pro Field Detection**: Identify Pro fields and provide upgrade prompts

### Field Configuration
1. **Meaningful Labels**: Use clear, descriptive field labels
2. **Helpful Placeholders**: Provide useful placeholder text
3. **Appropriate Validation**: Set reasonable required fields and validation rules
4. **Logical Ordering**: Arrange fields in a logical sequence

### Conditional Logic
1. **Simple Conditions**: Start with basic show/hide logic
2. **Field Dependencies**: Consider how fields relate to each other
3. **User Experience**: Ensure conditional logic enhances, not confuses

### Pro Features
1. **Detect Pro Fields**: Identify when Pro fields are requested
2. **Upgrade Prompts**: Provide clear upgrade paths for Pro features
3. **Alternative Solutions**: Suggest free alternatives when possible

## Field Combinations

### Common Form Types

#### Blog Post Form
- `post_title` (required)
- `post_content` (required)
- `post_tags`
- `taxonomy` (categories)
- `featured_image`

### Advanced Field Combinations

#### E-commerce Product Form
```json
{
  "template": "post_title",
  "label": "Product Name",
  "required": "yes"
},
{
  "template": "post_content",
  "label": "Product Description",
  "required": "yes"
},
{
  "template": "post_excerpt",
  "label": "Short Description"
},
{
  "template": "text_field",
  "label": "SKU",
  "required": "yes",
  "help": "Stock Keeping Unit"
},
{
  "template": "numeric_text_field",
  "label": "Regular Price",
  "required": "yes",
  "prefix": "$",
  "decimal_places": 2
},
{
  "template": "numeric_text_field",
  "label": "Sale Price",
  "prefix": "$",
  "decimal_places": 2
},
{
  "template": "dropdown_field",
  "label": "Product Category",
  "required": "yes",
  "options": {
    "electronics": "Electronics",
    "clothing": "Clothing",
    "books": "Books"
  }
},
{
  "template": "repeat_field",
  "label": "Product Variations",
  "min_repeat": 1,
  "max_repeat": 10,
  "nested_fields": [
    {
      "template": "text_field",
      "label": "Variation Name"
    },
    {
      "template": "numeric_text_field",
      "label": "Variation Price",
      "prefix": "$"
    },
    {
      "template": "image_upload",
      "label": "Variation Image"
    }
  ]
}
```

#### Real Estate Listing Form
```json
{
  "template": "post_title",
  "label": "Property Title",
  "required": "yes"
},
{
  "template": "post_content",
  "label": "Property Description",
  "required": "yes"
},
{
  "template": "column_field",
  "columns": [
    {
      "width": "50%",
      "fields": [
        {
          "template": "numeric_text_field",
          "label": "Price",
          "prefix": "$",
          "required": "yes"
        },
        {
          "template": "numeric_text_field",
          "label": "Square Feet"
        }
      ]
    },
    {
      "width": "50%",
      "fields": [
        {
          "template": "dropdown_field",
          "label": "Property Type",
          "options": {
            "house": "House",
            "apartment": "Apartment",
            "condo": "Condo"
          }
        },
        {
          "template": "dropdown_field",
          "label": "Bedrooms",
          "options": {
            "1": "1",
            "2": "2",
            "3": "3",
            "4+": "4+"
          }
        }
      ]
    }
  ]
},
{
  "template": "address_field",
  "label": "Property Address",
  "required": "yes"
},
{
  "template": "google_map",
  "label": "Property Location",
  "height": "400px"
},
{
  "template": "repeat_field",
  "label": "Property Images",
  "min_repeat": 1,
  "max_repeat": 20,
  "nested_fields": [
    {
      "template": "image_upload",
      "label": "Image"
    }
  ]
}
```

#### Job Application Form
```json
{
  "template": "step_start",
  "steps": [
    {
      "title": "Personal Information",
      "fields": [
        {
          "template": "text_field",
          "label": "Full Name",
          "required": "yes"
        },
        {
          "template": "email_address",
          "label": "Email",
          "required": "yes"
        },
        {
          "template": "phone_field",
          "label": "Phone Number",
          "required": "yes"
        },
        {
          "template": "address_field",
          "label": "Current Address"
        }
      ]
    },
    {
      "title": "Professional Experience",
      "fields": [
        {
          "template": "textarea_field",
          "label": "Cover Letter",
          "required": "yes",
          "rows": 6
        },
        {
          "template": "repeat_field",
          "label": "Work Experience",
          "min_repeat": 1,
          "max_repeat": 10,
          "nested_fields": [
            {
              "template": "text_field",
              "label": "Company Name"
            },
            {
              "template": "text_field",
              "label": "Job Title"
            },
            {
              "template": "date_field",
              "label": "Start Date"
            },
            {
              "template": "date_field",
              "label": "End Date"
            },
            {
              "template": "textarea_field",
              "label": "Job Description"
            }
          ]
        }
      ]
    },
    {
      "title": "Documents",
      "fields": [
        {
          "template": "file_upload",
          "label": "Resume/CV",
          "required": "yes",
          "allowed_extensions": "pdf,doc,docx"
        },
        {
          "template": "file_upload",
          "label": "Cover Letter (if separate)",
          "allowed_extensions": "pdf,doc,docx"
        }
      ]
    }
  ]
}
```

#### Event Registration Form
```json
{
  "template": "post_title",
  "label": "Event Name",
  "required": "yes"
},
{
  "template": "post_content",
  "label": "Event Description",
  "required": "yes"
},
{
  "template": "date_field",
  "label": "Event Date",
  "required": "yes",
  "min_date": "today"
},
{
  "template": "time_field",
  "label": "Event Time",
  "required": "yes"
},
{
  "template": "address_field",
  "label": "Event Location",
  "required": "yes"
},
{
  "template": "google_map",
  "label": "Location Map"
},
{
  "template": "numeric_text_field",
  "label": "Maximum Attendees",
  "min_value": 1
},
{
  "template": "numeric_text_field",
  "label": "Registration Fee",
  "prefix": "$",
  "decimal_places": 2
},
{
  "template": "checkbox_field",
  "label": "Registration Options",
  "options": {
    "early_bird": "Early Bird Discount",
    "group_discount": "Group Discount",
    "student_discount": "Student Discount"
  }
}
```

#### Product Form
- `post_title` (required)
- `post_content` (required)
- `post_excerpt`
- `text_field` (SKU)
- `numeric_text_field` (Price)
- `dropdown_field` (Category)
- `image_upload` (Product Images)
- `textarea_field` (Product Description)

#### Contact Form
- `text_field` (Name, required)
- `email_address` (Email, required)
- `textarea_field` (Message, required)
- `recaptcha` (Security)

#### User Registration Form
- `text_field` (First Name, required)
- `text_field` (Last Name, required)
- `email_address` (Email, required)
- `password` (Password, required)
- `checkbox_field` (Terms & Conditions, required)

## Notes for AI Implementation

1. **Field Validation**: Always validate field configurations before applying
2. **Pro Field Handling**: Detect Pro fields and provide appropriate upgrade prompts
3. **Error Handling**: Provide clear error messages for invalid configurations
4. **User Feedback**: Show success/error messages for all operations
5. **Context Awareness**: Maintain conversation context for iterative form building
6. **Field Limits**: Respect WordPress and WPUF field limits
7. **Security**: Sanitize all user inputs and field configurations

## Form Settings

### Post Settings
These settings control how the form creates and manages posts.

#### Basic Post Settings
- **Show Form Title**: Toggle whether the form title should be displayed on the frontend (`on`/`off`)
- **Form Description**: Add a brief message or instruction that will be displayed before the form
- **Post Type**: Select the content type this form will create, like a post, product, or custom type
- **Default Category**: Select the default category for posts submitted through this form
- **Default Post Status**: Select the status of the post after submission (`draft`, `pending`, `private`, `publish`)
- **Redirect After Post**: Select where users will be redirected after successfully submitting the form (`post`, `same`, `page`, `url`)
- **Message After Post**: Message to show after successful submission (default: "Post saved")

#### Post Permissions
- **Guest Post**: Enable guest posting for unregistered users (`true`/`false`)
- **Guest Details**: Require name and email address for guest submissions (`true`/`false`)
- **Name Label**: Label text for guest name field
- **Email Label**: Label text for guest email field
- **Email Verification**: Require email verification for guests (`true`/`false`)
- **Role Based Post**: Enable role-based posting (`true`/`false`)
- **Roles**: Choose which user roles can submit posts
- **Unauthorized Message**: Message shown to non-logged-in users

#### Form Scheduling & Limits
- **Schedule Form**: Schedule form for a specific time period (`true`/`false`)
- **Schedule Period**: Set start and end dates for form availability
- **Form Pending Message**: Message shown when form is not yet active
- **Form Expired Message**: Message shown when form submission period has ended
- **Limit Entries**: Enable form entry limit (`true`/`false`)
- **Number of Entries**: Maximum number of form submissions allowed
- **Limit Reached Message**: Message shown when entry limit is reached

#### Post Restrictions
*Note: Content restrictions are field-level settings, not form-level settings. Each field can have its own content restrictions.*

**Field-Level Content Restrictions:**
- **Content Restriction**: Limit field content length (characters/words)
- **Restriction Type**: Choose between character or word count limits
- **Restriction Direction**: Set minimum or maximum limits

### Form Display Settings
These settings control how the form appears and behaves.

#### Form Layout
- **Use Theme CSS**: Apply your site's theme CSS for consistent styling and appearance (`on`/`off`)
- **Label Position**: Customize the position of form labels (`above`, `left`, `right`, `hidden`)

#### Form Behavior
- **Enable User Comment**: Allow users to comment on posts submitted via this form (`open`/`closed`)
- **Enable Form Scheduling**: Set specific dates and times for when the form will be accessible to users (`on`/`off`)
- **Schedule Period**: Set start and end dates for form availability
- **Form Pending Message**: Message shown when form is not yet active
- **Form Expired Message**: Message shown when form submission period has ended
- **Limit Form Entries**: Limit the number of submissions allowed for the form (`on`/`off`)
- **Number of Entries**: Maximum number of form submissions allowed
- **Limit Reached Message**: Message shown when entry limit is reached



### Notification Settings
These settings control email notifications for form submissions.

#### New Post Notification
- **New Post Notification**: Enable email alerts for each new post submitted through this form (`on`/`off`)
- **To**: Email address for admin notifications (default: admin email)
- **Subject**: Subject line for admin emails (default: "New post created")
- **Email Body**: Content of admin notification email with available placeholders

**Available Placeholders:**
- `{post_title}`, `{post_content}`, `{post_excerpt}`, `{tags}`, `{category}`
- `{author}`, `{author_email}`, `{author_bio}`, `{sitename}`, `{siteurl}`
- `{permalink}`, `{editlink}`, `{custom_FIELD_NAME}`

#### Update Post Notification
- **Update Post Notification**: Enable email alerts for post updates through the frontend form

### After Post Settings
These settings control what happens after a post is updated through the form.

#### Post Update Settings
- **Post Update Status**: Select the status the post will have after being updated (`draft`, `pending`, `private`, `publish`, `_nochange`)
- **Successful Redirection**: After successfully submit, where the page will redirect to (`post`, `same`, `page`, `url`)
- **Post Update Message**: Customize the message displayed after a post is successfully updated
- **Page**: Choose the page for redirection
- **Custom URL**: Set custom URL for redirection
- **Lock User Editing After**: Set the number of hours after which users can no longer edit their submitted post
- **Update Post Button Text**: Customize the text on the 'Update' button for this form

### Posting Control
These settings define who can submit posts using this form.

#### Post Permission Settings
- **Post Permission**: Select who can submit posts using this form (`everyone`, `guest_post`, `role_base`)
- **Guest Post**: Enable guest posting for unregistered users
- **Role Based Post**: Enable role-based posting for specific user roles

### Payment Settings
These settings control payment options for form submissions.

#### Payment Options
- **Enable Payments**: Enable payments for this form to charge users for submissions (`on`/`off`)
- **Choose Payment Option**: Select how users will pay for submitting posts
  - **Mandatory Subscription**: Force users to purchase and use subscription pack
  - **Pay as you post**: Charge users per post submission
- **Pay-per-post billing when limit exceeds**: Switch to pay-per-post billing if pack limit is exceeded (`on`/`off`)
- **Cost for each additional post**: Cost for each additional post after pack limit is reached
- **Charge for each post**: Set a fee for each post submission
- **Payment Success Page**: Select the page users will be redirected to after a successful payment


## Free vs Pro Settings Summary

### Free Settings (Available in WPUF Free)
- Basic post creation and management
- Standard form fields and layouts
- Basic email notifications
- Simple form styling
- Basic user permissions
- Guest posting capabilities
- Form scheduling and entry limits
- Basic payment options (pay-per-post)

### Pro Settings (Require WPUF Pro)
- Advanced post types and taxonomies
- Complex field relationships and conditional logic
- Advanced payment options (subscription packs, fallback billing)
- Advanced form layouts and styling
- Multi-step forms with advanced navigation
- Advanced user management and role restrictions
- Content restriction and access control

## Form Settings Examples

### Basic Blog Form Settings (Free)
```json
{
  "show_form_title": "on",
  "form_description": "Submit your blog post using this form",
  "post_type": "post",
  "default_category": "uncategorized",
  "post_status": "draft",
  "redirect_to": "same",
  "message": "Your post has been submitted successfully!",
  "guest_post": "true",
  "guest_details": "true"
}
```

### E-commerce Product Form Settings (Pro)
```json
{
  "show_form_title": "on",
  "form_description": "Submit your product listing",
  "post_type": "product",
  "post_status": "pending",
  "redirect_to": "same",
  "message": "Product submitted successfully!",
  "payment_options": "on",
  "choose_payment_option": "enable_pay_per_post",
  "pay_per_post_cost": "10.00",
  "notification": {
    "new": "on",
    "new_subject": "New product submitted",
    "new_body": "A new product has been submitted: {post_title}"
  }
}
```