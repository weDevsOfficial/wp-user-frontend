(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var tabs = document.querySelector('.wpuf-settings-wrap').querySelectorAll('h2 a');
        var content = document.querySelectorAll('.wpuf-settings-wrap .metabox-holder th');
        var close = document.querySelector('#wpuf-search-section span');

        var search_input = document.querySelector('#wpuf-settings-search');

        search_input.addEventListener('keyup', function (e) {
            var search_value = e.target.value.toLowerCase();
            var value_tab = [];

            if (search_value.length) {
                close.style.display = 'flex';
                content.forEach(function (row, index) {

                    var content_id = row.closest('div').getAttribute('id');
                    var tab_id = content_id + '-tab';
                    var found_value = row.innerText.toLowerCase().includes(search_value);

                    if (found_value) {
                        row.closest('tr').style.display = 'table-row';
                    } else {
                        row.closest('tr').style.display = 'none';
                    }

                    if ('wpuf_mails' === content_id) {
                        row.closest('tbody').querySelectorAll('tr').forEach(function (tr) {
                            tr.style.display = '';
                        });
                    }

                    if (found_value === true && !value_tab.includes(tab_id)) {
                        value_tab.push(tab_id);
                    }
                })

                if (value_tab.length) {
                    document.getElementById(value_tab[0]).click();
                }

                tabs.forEach(function (tab) {
                    var tab_id = tab.getAttribute('id');
                    if (!value_tab.includes(tab_id)) {
                        document.getElementById(tab_id).style.display = 'none';
                    } else {
                        document.getElementById(tab_id).style.display = 'block';
                    }
                });

            } else {
                wpuf_search_reset();
            }
        })

        close.addEventListener('click', function (event) {
            wpuf_search_reset();
            search_input.value = '';
            close.style.display = 'none';
        })

        // Gateway Selector Card Grid
        wpufInitGatewaySelector();

        function wpuf_search_reset() {
            content.forEach(function (row, index) {
                var content_id = row.closest('div').getAttribute('id');
                var tab_id = content_id + '-tab';
                document.getElementById(content_id).style.display = '';
                document.getElementById(tab_id).style.display = '';
                document.getElementById('wpuf_general-tab').click();
            })
            document.querySelector('.wpuf-settings-wrap .metabox-holder').querySelectorAll('tr').forEach(function (row) {
                row.style.display = '';
            });

        }

        // --- Accordion for Payment Gateways ---
        var paymentTab = document.getElementById('wpuf_payment');
        if (paymentTab) {
            var table = paymentTab.querySelector('table.form-table');
            if (table) {
                var trs = Array.from(table.querySelectorAll('tbody > tr'));
                var groups = {
                    paypal: {
                        title: 'PayPal',
                        iconClass: 'wpuf-gateway-icon wpuf-gateway-icon--paypal',
                        rows: [],
                        existingCheckbox: null
                    },
                    bank: {
                        title: 'Bank Transfer',
                        iconClass: 'wpuf-gateway-icon wpuf-gateway-icon--bank',
                        rows: [],
                        existingCheckbox: null
                    },
                    stripe: {
                        title: 'Credit Card',
                        iconClass: 'wpuf-gateway-icon wpuf-gateway-icon--stripe',
                        rows: [],
                        existingCheckbox: null
                    }
                };

                var allowGatewaysTr = null;

                trs.forEach(function (tr) {
                    var html = tr.innerHTML;
                    if (html.includes('wpuf_payment[active_gateways]')) {
                        allowGatewaysTr = tr;
                    }
                });

                // Card grid in allowGatewaysTr handles enable/disable.
                // Do NOT hide it and do NOT move checkboxes — accordion only groups per-gateway settings rows below.

                // Use the data-gateway-id tag set by wpufInitGatewaySelector (runs before this block).
                // This includes mapped fields like failed_retry → paypal.
                trs.forEach(function (tr) {
                    if (tr === allowGatewaysTr) return;
                    var gid = tr.getAttribute('data-gateway-id');
                    if (gid && groups[gid]) {
                        groups[gid].rows.push(tr);
                    }
                });

                var theTbody = table.querySelector('tbody');

                // Resolve the existing PRO badge img URL — search ALL tabs including hidden ones
                var proIconSrcUrl = '';
                var proIconImg = document.querySelector('.pro-icon img');
                if (!proIconImg) {
                    proIconImg = document.querySelector('span.pro-icon img');
                }
                if (proIconImg) {
                    proIconSrcUrl = proIconImg.getAttribute('src');
                } else {
                    var anyImg = document.querySelector('img[src*="wp-user-frontend"][src*="assets"]');
                    if (anyImg) {
                        proIconSrcUrl = anyImg.getAttribute('src').split('/assets/')[0] + '/assets/images/pro-badge.svg';
                    }
                }

                function createProIconNode() {
                    if (proIconSrcUrl) {
                        var img = document.createElement('img');
                        img.src = proIconSrcUrl;
                        img.alt = 'PRO';
                        img.style.verticalAlign = 'middle';
                        return img;
                    }
                    var fallback = document.createElement('span');
                    fallback.className = 'wpuf-pro-badge-fallback';
                    fallback.setAttribute('role', 'img');
                    fallback.setAttribute('aria-label', 'PRO');
                    return fallback;
                }

                Object.keys(groups).forEach(function (key) {
                    var group = groups[key];
                    
                    // Stripe is disabled only when no stripe settings rows are registered in PHP
                    // (PRO off / stripe module off). If PRO active + stripe module on, rows > 0.
                    var isProFeatureDisabled = (key === 'stripe' && group.rows.length === 0);

                    if (isProFeatureDisabled) {
                        var dummyTr = document.createElement('tr');
                        var dummyTh = document.createElement('th');
                        dummyTh.scope = 'row';

                        // "Enable Gateway" label + PRO icon badge — same pattern used throughout the plugin:
                        // <label>Enable Gateway <span class="pro-icon"><img src="...pro-badge.svg"></span></label>
                        var enableLabel = document.createElement('label');
                        enableLabel.appendChild(document.createTextNode('Enable Gateway '));
                        var proIconSpan = document.createElement('span');
                        proIconSpan.className = 'pro-icon';
                        proIconSpan.appendChild(createProIconNode());
                        enableLabel.appendChild(proIconSpan);
                        dummyTh.appendChild(enableLabel);

                        var dummyTd = document.createElement('td');
                        var upsellP = document.createElement('p');
                        upsellP.className = 'wpuf-gateway-pro-upsell';
                        upsellP.appendChild(document.createTextNode('Available with '));
                        var upsellLink = document.createElement('a');
                        upsellLink.href = 'https://wedevs.com/wp-user-frontend-pro/';
                        upsellLink.target = '_blank';
                        upsellLink.textContent = 'WPUF Pro';
                        upsellP.appendChild(upsellLink);
                        upsellP.appendChild(document.createTextNode('.'));
                        dummyTd.appendChild(upsellP);

                        dummyTr.appendChild(dummyTh);
                        dummyTr.appendChild(dummyTd);
                        group.rows.push(dummyTr);
                    }

                    if (group.rows.length === 0) return;

                    var triggerRow = document.createElement('tr');
                    var triggerCell = document.createElement('td');
                    triggerCell.colSpan = 2;
                    triggerCell.style.padding = '0';

                    var triggerBtn = document.createElement('button');
                    triggerBtn.type = 'button';
                    triggerBtn.className = 'wpuf-accordion-trigger';
                    triggerBtn.setAttribute('data-target-group', key);

                    var titleSpan = document.createElement('span');
                    titleSpan.style.display = 'flex';
                    titleSpan.style.alignItems = 'center';
                    var iconSpan = document.createElement('span');
                    iconSpan.className = group.iconClass;
                    titleSpan.appendChild(iconSpan);
                    titleSpan.appendChild(document.createTextNode(group.title));

                    if (isProFeatureDisabled) {
                        triggerBtn.classList.add('is-disabled');
                        var badgeWrapper = document.createElement('div');
                        badgeWrapper.className = 'wpuf-gateway-pro-badge';
                        var badgeSpan = document.createElement('span');
                        badgeSpan.className = 'pro-icon';
                        badgeSpan.title = 'Pro Feature';
                        badgeSpan.appendChild(createProIconNode());
                        badgeWrapper.appendChild(badgeSpan);
                        titleSpan.appendChild(badgeWrapper);
                    }

                    triggerBtn.appendChild(titleSpan);
                    triggerCell.appendChild(triggerBtn);
                    triggerRow.appendChild(triggerCell);

                    theTbody.appendChild(triggerRow);

                    // We add a specific class to the gateway setting rows to hide them by default
                    group.rows.forEach(function (r) {
                        r.classList.add('wpuf-accordion-row');
                        r.setAttribute('data-group', key);
                        theTbody.appendChild(r);
                    });

                    // Event listener for toggle 
                    triggerBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        if (isProFeatureDisabled) {
                            return;
                        }

                        var isActive = this.classList.contains('active');

                        // Close all accordions
                        document.querySelectorAll('.wpuf-accordion-trigger').forEach(function (btn) {
                            btn.classList.remove('active');
                            var g = btn.getAttribute('data-target-group');
                            document.querySelectorAll('tr[data-group="' + g + '"]').forEach(function (tr) {
                                tr.classList.remove('active');
                            });
                        });

                        // Toggle current
                        if (!isActive) {
                            this.classList.add('active');
                            document.querySelectorAll('tr[data-group="' + key + '"]').forEach(function (tr) {
                                tr.classList.add('active');
                            });
                        }
                    });
                });
            }
        }

    });

    /**
     * Gateway Selector Card Grid
     *
     * Handles card click to toggle checkbox (multi-select) and
     * shows only the clicked gateway's settings rows below.
     */
    function wpufInitGatewaySelector() {
        var container = document.querySelector('.wpuf-gateway-cards');

        if ( ! container ) {
            return;
        }

        // Map gateway IDs to their settings field name prefixes.
        // PayPal fields: paypal_*, gate_instruct_paypal
        // Bank fields: bank_*, gate_instruct_bank
        // Generic pattern: field name contains the gateway ID
        var gatewayCards = container.querySelectorAll('.wpuf-gateway-card');

        // Find the <tr> that contains the gateway selector itself
        var selectorRow = container.closest('tr');

        if ( ! selectorRow ) {
            return;
        }

        // Collect all <tr> siblings after the selector row in the same table
        var formTable  = selectorRow.closest('table');
        var allRows    = formTable ? formTable.querySelectorAll('tr') : [];
        var afterRows  = [];
        var pastSelector = false;

        allRows.forEach(function(row) {
            if ( row === selectorRow ) {
                pastSelector = true;
                return;
            }

            if ( pastSelector ) {
                afterRows.push(row);
            }
        });

        // Fields whose names don't contain a gateway ID but belong to one
        var fieldGatewayMap = {
            'failed_retry': 'paypal',
        };

        /**
         * Determine which gateway a settings row belongs to by
         * checking the name attribute of inputs/selects/textareas inside it.
         */
        function getRowGatewayId(row) {
            var inputs = row.querySelectorAll('input, select, textarea');
            var gatewayIds = [];

            gatewayCards.forEach(function(card) {
                gatewayIds.push(card.getAttribute('data-gateway'));
            });

            for ( var i = 0; i < inputs.length; i++ ) {
                var name = inputs[i].getAttribute('name') || '';
                // Extract field name from wpuf_payment[field_name]
                var match = name.match(/\[([^\]]+)\]$/);

                if ( match ) {
                    var fieldName = match[1];

                    // Check explicit field-to-gateway mapping first
                    if ( fieldGatewayMap[fieldName] ) {
                        return fieldGatewayMap[fieldName];
                    }

                    for ( var j = 0; j < gatewayIds.length; j++ ) {
                        var gid = gatewayIds[j];

                        // Match: gate_instruct_paypal, paypal_email, bank_success, etc.
                        if ( fieldName.indexOf(gid) !== -1 || fieldName.indexOf('gate_instruct_' + gid) !== -1 ) {
                            return gid;
                        }
                    }
                }
            }

            // Check the <th> label text for a gateway ID match
            var thLabel = row.querySelector('th');
            if ( thLabel ) {
                var labelText = thLabel.getAttribute('scope') === 'row' ? (thLabel.textContent || '') : '';

                for ( var k = 0; k < gatewayIds.length; k++ ) {
                    if ( labelText.toLowerCase().indexOf(gatewayIds[k]) !== -1 ) {
                        return gatewayIds[k];
                    }
                }

                // Check the label[for] attribute (e.g. "wpuf_payment[paypal_webhook_events_info]")
                var labelEl = thLabel.querySelector('label[for]');
                if ( labelEl ) {
                    var forAttr = labelEl.getAttribute('for') || '';
                    var forMatch = forAttr.match(/\[([^\]]+)\]$/);
                    if ( forMatch ) {
                        var forFieldName = forMatch[1];

                        if ( fieldGatewayMap[forFieldName] ) {
                            return fieldGatewayMap[forFieldName];
                        }

                        for ( var m = 0; m < gatewayIds.length; m++ ) {
                            if ( forFieldName.indexOf(gatewayIds[m]) !== -1 ) {
                                return gatewayIds[m];
                            }
                        }
                    }
                }
            }

            return null;
        }

        // Tag each row with its gateway ID
        afterRows.forEach(function(row) {
            var gid = getRowGatewayId(row);

            if ( gid ) {
                row.classList.add('wpuf-gateway-setting-row');
                row.setAttribute('data-gateway-id', gid);
            }
        });

        /**
         * Update the focused (green border) state on gateway cards
         */
        function setFocusedCard(gatewayId) {
            gatewayCards.forEach(function(card) {
                if ( card.getAttribute('data-gateway') === gatewayId ) {
                    card.classList.add('wpuf-gateway-card--focused');
                } else {
                    card.classList.remove('wpuf-gateway-card--focused');
                }
            });
        }

        /**
         * Show settings rows for a specific gateway, hide others
         */
        function showGatewaySettings(gatewayId) {
            afterRows.forEach(function(row) {
                if ( ! row.classList.contains('wpuf-gateway-setting-row') ) {
                    return;
                }

                if ( row.getAttribute('data-gateway-id') === gatewayId ) {
                    row.classList.remove('wpuf-gateway-setting-hidden');
                } else {
                    row.classList.add('wpuf-gateway-setting-hidden');
                }
            });

            setFocusedCard(gatewayId);
        }

        /**
         * Hide all gateway-specific settings rows
         */
        function hideAllGatewaySettings() {
            afterRows.forEach(function(row) {
                if ( row.classList.contains('wpuf-gateway-setting-row') ) {
                    row.classList.add('wpuf-gateway-setting-hidden');
                }
            });

            setFocusedCard(null);
        }

        // Checkbox change handler (triggered by the <label> toggle in the top-right corner)
        gatewayCards.forEach(function(card) {
            var checkbox = card.querySelector('.wpuf-gateway-card__checkbox');

            checkbox.addEventListener('change', function() {
                var gatewayId = card.getAttribute('data-gateway');

                if ( checkbox.checked ) {
                    card.classList.add('wpuf-gateway-card--active');
                } else {
                    card.classList.remove('wpuf-gateway-card--active');
                }

                // Show settings of the toggled gateway if checked,
                // fall back to first remaining active, or hide all
                var anyActive = container.querySelector('.wpuf-gateway-card--active');

                if ( checkbox.checked ) {
                    showGatewaySettings(gatewayId);
                } else if ( anyActive ) {
                    showGatewaySettings(anyActive.getAttribute('data-gateway'));
                } else {
                    hideAllGatewaySettings();
                }
            });
        });

        // Card body click handler — only shows settings, does not toggle selection
        gatewayCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                // Ignore clicks on the toggle label or checkbox (those handle selection)
                if ( e.target.closest('.wpuf-gateway-card__toggle') ) {
                    return;
                }

                if ( card.classList.contains('wpuf-gateway-card--pro-locked') ) {
                    return;
                }

                var gatewayId = card.getAttribute('data-gateway');
                showGatewaySettings(gatewayId);
            });
        });

        // On page load, show settings for the first active gateway, or hide all
        var firstActive = container.querySelector('.wpuf-gateway-card--active');

        if ( firstActive ) {
            showGatewaySettings(firstActive.getAttribute('data-gateway'));
        } else {
            hideAllGatewaySettings();
        }
    }
})();
