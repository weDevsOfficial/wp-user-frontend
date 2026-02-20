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
                        icon: '<span style="box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;border:1.5px solid #0079C1;background:transparent;color:#0079C1;margin-right:10px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" style="width:14px;height:14px;fill:currentColor;"><path d="M111.4 295.9l-35.4 223.7c-.5 2.8 1.6 5.4 4.5 5.4H155c2.3 0 4.2-1.7 4.5-4l8.3-51.5c4-23.7 24-41.9 48-41.9h21.1c54.7 0 97.4-21.7 109.8-76 4-17.7 2.1-34.9-4.3-50.1-13-29.6-43.1-42-83.3-42h-85.3c-2.8 0-5.1 2.3-5.1 5.1v31.3zM342.3 84.1c-14.7-21.5-44.5-31.4-86.4-31.4H114.6c-4.1 0-7.6 3.1-8.2 7.2l-32.9 203.4c-.6 3.5 1.9 6.8 5.6 6.8h72.2c4.7 0 8.7-3.4 9.4-8l8.3-51.5c4-23.7 24-41.9 48-41.9h28.5c54.7 0 97.4-21.7 109.8-76 5.9-25.7 3.9-48.4-13-70z"/></svg></span>',
                        rows: [],
                        existingCheckbox: null
                    },
                    bank: {
                        title: 'Bank Transfer',
                        icon: '<span style="box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;border:1.5px solid #10B981;background:transparent;color:#10B981;margin-right:10px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:14px;height:14px;fill:currentColor;"><path d="M243.4 2.6l-224 96c-14 6-21.8 21-18.7 35.8S16.8 160 32 160v8c0 13.3 10.7 24 24 24H456c13.3 0 24-10.7 24-24v-8c15.2 0 28.3-10.7 31.3-25.6s-4.8-29.9-18.7-35.8l-224-96c-8-3.4-17.2-3.4-25.2 0zM128 224H64V420.3c-.6 .3-1.2 .7-1.8 1.1l-48 32c-11.7 7.8-17 22.4-12.9 35.9S17.9 512 32 512H480c14.1 0 26.5-9.2 30.6-22.7s-1.1-28.1-12.9-35.9l-48-32c-.6-.4-1.2-.7-1.8-1.1V224H384V416H344V224H280V416H240V224H176V416H136V224H128zM384 128V96h48v32H384zM136 96v32H80V96h56zM280 96v32H240V96h40z"/></svg></span>',
                        rows: [],
                        existingCheckbox: null
                    },
                    stripe: {
                        title: 'Credit Card',
                        icon: '<span class="wpuf-svg-icon-bg" style="box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;border:1.5px solid #5469d4;background:transparent;color:#5469d4;margin-right:10px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" style="width:14px;height:14px;fill:currentColor;"><path d="M512 80c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H512zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM208 256h64c8.8 0 16-7.2 16-16s-7.2-16-16-16H208c-8.8 0-16 7.2-16 16s7.2 16 16 16z"/></svg></span>',
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

                if (allowGatewaysTr) {
                    var inputs = allowGatewaysTr.querySelectorAll('input[type="checkbox"]');
                    inputs.forEach(function (input) {
                        var k = input.value;
                        if (groups[k]) {
                            var wrapperLabel = input.closest('label');
                            groups[k].existingCheckbox = wrapperLabel; // Keep a reference to the label containing the checkbox

                            // Let's remove any <br> right after the label to keep HTML clean later
                            if (wrapperLabel && wrapperLabel.nextSibling && wrapperLabel.nextSibling.nodeName === 'BR') {
                                wrapperLabel.nextSibling.remove();
                            }
                        }
                    });

                    allowGatewaysTr.style.display = 'none';
                }

                trs.forEach(function (tr) {
                    if (tr === allowGatewaysTr) return;

                    var th = tr.querySelector('th');
                    var label = th ? th.querySelector('label') : null;
                    var forAttr = label ? label.getAttribute('for') || '' : '';

                    var isPaypal = forAttr.includes('paypal');
                    var isBank = forAttr.includes('bank');
                    var isStripe = forAttr.includes('stripe');

                    var contentMatches = tr.innerHTML;

                    if (!isPaypal && !isBank && !isStripe) {
                        if (contentMatches.includes('wpuf_payment[paypal') || contentMatches.includes('wpuf-paypal-webhook') || contentMatches.includes('[gate_instruct_paypal]')) {
                            isPaypal = true;
                        } else if (contentMatches.includes('wpuf_payment[bank') || contentMatches.includes('[gate_instruct_bank]')) {
                            isBank = true;
                        } else if (contentMatches.includes('wpuf_payment[stripe') || contentMatches.includes('[gate_instruct_stripe]')) {
                            isStripe = true;
                        }
                    }

                    if (isPaypal) groups.paypal.rows.push(tr);
                    else if (isBank) groups.bank.rows.push(tr);
                    else if (isStripe) groups.stripe.rows.push(tr);
                });

                var style = document.createElement('style');
                style.innerHTML = `
                    .wpuf-accordion-trigger {
                        background: #f1f1f1;
                        color: #333;
                        cursor: pointer;
                        padding: 15px;
                        width: calc(100% - 25px);
                        border: 1px solid #ccc;
                        box-sizing: border-box;
                        text-align: left;
                        outline: none;
                        font-size: 15px;
                        transition: 0.4s;
                        border-radius: 4px;
                        display: flex;
                        align-items: center;
                        font-weight: 600;
                        margin-top: 20px;
                        margin-bottom: 5px;
                    }
                    .wpuf-accordion-trigger.active, .wpuf-accordion-trigger:hover {
                        background: #ddd;
                    }
                    .wpuf-accordion-trigger:after {
                        content: '';
                        display: block;
                        width: 8px;
                        height: 8px;
                        border-right: 2px solid #777;
                        border-bottom: 2px solid #777;
                        transform: translateY(-2px) rotate(45deg);
                        float: right;
                        margin-left: auto;
                        transition: transform 0.3s ease;
                    }
                    .wpuf-accordion-trigger.active:after {
                        transform: translateY(2px) rotate(225deg);
                    }
                    .wpuf-accordion-row {
                        display: none;
                    }
                    .wpuf-accordion-row.active {
                        display: table-row;
                    }
                    .wpuf-accordion-trigger.is-disabled {
                       background: #f9f9f9;
                       color: #888;
                    }
                    .wpuf-accordion-trigger.is-disabled .wpuf-svg-icon-bg {
                       border-color: #ccc !important;
                       color: #ccc !important;
                    }
                    .wpuf-accordion-trigger.is-disabled:hover {
                       background: #f9f9f9;
                    }
                    .wpuf-accordion-trigger.is-disabled:after {
                        display: none;
                    }
                `;
                document.head.appendChild(style);

                var theTbody = table.querySelector('tbody');

                var fallbackProIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" style="fill:#F59E0B; width:20px; height:20px;"><path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"/></svg>';

                // Resolve the existing PRO badge img — search ALL tabs including hidden ones
                var existingProIconSrc = '';
                var proIconImg = document.querySelector('.pro-icon img');
                if (!proIconImg) {
                    // Also try settings fields that may be hidden
                    proIconImg = document.querySelector('span.pro-icon img');
                }
                if (proIconImg) {
                    existingProIconSrc = '<img src="' + proIconImg.getAttribute('src') + '" alt="PRO" style="vertical-align:middle;">';
                } else {
                    // Derive path from any known plugin img on the page, or use absolute path pattern
                    var anyImg = document.querySelector('img[src*="wp-user-frontend"][src*="assets"]');
                    if (anyImg) {
                        var baseUrl = anyImg.getAttribute('src').split('/assets/')[0];
                        existingProIconSrc = '<img src="' + baseUrl + '/assets/images/pro-badge.svg" alt="PRO" style="vertical-align:middle;">';
                    } else {
                        existingProIconSrc = fallbackProIconSvg;
                    }
                }

                Object.keys(groups).forEach(function (key) {
                    var group = groups[key];
                    var isProFeatureDisabled = (key === 'stripe' && !group.existingCheckbox);

                    if (group.existingCheckbox) {
                        var checkboxTr = document.createElement('tr');
                        var checkboxTh = document.createElement('th');
                        checkboxTh.scope = 'row';

                        if (key === 'stripe') {
                            // Build label with PRO badge — same pattern as native PRO-gated settings
                            var enableLbl = document.createElement('label');
                            enableLbl.appendChild(document.createTextNode('Enable Gateway '));
                            var proBadgeSpan = document.createElement('span');
                            proBadgeSpan.className = 'pro-icon';
                            proBadgeSpan.innerHTML = existingProIconSrc;
                            enableLbl.appendChild(proBadgeSpan);
                            checkboxTh.appendChild(enableLbl);
                        } else {
                            checkboxTh.innerHTML = 'Enable Gateway';
                        }

                        var checkboxTd = document.createElement('td');

                        var newFieldset = document.createElement('fieldset');
                        newFieldset.appendChild(group.existingCheckbox);
                        checkboxTd.appendChild(newFieldset);

                        checkboxTr.appendChild(checkboxTh);
                        checkboxTr.appendChild(checkboxTd);

                        // Push into the array at the beginning
                        group.rows.unshift(checkboxTr);
                    } else if (isProFeatureDisabled) {
                        var dummyTr = document.createElement('tr');
                        var dummyTh = document.createElement('th');
                        dummyTh.scope = 'row';

                        // "Enable Gateway" label + PRO icon badge — same pattern used throughout the plugin:
                        // <label>Enable Gateway <span class="pro-icon"><img src="...pro-badge.svg"></span></label>
                        var enableLabel = document.createElement('label');
                        enableLabel.appendChild(document.createTextNode('Enable Gateway '));
                        var proIconSpan = document.createElement('span');
                        proIconSpan.className = 'pro-icon';
                        proIconSpan.innerHTML = existingProIconSrc;
                        enableLabel.appendChild(proIconSpan);
                        dummyTh.appendChild(enableLabel);

                        var dummyTd = document.createElement('td');
                        dummyTd.innerHTML = '<p style="color:#888; margin:0; font-style:italic;">Available with <a href="https://wedevs.com/wp-user-frontend-pro/" target="_blank" style="color:#0073aa; font-weight:600;">WPUF Pro</a>.</p>';

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
                    titleSpan.innerHTML = group.icon + group.title;
                    titleSpan.style.display = 'flex';
                    titleSpan.style.alignItems = 'center';

                    if (isProFeatureDisabled) {
                        triggerBtn.classList.add('is-disabled');
                        var badgeWrapper = document.createElement('div');
                        badgeWrapper.innerHTML = '<span class="pro-icon" title="Pro Feature" style="margin-left: 8px; display: inline-flex; align-items: center;">' + existingProIconSrc + '</span>';
                        badgeWrapper.style.display = 'inline-flex';
                        badgeWrapper.style.alignItems = 'center';
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
})();
