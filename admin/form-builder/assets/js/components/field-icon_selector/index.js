Vue.component('field-icon_selector', {
    template: '#tmpl-wpuf-field-icon_selector',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],
    
    mounted: function() {
        document.addEventListener('click', this.handleClickOutside);
    },

    data: function () {
        return {
            showIconPicker: false,
            searchTerm: '',
            icons: this.getAllIcons()
        };
    },

    computed: {
        value: {
            get: function () {
                return this.editing_form_field[this.option_field.name];
            },

            set: function (value) {
                this.$store.commit('update_editing_form_field', {
                    editing_field_id: this.editing_form_field.id,
                    field_name: this.option_field.name,
                    value: value
                });
            }
        },

        selectedIconDisplay: function() {
            if (this.value) {
                var icon = this.icons.find(function(item) {
                    return item.class === this.value;
                }.bind(this));
                return icon ? icon.name : this.value;
            }
            return 'Select an icon';
        },

        filteredIcons: function() {
            var self = this;
            if (!this.icons.length) return [];
            
            if (!this.searchTerm) return this.icons;
            
            var searchLower = this.searchTerm.toLowerCase();
            return this.icons.filter(function(icon) {
                return icon.name.toLowerCase().indexOf(searchLower) !== -1 ||
                       icon.keywords.toLowerCase().indexOf(searchLower) !== -1;
            });
        }
    },

    methods: {
        getAllIcons: function() {
            // Comprehensive Font Awesome 6 icon collection
            return [
                // User & People
                { class: 'fas fa-user', name: 'User', keywords: 'person account profile' },
                { class: 'far fa-user', name: 'User Outline', keywords: 'person account profile outline' },
                { class: 'fas fa-users', name: 'Users', keywords: 'people group team' },
                { class: 'fas fa-user-circle', name: 'User Circle', keywords: 'person account profile avatar' },
                { class: 'fas fa-user-plus', name: 'Add User', keywords: 'person account new register' },
                { class: 'fas fa-user-minus', name: 'Remove User', keywords: 'person account delete' },
                { class: 'fas fa-user-check', name: 'User Check', keywords: 'person verified approve' },
                { class: 'fas fa-user-edit', name: 'Edit User', keywords: 'person account modify' },
                { class: 'fas fa-user-cog', name: 'User Settings', keywords: 'person account config' },
                { class: 'fas fa-user-graduate', name: 'Graduate', keywords: 'student education' },
                { class: 'fas fa-user-tie', name: 'Business User', keywords: 'person professional' },
                
                // Communication
                { class: 'fas fa-envelope', name: 'Email', keywords: 'mail message contact' },
                { class: 'far fa-envelope', name: 'Email Outline', keywords: 'mail message contact outline' },
                { class: 'fas fa-envelope-open', name: 'Open Email', keywords: 'mail read message' },
                { class: 'fas fa-phone', name: 'Phone', keywords: 'call telephone contact' },
                { class: 'fas fa-mobile-alt', name: 'Mobile', keywords: 'phone cell smartphone' },
                { class: 'fas fa-comment', name: 'Comment', keywords: 'message chat talk' },
                { class: 'far fa-comment', name: 'Comment Outline', keywords: 'message chat talk outline' },
                { class: 'fas fa-comments', name: 'Comments', keywords: 'messages chat conversation' },
                { class: 'fas fa-inbox', name: 'Inbox', keywords: 'mail messages receive' },
                { class: 'fas fa-paper-plane', name: 'Send', keywords: 'submit message mail' },
                { class: 'fas fa-at', name: 'At Symbol', keywords: 'email address mention' },
                { class: 'fas fa-bullhorn', name: 'Megaphone', keywords: 'announce speaker' },
                
                // Security & Privacy
                { class: 'fas fa-lock', name: 'Lock', keywords: 'secure password private' },
                { class: 'fas fa-unlock', name: 'Unlock', keywords: 'open access' },
                { class: 'fas fa-key', name: 'Key', keywords: 'password access login' },
                { class: 'fas fa-shield-alt', name: 'Shield', keywords: 'protect security safe' },
                { class: 'fas fa-eye', name: 'Eye', keywords: 'view show visible' },
                { class: 'fas fa-eye-slash', name: 'Eye Slash', keywords: 'hide invisible password' },
                { class: 'fas fa-fingerprint', name: 'Fingerprint', keywords: 'security biometric' },
                
                // Location & Navigation
                { class: 'fas fa-home', name: 'Home', keywords: 'house address main' },
                { class: 'fas fa-map-marker-alt', name: 'Location', keywords: 'pin address place' },
                { class: 'fas fa-map', name: 'Map', keywords: 'location navigation' },
                { class: 'fas fa-globe', name: 'Globe', keywords: 'world earth website' },
                { class: 'fas fa-compass', name: 'Compass', keywords: 'direction navigation' },
                { class: 'fas fa-route', name: 'Route', keywords: 'path direction way' },
                { class: 'fas fa-city', name: 'City', keywords: 'urban buildings town' },
                
                // Business & Finance
                { class: 'fas fa-building', name: 'Building', keywords: 'office company business' },
                { class: 'far fa-building', name: 'Building Outline', keywords: 'office company business outline' },
                { class: 'fas fa-briefcase', name: 'Briefcase', keywords: 'business work job' },
                { class: 'fas fa-credit-card', name: 'Credit Card', keywords: 'payment money finance' },
                { class: 'far fa-credit-card', name: 'Credit Card Outline', keywords: 'payment money finance outline' },
                { class: 'fas fa-dollar-sign', name: 'Dollar', keywords: 'money currency price' },
                { class: 'fas fa-euro-sign', name: 'Euro', keywords: 'money currency price' },
                { class: 'fas fa-chart-line', name: 'Line Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-chart-bar', name: 'Bar Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-chart-pie', name: 'Pie Chart', keywords: 'graph analytics stats' },
                { class: 'fas fa-calculator', name: 'Calculator', keywords: 'math compute numbers' },
                
                // Date & Time
                { class: 'fas fa-calendar', name: 'Calendar', keywords: 'date time schedule' },
                { class: 'far fa-calendar', name: 'Calendar Outline', keywords: 'date time schedule outline' },
                { class: 'fas fa-calendar-alt', name: 'Calendar Alt', keywords: 'date time schedule' },
                { class: 'fas fa-clock', name: 'Clock', keywords: 'time hour minute' },
                { class: 'far fa-clock', name: 'Clock Outline', keywords: 'time hour minute outline' },
                { class: 'fas fa-stopwatch', name: 'Stopwatch', keywords: 'timer time measure' },
                { class: 'fas fa-history', name: 'History', keywords: 'time past previous' },
                
                // Files & Documents
                { class: 'fas fa-file', name: 'File', keywords: 'document paper text' },
                { class: 'far fa-file', name: 'File Outline', keywords: 'document paper text outline' },
                { class: 'fas fa-file-alt', name: 'File Text', keywords: 'document text content' },
                { class: 'fas fa-file-pdf', name: 'PDF File', keywords: 'document adobe pdf' },
                { class: 'fas fa-file-word', name: 'Word File', keywords: 'document microsoft doc' },
                { class: 'fas fa-file-excel', name: 'Excel File', keywords: 'spreadsheet microsoft xls' },
                { class: 'fas fa-folder', name: 'Folder', keywords: 'directory files storage' },
                { class: 'far fa-folder', name: 'Folder Outline', keywords: 'directory files storage outline' },
                { class: 'fas fa-folder-open', name: 'Folder Open', keywords: 'directory files browse' },
                
                // Media
                { class: 'fas fa-image', name: 'Image', keywords: 'photo picture media' },
                { class: 'far fa-image', name: 'Image Outline', keywords: 'photo picture media outline' },
                { class: 'fas fa-images', name: 'Images', keywords: 'photos pictures gallery' },
                { class: 'fas fa-video', name: 'Video', keywords: 'movie film media' },
                { class: 'fas fa-music', name: 'Music', keywords: 'audio sound song' },
                { class: 'fas fa-camera', name: 'Camera', keywords: 'photo picture capture' },
                { class: 'fas fa-play', name: 'Play', keywords: 'start video audio' },
                { class: 'fas fa-pause', name: 'Pause', keywords: 'stop video audio' },
                
                // Actions & Controls
                { class: 'fas fa-plus', name: 'Plus', keywords: 'add new create more' },
                { class: 'fas fa-minus', name: 'Minus', keywords: 'remove subtract less' },
                { class: 'fas fa-times', name: 'Close', keywords: 'x remove delete cancel' },
                { class: 'fas fa-check', name: 'Check', keywords: 'approve yes correct tick' },
                { class: 'fas fa-arrow-right', name: 'Arrow Right', keywords: 'next forward direction' },
                { class: 'fas fa-arrow-left', name: 'Arrow Left', keywords: 'back previous direction' },
                { class: 'fas fa-arrow-up', name: 'Arrow Up', keywords: 'top ascend direction' },
                { class: 'fas fa-arrow-down', name: 'Arrow Down', keywords: 'bottom descend direction' },
                { class: 'fas fa-edit', name: 'Edit', keywords: 'modify change update pencil' },
                { class: 'fas fa-trash', name: 'Delete', keywords: 'remove destroy bin' },
                { class: 'fas fa-save', name: 'Save', keywords: 'store keep preserve' },
                { class: 'fas fa-download', name: 'Download', keywords: 'get receive save' },
                { class: 'fas fa-upload', name: 'Upload', keywords: 'send attach share' },
                { class: 'fas fa-search', name: 'Search', keywords: 'find look magnify' },
                { class: 'fas fa-copy', name: 'Copy', keywords: 'duplicate clone' },
                
                // Status & Indicators
                { class: 'fas fa-heart', name: 'Heart', keywords: 'love like favorite' },
                { class: 'far fa-heart', name: 'Heart Outline', keywords: 'love like favorite outline' },
                { class: 'fas fa-star', name: 'Star', keywords: 'favorite rating bookmark' },
                { class: 'far fa-star', name: 'Star Outline', keywords: 'favorite rating bookmark outline' },
                { class: 'fas fa-bookmark', name: 'Bookmark', keywords: 'save favorite mark' },
                { class: 'fas fa-bell', name: 'Bell', keywords: 'notification alert alarm' },
                { class: 'fas fa-exclamation-triangle', name: 'Warning', keywords: 'alert danger caution' },
                { class: 'fas fa-info-circle', name: 'Info', keywords: 'information help about' },
                { class: 'fas fa-question-circle', name: 'Question', keywords: 'help support faq' },
                { class: 'fas fa-thumbs-up', name: 'Thumbs Up', keywords: 'like approve good' },
                { class: 'fas fa-thumbs-down', name: 'Thumbs Down', keywords: 'dislike disapprove bad' },
                
                // Web & Technology
                { class: 'fas fa-link', name: 'Link', keywords: 'url website connection' },
                { class: 'fas fa-wifi', name: 'WiFi', keywords: 'internet connection wireless' },
                { class: 'fas fa-code', name: 'Code', keywords: 'programming development html' },
                { class: 'fas fa-database', name: 'Database', keywords: 'data storage sql' },
                { class: 'fas fa-cloud', name: 'Cloud', keywords: 'storage online server' },
                { class: 'fas fa-desktop', name: 'Desktop', keywords: 'computer monitor pc' },
                { class: 'fas fa-laptop', name: 'Laptop', keywords: 'computer notebook portable' },
                { class: 'fas fa-tablet-alt', name: 'Tablet', keywords: 'ipad device mobile' },
                
                // Shopping & Commerce
                { class: 'fas fa-shopping-cart', name: 'Shopping Cart', keywords: 'buy purchase ecommerce' },
                { class: 'fas fa-shopping-bag', name: 'Shopping Bag', keywords: 'buy purchase store' },
                { class: 'fas fa-store', name: 'Store', keywords: 'shop business retail' },
                { class: 'fas fa-gift', name: 'Gift', keywords: 'present surprise reward' },
                { class: 'fas fa-tag', name: 'Tag', keywords: 'label price category' },
                { class: 'fas fa-tags', name: 'Tags', keywords: 'labels categories prices' },
                
                // Social Media
                { class: 'fab fa-facebook', name: 'Facebook', keywords: 'social media social network' },
                { class: 'fab fa-twitter', name: 'Twitter', keywords: 'social media tweet' },
                { class: 'fab fa-instagram', name: 'Instagram', keywords: 'social media photos' },
                { class: 'fab fa-linkedin', name: 'LinkedIn', keywords: 'professional network business' },
                { class: 'fab fa-youtube', name: 'YouTube', keywords: 'video social media' },
                { class: 'fab fa-github', name: 'GitHub', keywords: 'code development git' },
                { class: 'fab fa-whatsapp', name: 'WhatsApp', keywords: 'messaging chat mobile' },
                
                // Education
                { class: 'fas fa-graduation-cap', name: 'Graduation Cap', keywords: 'education school university' },
                { class: 'fas fa-book', name: 'Book', keywords: 'read education learning' },
                { class: 'fas fa-book-open', name: 'Open Book', keywords: 'read study learning' },
                { class: 'fas fa-pen', name: 'Pen', keywords: 'write edit signing' },
                { class: 'fas fa-pencil-alt', name: 'Pencil', keywords: 'write edit draw' },
                { class: 'fas fa-school', name: 'School', keywords: 'education building learning' },
                
                // Transportation
                { class: 'fas fa-car', name: 'Car', keywords: 'vehicle auto transport' },
                { class: 'fas fa-plane', name: 'Plane', keywords: 'flight travel airplane' },
                { class: 'fas fa-train', name: 'Train', keywords: 'transport railway travel' },
                { class: 'fas fa-bus', name: 'Bus', keywords: 'transport public travel' },
                { class: 'fas fa-bicycle', name: 'Bicycle', keywords: 'bike transport cycle' },
                { class: 'fas fa-ship', name: 'Ship', keywords: 'boat transport water' },
                
                // Health & Medical
                { class: 'fas fa-stethoscope', name: 'Stethoscope', keywords: 'medical doctor health' },
                { class: 'fas fa-pills', name: 'Pills', keywords: 'medicine health medication' },
                { class: 'fas fa-hospital', name: 'Hospital', keywords: 'medical health building' },
                { class: 'fas fa-ambulance', name: 'Ambulance', keywords: 'emergency medical health' },
                
                // Form Fields Common
                { class: 'fas fa-font', name: 'Text', keywords: 'typography text field input' },
                { class: 'fas fa-paragraph', name: 'Paragraph', keywords: 'text content textarea' },
                { class: 'fas fa-list-ul', name: 'Bullet List', keywords: 'items checklist options' },
                { class: 'fas fa-list-ol', name: 'Numbered List', keywords: 'ordered items steps' },
                { class: 'fas fa-check-square', name: 'Checkbox', keywords: 'select option choice tick' },
                { class: 'fas fa-dot-circle', name: 'Radio Button', keywords: 'select option choice' },
                { class: 'fas fa-caret-down', name: 'Dropdown', keywords: 'select options menu' },
                { class: 'fas fa-calendar-day', name: 'Date Picker', keywords: 'date calendar select' },
                { class: 'fas fa-toggle-on', name: 'Toggle On', keywords: 'switch enable yes' },
                { class: 'fas fa-toggle-off', name: 'Toggle Off', keywords: 'switch disable no' },
                
                // Miscellaneous
                { class: 'fas fa-cog', name: 'Settings', keywords: 'config gear options' },
                { class: 'fas fa-magic', name: 'Magic', keywords: 'wand special effect' },
                { class: 'fas fa-lightbulb', name: 'Idea', keywords: 'innovation creativity light' },
                { class: 'fas fa-trophy', name: 'Trophy', keywords: 'award winner achievement' },
                { class: 'fas fa-flag', name: 'Flag', keywords: 'country nation mark' },
                { class: 'fas fa-fire', name: 'Fire', keywords: 'hot trending popular' },
                { class: 'fas fa-rocket', name: 'Rocket', keywords: 'launch fast speed' },
                
                // Additional Common Icons
                { class: 'fas fa-address-book', name: 'Address Book', keywords: 'contacts directory phone' },
                { class: 'fas fa-address-card', name: 'Address Card', keywords: 'id contact information' },
                { class: 'fas fa-handshake', name: 'Handshake', keywords: 'agreement deal partnership' },
                { class: 'fas fa-id-card', name: 'ID Card', keywords: 'identification badge license' },
                { class: 'fas fa-birthday-cake', name: 'Birthday Cake', keywords: 'celebration cake party' },
                { class: 'fas fa-wine-glass', name: 'Wine Glass', keywords: 'drink alcohol celebration' },
                { class: 'fas fa-coffee', name: 'Coffee', keywords: 'drink cafe beverage' },
                { class: 'fas fa-pizza-slice', name: 'Pizza', keywords: 'food restaurant meal' },
                { class: 'fas fa-hamburger', name: 'Hamburger', keywords: 'food restaurant meal' },
                { class: 'fas fa-ice-cream', name: 'Ice Cream', keywords: 'dessert food sweet' },
                { class: 'fas fa-gamepad', name: 'Gaming', keywords: 'games controller entertainment' },
                { class: 'fas fa-football-ball', name: 'Football', keywords: 'sports game ball' },
                { class: 'fas fa-basketball-ball', name: 'Basketball', keywords: 'sports game ball' },
                { class: 'fas fa-tennis-ball', name: 'Tennis Ball', keywords: 'sports game ball' },
                { class: 'fas fa-running', name: 'Running', keywords: 'exercise fitness sports' },
                { class: 'fas fa-dumbbell', name: 'Dumbbell', keywords: 'exercise fitness gym' },
                { class: 'fas fa-spa', name: 'Spa', keywords: 'wellness relaxation health' },
                { class: 'fas fa-smile', name: 'Smile', keywords: 'happy emotion face' },
                { class: 'fas fa-frown', name: 'Frown', keywords: 'sad emotion face' },
                { class: 'fas fa-meh', name: 'Neutral Face', keywords: 'neutral emotion face' },
                { class: 'fas fa-mask', name: 'Mask', keywords: 'protection health safety' },
                { class: 'fas fa-temperature-high', name: 'Temperature High', keywords: 'fever health hot' },
                { class: 'fas fa-temperature-low', name: 'Temperature Low', keywords: 'cold health cool' },
                { class: 'fas fa-snowflake', name: 'Snowflake', keywords: 'winter cold weather' },
                { class: 'fas fa-sun', name: 'Sun', keywords: 'weather sunny bright' },
                { class: 'fas fa-moon', name: 'Moon', keywords: 'night dark lunar' },
                { class: 'fas fa-cloud-rain', name: 'Rain', keywords: 'weather storm water' },
                { class: 'fas fa-umbrella', name: 'Umbrella', keywords: 'rain weather protection' },
                { class: 'fas fa-mountain', name: 'Mountain', keywords: 'nature landscape peak' },
                { class: 'fas fa-tree', name: 'Tree', keywords: 'nature forest plant' },
                { class: 'fas fa-leaf', name: 'Leaf', keywords: 'nature plant green' },
                { class: 'fas fa-seedling', name: 'Seedling', keywords: 'plant growth nature' },
                { class: 'fas fa-paw', name: 'Paw', keywords: 'animal pet dog cat' },
                { class: 'fas fa-dog', name: 'Dog', keywords: 'pet animal canine' },
                { class: 'fas fa-cat', name: 'Cat', keywords: 'pet animal feline' },
                { class: 'fas fa-fish', name: 'Fish', keywords: 'animal water sea' },
                { class: 'fas fa-horse', name: 'Horse', keywords: 'animal riding equine' },
                { class: 'fas fa-dove', name: 'Dove', keywords: 'bird peace animal' },
                { class: 'fas fa-bug', name: 'Bug', keywords: 'insect error issue' },
                { class: 'fas fa-spider', name: 'Spider', keywords: 'insect web animal' },
                { class: 'fas fa-hammer', name: 'Hammer', keywords: 'tool build construction' },
                { class: 'fas fa-wrench', name: 'Wrench', keywords: 'tool fix repair' },
                { class: 'fas fa-screwdriver', name: 'Screwdriver', keywords: 'tool fix repair' },
                { class: 'fas fa-paint-brush', name: 'Paint Brush', keywords: 'art design creative' },
                { class: 'fas fa-palette', name: 'Palette', keywords: 'art color design' },
                { class: 'fas fa-gem', name: 'Gem', keywords: 'diamond precious jewel' },
                { class: 'fas fa-crown', name: 'Crown', keywords: 'king queen royalty' },
                { class: 'fas fa-award', name: 'Award', keywords: 'medal prize achievement' },
                { class: 'fas fa-medal', name: 'Medal', keywords: 'award prize winner' },
                { class: 'fas fa-ribbon', name: 'Ribbon', keywords: 'award decoration banner' },
                
                // More Communication & Contact Icons
                { class: 'fas fa-phone-alt', name: 'Phone Alt', keywords: 'call telephone contact mobile' },
                { class: 'fas fa-phone-square', name: 'Phone Square', keywords: 'call telephone contact' },
                { class: 'fas fa-fax', name: 'Fax', keywords: 'fax machine communication' },
                { class: 'fas fa-voicemail', name: 'Voicemail', keywords: 'message voice recording' },
                { class: 'fas fa-comment-dots', name: 'Comment Dots', keywords: 'chat message conversation' },
                { class: 'fas fa-comment-slash', name: 'Comment Slash', keywords: 'no chat mute message' },
                { class: 'fas fa-sms', name: 'SMS', keywords: 'text message mobile' },
                { class: 'fas fa-mail-bulk', name: 'Mail Bulk', keywords: 'newsletter mailing list' },
                
                // Technology & Digital
                { class: 'fas fa-server', name: 'Server', keywords: 'hosting technology database' },
                { class: 'fas fa-hard-drive', name: 'Hard Drive', keywords: 'storage disk technology' },
                { class: 'fas fa-microchip', name: 'Microchip', keywords: 'processor cpu technology' },
                { class: 'fas fa-memory', name: 'Memory', keywords: 'ram chip technology' },
                { class: 'fas fa-ethernet', name: 'Ethernet', keywords: 'network cable connection' },
                { class: 'fas fa-broadcast-tower', name: 'Broadcast Tower', keywords: 'signal antenna transmission' },
                { class: 'fas fa-satellite', name: 'Satellite', keywords: 'space communication signal' },
                { class: 'fas fa-router', name: 'Router', keywords: 'network internet device' },
                { class: 'fas fa-keyboard', name: 'Keyboard', keywords: 'input device typing' },
                { class: 'fas fa-mouse', name: 'Mouse', keywords: 'input device pointer' },
                { class: 'fas fa-print', name: 'Print', keywords: 'printer document output' },
                { class: 'fas fa-scanner', name: 'Scanner', keywords: 'scan document input' },
                { class: 'fas fa-tv', name: 'Television', keywords: 'monitor screen display' },
                { class: 'fas fa-mobile', name: 'Mobile Phone', keywords: 'smartphone device' },
                { class: 'fas fa-sim-card', name: 'SIM Card', keywords: 'mobile phone card' },
                { class: 'fas fa-sd-card', name: 'SD Card', keywords: 'memory storage card' },
                { class: 'fas fa-usb', name: 'USB', keywords: 'connector cable device' },
                { class: 'fas fa-plug', name: 'Power Plug', keywords: 'electricity power connector' },
                { class: 'fas fa-battery-full', name: 'Battery Full', keywords: 'power charge full' },
                { class: 'fas fa-battery-half', name: 'Battery Half', keywords: 'power charge medium' },
                { class: 'fas fa-battery-empty', name: 'Battery Empty', keywords: 'power charge low' },
                
                // More Actions & Navigation
                { class: 'fas fa-chevron-up', name: 'Chevron Up', keywords: 'arrow up direction' },
                { class: 'fas fa-chevron-down', name: 'Chevron Down', keywords: 'arrow down direction' },
                { class: 'fas fa-chevron-left', name: 'Chevron Left', keywords: 'arrow left direction' },
                { class: 'fas fa-chevron-right', name: 'Chevron Right', keywords: 'arrow right direction' },
                { class: 'fas fa-angle-up', name: 'Angle Up', keywords: 'arrow up direction small' },
                { class: 'fas fa-angle-down', name: 'Angle Down', keywords: 'arrow down direction small' },
                { class: 'fas fa-angle-left', name: 'Angle Left', keywords: 'arrow left direction small' },
                { class: 'fas fa-angle-right', name: 'Angle Right', keywords: 'arrow right direction small' },
                { class: 'fas fa-arrows-alt', name: 'Arrows Alt', keywords: 'expand resize move' },
                { class: 'fas fa-expand', name: 'Expand', keywords: 'fullscreen enlarge grow' },
                { class: 'fas fa-compress', name: 'Compress', keywords: 'minimize shrink reduce' },
                { class: 'fas fa-expand-arrows-alt', name: 'Expand Arrows', keywords: 'resize fullscreen' },
                { class: 'fas fa-external-link-alt', name: 'External Link', keywords: 'open new window link' },
                { class: 'fas fa-share', name: 'Share', keywords: 'social media distribute' },
                { class: 'fas fa-share-alt', name: 'Share Alt', keywords: 'social media distribute network' },
                { class: 'fas fa-redo', name: 'Redo', keywords: 'repeat again forward' },
                { class: 'fas fa-undo', name: 'Undo', keywords: 'reverse back previous' },
                { class: 'fas fa-sync', name: 'Sync', keywords: 'refresh reload update' },
                { class: 'fas fa-sync-alt', name: 'Sync Alt', keywords: 'refresh reload update rotate' },
                { class: 'fas fa-sort', name: 'Sort', keywords: 'arrange order organize' },
                { class: 'fas fa-sort-up', name: 'Sort Up', keywords: 'arrange ascending' },
                { class: 'fas fa-sort-down', name: 'Sort Down', keywords: 'arrange descending' },
                { class: 'fas fa-filter', name: 'Filter', keywords: 'search refine narrow' },
                { class: 'fas fa-random', name: 'Random', keywords: 'shuffle mix reorder' },
                
                // More Form Elements
                { class: 'fas fa-sliders-h', name: 'Sliders', keywords: 'range slider controls' },
                { class: 'fas fa-bars', name: 'Menu Bars', keywords: 'hamburger navigation menu' },
                { class: 'fas fa-ellipsis-h', name: 'More Options', keywords: 'dots menu options' },
                { class: 'fas fa-ellipsis-v', name: 'More Options Vertical', keywords: 'dots menu vertical' },
                { class: 'fas fa-grip-horizontal', name: 'Grip Horizontal', keywords: 'drag handle resize' },
                { class: 'fas fa-grip-vertical', name: 'Grip Vertical', keywords: 'drag handle vertical' },
                { class: 'fas fa-asterisk', name: 'Asterisk', keywords: 'required star important' },
                { class: 'fas fa-quote-left', name: 'Quote Left', keywords: 'testimonial citation' },
                { class: 'fas fa-quote-right', name: 'Quote Right', keywords: 'testimonial citation' },
                
                // More Status & Alerts
                { class: 'fas fa-check-circle', name: 'Check Circle', keywords: 'success approved valid' },
                { class: 'fas fa-times-circle', name: 'Times Circle', keywords: 'error cancel invalid' },
                { class: 'fas fa-exclamation-circle', name: 'Exclamation Circle', keywords: 'warning alert important' },
                { class: 'fas fa-minus-circle', name: 'Minus Circle', keywords: 'remove delete subtract' },
                { class: 'fas fa-plus-circle', name: 'Plus Circle', keywords: 'add create new' },
                { class: 'fas fa-question', name: 'Question', keywords: 'help support faq' },
                { class: 'fas fa-ban', name: 'Ban', keywords: 'prohibited forbidden stop' },
                { class: 'fas fa-stop-circle', name: 'Stop Circle', keywords: 'halt pause end' },
                { class: 'fas fa-play-circle', name: 'Play Circle', keywords: 'start begin video' },
                { class: 'fas fa-pause-circle', name: 'Pause Circle', keywords: 'stop wait break' },
                { class: 'fas fa-spinner', name: 'Spinner', keywords: 'loading progress wait' },
                { class: 'fas fa-circle-notch', name: 'Loading', keywords: 'spinner progress wait' },
                
                // Location & Places
                { class: 'fas fa-map-pin', name: 'Map Pin', keywords: 'location marker point' },
                { class: 'fas fa-map-marked', name: 'Map Marked', keywords: 'location navigation marked' },
                { class: 'fas fa-map-marked-alt', name: 'Map Marked Alt', keywords: 'location navigation pins' },
                { class: 'fas fa-directions', name: 'Directions', keywords: 'navigation route path' },
                { class: 'fas fa-location-arrow', name: 'Location Arrow', keywords: 'gps position direction' },
                { class: 'fas fa-street-view', name: 'Street View', keywords: 'maps google street' },
                { class: 'fas fa-hotel', name: 'Hotel', keywords: 'accommodation lodging stay' },
                { class: 'fas fa-hospital-alt', name: 'Hospital Alt', keywords: 'medical health clinic' },
                { class: 'fas fa-university', name: 'University', keywords: 'education college school' },
                { class: 'fas fa-church', name: 'Church', keywords: 'religion worship building' },
                { class: 'fas fa-mosque', name: 'Mosque', keywords: 'religion worship islam' },
                { class: 'fas fa-synagogue', name: 'Synagogue', keywords: 'religion worship judaism' },
                { class: 'fas fa-place-of-worship', name: 'Place of Worship', keywords: 'religion church temple' },
                { class: 'fas fa-gas-pump', name: 'Gas Station', keywords: 'fuel petrol station' },
                { class: 'fas fa-parking', name: 'Parking', keywords: 'car park space' },
                { class: 'fas fa-restroom', name: 'Restroom', keywords: 'bathroom toilet facilities' },
                
                // More Social & Brands
                { class: 'fab fa-google', name: 'Google', keywords: 'search engine brand' },
                { class: 'fab fa-apple', name: 'Apple', keywords: 'brand technology ios' },
                { class: 'fab fa-microsoft', name: 'Microsoft', keywords: 'brand technology windows' },
                { class: 'fab fa-amazon', name: 'Amazon', keywords: 'brand shopping ecommerce' },
                { class: 'fab fa-skype', name: 'Skype', keywords: 'video call communication' },
                { class: 'fab fa-discord', name: 'Discord', keywords: 'gaming chat communication' },
                { class: 'fab fa-slack', name: 'Slack', keywords: 'work communication team' },
                { class: 'fab fa-telegram', name: 'Telegram', keywords: 'messaging chat secure' },
                { class: 'fab fa-viber', name: 'Viber', keywords: 'messaging chat mobile' },
                { class: 'fab fa-snapchat', name: 'Snapchat', keywords: 'social media photos' },
                { class: 'fab fa-tiktok', name: 'TikTok', keywords: 'social media video' },
                { class: 'fab fa-pinterest', name: 'Pinterest', keywords: 'social media images' },
                { class: 'fab fa-reddit', name: 'Reddit', keywords: 'social media forum' },
                { class: 'fab fa-tumblr', name: 'Tumblr', keywords: 'blogging social media' },
                { class: 'fab fa-twitch', name: 'Twitch', keywords: 'streaming gaming video' },
                { class: 'fab fa-vimeo', name: 'Vimeo', keywords: 'video streaming platform' },
                { class: 'fab fa-spotify', name: 'Spotify', keywords: 'music streaming audio' },
                { class: 'fab fa-soundcloud', name: 'SoundCloud', keywords: 'music audio streaming' },
                
                // Food & Restaurants  
                { class: 'fas fa-utensils', name: 'Utensils', keywords: 'restaurant food dining' },
                { class: 'fas fa-wine-bottle', name: 'Wine Bottle', keywords: 'alcohol drink beverage' },
                { class: 'fas fa-beer', name: 'Beer', keywords: 'alcohol drink beverage' },
                { class: 'fas fa-cocktail', name: 'Cocktail', keywords: 'drink alcohol beverage' },
                { class: 'fas fa-apple-alt', name: 'Apple Fruit', keywords: 'fruit food healthy' },
                { class: 'fas fa-lemon', name: 'Lemon', keywords: 'fruit food citrus' },
                { class: 'fas fa-carrot', name: 'Carrot', keywords: 'vegetable food healthy' },
                { class: 'fas fa-pepper-hot', name: 'Hot Pepper', keywords: 'spicy food vegetable' },
                { class: 'fas fa-bread-slice', name: 'Bread', keywords: 'food bakery carb' },
                { class: 'fas fa-cheese', name: 'Cheese', keywords: 'food dairy protein' },
                { class: 'fas fa-egg', name: 'Egg', keywords: 'food protein breakfast' },
                
                // Time & Scheduling
                { class: 'fas fa-calendar-check', name: 'Calendar Check', keywords: 'schedule completed task' },
                { class: 'fas fa-calendar-plus', name: 'Calendar Plus', keywords: 'add event schedule' },
                { class: 'fas fa-calendar-minus', name: 'Calendar Minus', keywords: 'remove event schedule' },
                { class: 'fas fa-calendar-times', name: 'Calendar Times', keywords: 'cancel event schedule' },
                { class: 'fas fa-calendar-week', name: 'Calendar Week', keywords: 'weekly schedule view' },
                { class: 'fas fa-hourglass', name: 'Hourglass', keywords: 'time waiting duration' },
                { class: 'fas fa-hourglass-start', name: 'Hourglass Start', keywords: 'time beginning start' },
                { class: 'fas fa-hourglass-half', name: 'Hourglass Half', keywords: 'time progress middle' },
                { class: 'fas fa-hourglass-end', name: 'Hourglass End', keywords: 'time finished complete' },
                { class: 'fas fa-alarm-clock', name: 'Alarm Clock', keywords: 'wake up reminder time' },
                { class: 'fas fa-business-time', name: 'Business Time', keywords: 'work schedule office' },
                
                // More File Types
                { class: 'fas fa-file-image', name: 'Image File', keywords: 'photo picture media file' },
                { class: 'fas fa-file-video', name: 'Video File', keywords: 'movie media file' },
                { class: 'fas fa-file-audio', name: 'Audio File', keywords: 'music sound media file' },
                { class: 'fas fa-file-code', name: 'Code File', keywords: 'programming development file' },
                { class: 'fas fa-file-archive', name: 'Archive File', keywords: 'zip compressed file' },
                { class: 'fas fa-file-csv', name: 'CSV File', keywords: 'spreadsheet data file' },
                { class: 'fas fa-file-powerpoint', name: 'PowerPoint File', keywords: 'presentation slide file' },
                { class: 'fas fa-file-contract', name: 'Contract File', keywords: 'legal document agreement' },
                { class: 'fas fa-file-signature', name: 'Signature File', keywords: 'signed document legal' },
                { class: 'fas fa-file-invoice', name: 'Invoice File', keywords: 'bill payment document' },
                { class: 'fas fa-file-download', name: 'Download File', keywords: 'get save receive' },
                { class: 'fas fa-file-upload', name: 'Upload File', keywords: 'send attach share' },
                
                // More Weather
                { class: 'fas fa-cloud-sun', name: 'Partly Cloudy', keywords: 'weather mixed sunny' },
                { class: 'fas fa-cloud-moon', name: 'Cloudy Night', keywords: 'weather night overcast' },
                { class: 'fas fa-bolt', name: 'Lightning', keywords: 'storm thunder weather' },
                { class: 'fas fa-wind', name: 'Wind', keywords: 'weather breeze air' },
                { class: 'fas fa-rainbow', name: 'Rainbow', keywords: 'weather colorful arc' },
                
                // More Tools & Objects
                { class: 'fas fa-toolbox', name: 'Toolbox', keywords: 'repair maintenance kit' },
                { class: 'fas fa-drill', name: 'Drill', keywords: 'tool construction repair' },
                { class: 'fas fa-saw', name: 'Saw', keywords: 'tool construction cut' },
                { class: 'fas fa-ruler', name: 'Ruler', keywords: 'measure tool length' },
                { class: 'fas fa-compass-drafting', name: 'Drafting Compass', keywords: 'drawing tool geometry' },
                { class: 'fas fa-scissors', name: 'Scissors', keywords: 'cut tool office' },
                { class: 'fas fa-paperclip', name: 'Paperclip', keywords: 'attach office document' },
                { class: 'fas fa-thumbtack', name: 'Thumbtack', keywords: 'pin attach notice' },
                { class: 'fas fa-stapler', name: 'Stapler', keywords: 'office attach papers' },
                { class: 'fas fa-tape', name: 'Tape', keywords: 'adhesive stick office' },
                { class: 'fas fa-eraser', name: 'Eraser', keywords: 'remove delete correct' },
                { class: 'fas fa-highlighter', name: 'Highlighter', keywords: 'mark important text' },
                { class: 'fas fa-marker', name: 'Marker', keywords: 'write draw color' },
                { class: 'fas fa-pen-fancy', name: 'Fancy Pen', keywords: 'write signature elegant' },
                { class: 'fas fa-feather', name: 'Feather', keywords: 'write quill pen' },
                
                // More Miscellaneous
                { class: 'fas fa-anchor', name: 'Anchor', keywords: 'naval marine ship' },
                { class: 'fas fa-wheel', name: 'Wheel', keywords: 'tire circle rotation' },
                { class: 'fas fa-cogs', name: 'Cogs', keywords: 'settings gears multiple' },
                { class: 'fas fa-puzzle-piece', name: 'Puzzle Piece', keywords: 'solution fit together' },
                { class: 'fas fa-dice', name: 'Dice', keywords: 'game random chance' },
                { class: 'fas fa-chess', name: 'Chess', keywords: 'game strategy board' },
                { class: 'fas fa-cards', name: 'Playing Cards', keywords: 'game deck cards' },
                { class: 'fas fa-music-note', name: 'Music Note', keywords: 'audio sound melody' },
                { class: 'fas fa-theater-masks', name: 'Theater Masks', keywords: 'drama performance art' },
                { class: 'fas fa-masks-theater', name: 'Drama Masks', keywords: 'comedy tragedy theater' },
                { class: 'fas fa-guitar', name: 'Guitar', keywords: 'music instrument string' },
                { class: 'fas fa-drum', name: 'Drum', keywords: 'music instrument percussion' },
                { class: 'fas fa-microphone', name: 'Microphone', keywords: 'audio record voice' },
                { class: 'fas fa-headphones', name: 'Headphones', keywords: 'audio listen music' },
                { class: 'fas fa-volume-up', name: 'Volume Up', keywords: 'audio sound loud' },
                { class: 'fas fa-volume-down', name: 'Volume Down', keywords: 'audio sound quiet' },
                { class: 'fas fa-volume-mute', name: 'Volume Mute', keywords: 'audio sound off' },
                { class: 'fas fa-volume-off', name: 'Volume Off', keywords: 'audio sound silent' }
            ];
        },

        selectIcon: function(iconClass) {
            this.value = iconClass;
            this.showIconPicker = false;
        },

        clearIcon: function() {
            this.value = '';
            this.showIconPicker = false;
        },

        togglePicker: function() {
            this.showIconPicker = !this.showIconPicker;
        },

        handleClickOutside: function(event) {
            if (!this.$el.contains(event.target)) {
                this.showIconPicker = false;
            }
        }
    },

    beforeDestroy: function() {
        document.removeEventListener('click', this.handleClickOutside);
    }
});