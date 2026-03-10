Vue.component('field-select', {
    template: '#tmpl-wpuf-field-select',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    data: function () {
        return {
            showOptions: false,
            selectedOption: 'Select an option',
        };
    },

    mounted: function() {
        // Initialize selectedOption when component mounts
        this.initializeSelectedOption();
    },

    watch: {
        value: {
            handler: function(newVal) {
                // Update selectedOption when value changes
                
                this.initializeSelectedOption();
            },
            immediate: true
        },
        'editing_form_field': {
            handler: function(newVal, oldVal) {
                // When the entire editing_form_field object changes (like on data load)
                this.initializeSelectedOption();
            },
            deep: true
        },
        'option_field.options': {
            handler: function(newVal) {
                // When options change, reinitialize
                this.initializeSelectedOption();
            },
            deep: true
        }
    },

    methods: {
        initializeSelectedOption: function() {
            var self = this;
            this.$nextTick(function() {
                // Get the current value
                var currentValue = self.editing_form_field[self.option_field.name];
                
                if (currentValue && self.option_field.options && self.option_field.options[currentValue]) {
                    self.selectedOption = self.option_field.options[currentValue];
                } else if (!currentValue && self.option_field.default && self.option_field.options && self.option_field.options[self.option_field.default]) {
                    // If no value but there's a default, show the default
                    self.selectedOption = self.option_field.options[self.option_field.default];
                    // Also set the value to default if there's no current value
                    if (!currentValue) {
                        self.value = self.option_field.default;
                    }
                } else {
                    self.selectedOption = 'Select an option';
                }
            });
        }
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
    }
});
