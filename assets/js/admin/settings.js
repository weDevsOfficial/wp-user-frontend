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


    });

    var wpufI18n = window.wpufSettingsI18n || {};
    function wpufSprintf(template, value) {
        return String(template).replace('%s', value);
    }
    function wpufEscapeRegex(str) {
        return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Gateway Selector Card Grid
     *
     * Card click opens that gateway's settings panel; settings rows for
     * non-focused gateways stay hidden. Within the focused gateway,
     * non-enable rows stay hidden until the "Enable Gateway" toggle is on.
     * The hidden card checkbox is driven by that toggle.
     */
    function wpufInitGatewaySelector() {
        var container = document.querySelector('.wpuf-gateway-cards');

        if ( ! container ) {
            return;
        }

        var gatewayCards = container.querySelectorAll('.wpuf-gateway-card');
        var selectorRow  = container.closest('tr');

        if ( ! selectorRow ) {
            return;
        }

        // Collect all <tr> siblings after the selector row in the same table.
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

        // Fields whose names don't contain a gateway ID but belong to one.
        var fieldGatewayMap = {
            'failed_retry': 'paypal',
        };

        // Build gateway id list + boundary regex map once.
        var gatewayIds = [];
        var gatewayRegexMap = {};
        gatewayCards.forEach(function(card) {
            var id = card.getAttribute('data-gateway');
            if ( ! id ) return;
            gatewayIds.push(id);
            gatewayRegexMap[id] = new RegExp('(^|_)' + wpufEscapeRegex(id) + '(_|$)');
        });

        /**
         * Determine which gateway a settings row belongs to by
         * checking the name attribute of inputs/selects/textareas inside it.
         */
        function getRowGatewayId(row) {
            var inputs = row.querySelectorAll('input, select, textarea');

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
                        if ( gatewayRegexMap[gid].test(fieldName) ) {
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
                            if ( gatewayRegexMap[gatewayIds[m]].test(forFieldName) ) {
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

        var formTbody = formTable ? formTable.querySelector('tbody') : null;

        // Hide all gateway settings rows by default; card click reveals one gateway's rows.
        // Within the focused gateway, non-enable rows stay hidden until that gateway is enabled.
        function showGateway(gatewayId) {
            var focusedCard = container.querySelector('.wpuf-gateway-card[data-gateway="' + gatewayId + '"]');
            var focusedCheckbox = focusedCard ? focusedCard.querySelector('.wpuf-gateway-card__checkbox') : null;
            var enabled = focusedCheckbox ? focusedCheckbox.checked : false;

            afterRows.forEach(function(row) {
                if ( ! row.classList.contains('wpuf-gateway-setting-row') ) return;

                if ( row.getAttribute('data-gateway-id') !== gatewayId ) {
                    row.classList.add('wpuf-gateway-setting-hidden');
                    return;
                }

                if ( row.classList.contains('wpuf-gateway-enable-row') || enabled ) {
                    row.classList.remove('wpuf-gateway-setting-hidden');
                } else {
                    row.classList.add('wpuf-gateway-setting-hidden');
                }
            });

            gatewayCards.forEach(function(card) {
                if ( card.getAttribute('data-gateway') === gatewayId ) {
                    card.classList.add('wpuf-gateway-card--focused');
                } else {
                    card.classList.remove('wpuf-gateway-card--focused');
                }
            });
        }

        // Card click / Enter / Space → show that gateway's settings panel.
        function activateCard(card) {
            var gid = card.getAttribute('data-gateway');
            if ( gid ) {
                showGateway(gid);
            }
        }
        gatewayCards.forEach(function(card) {
            card.addEventListener('click', function() {
                activateCard(card);
            });
            card.addEventListener('keydown', function(e) {
                if ( e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar' ) {
                    e.preventDefault();
                    activateCard(card);
                }
            });
        });

        // Inject an "Enable Gateway" toggle row at the top of each gateway's
        // settings panel. Toggle drives the hidden card checkbox that the form submits.
        gatewayCards.forEach(function(card) {
            var gid = card.getAttribute('data-gateway');
            if ( ! gid ) return;

            var checkbox = card.querySelector('.wpuf-gateway-card__checkbox');
            if ( ! checkbox ) return;

            var firstSettingRow = formTable.querySelector('tr[data-gateway-id="' + gid + '"]');

            var toggleTr = document.createElement('tr');
            toggleTr.classList.add('wpuf-gateway-setting-row', 'wpuf-gateway-enable-row', 'wpuf-gateway-setting-hidden');
            toggleTr.setAttribute('data-gateway-id', gid);

            var nameEl = card.querySelector('.wpuf-gateway-card__label') || card.querySelector('.wpuf-gateway-card__name');
            var gatewayName = nameEl ? nameEl.textContent.trim() : gid;
            var toggleLabelText = wpufSprintf(wpufI18n.enableGateway || 'Enable %s', gatewayName);

            var toggleTh = document.createElement('th');
            toggleTh.scope = 'row';
            var toggleLabel = document.createElement('label');
            toggleLabel.textContent = toggleLabelText;
            toggleTh.appendChild(toggleLabel);

            var toggleTd = document.createElement('td');
            var switchLabel = document.createElement('label');
            switchLabel.className = 'wpuf-gateway-toggle';
            var switchInput = document.createElement('input');
            switchInput.type = 'checkbox';
            switchInput.className = 'wpuf-gateway-toggle__input';
            switchInput.setAttribute('data-gateway', gid);
            switchInput.setAttribute('aria-label', toggleLabelText);
            switchInput.checked = checkbox.checked;
            var switchSlider = document.createElement('span');
            switchSlider.className = 'wpuf-gateway-toggle__slider';
            switchLabel.appendChild(switchInput);
            switchLabel.appendChild(switchSlider);
            toggleTd.appendChild(switchLabel);

            toggleTr.appendChild(toggleTh);
            toggleTr.appendChild(toggleTd);

            if ( firstSettingRow && firstSettingRow.parentNode ) {
                firstSettingRow.parentNode.insertBefore(toggleTr, firstSettingRow);
            } else if ( formTbody ) {
                formTbody.appendChild(toggleTr);
            }
            afterRows.unshift(toggleTr);

            switchInput.addEventListener('change', function() {
                checkbox.checked = switchInput.checked;
                if ( switchInput.checked ) {
                    card.classList.add('wpuf-gateway-card--active');
                } else {
                    card.classList.remove('wpuf-gateway-card--active');
                }
                showGateway(gid);
            });
        });

        // Default panel: Bank Payment if available, else first card.
        var defaultCard = container.querySelector('.wpuf-gateway-card[data-gateway="bank"]') || gatewayCards[0];
        if ( defaultCard ) {
            showGateway(defaultCard.getAttribute('data-gateway'));
        }
    }
})();
