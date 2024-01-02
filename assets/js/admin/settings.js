(function () {
    document.addEventListener('DOMContentLoaded',function () {
        var tabs    = document.querySelector('.wpuf-settings-wrap').querySelectorAll('h2 a');
        var content = document.querySelectorAll('.wpuf-settings-wrap .metabox-holder th');
        var close   = document.querySelector('#wpuf-search-section span');

        var search_input = document.querySelector('#wpuf-settings-search');

        search_input.addEventListener('keyup', function (e) {
            var search_value = e.target.value.toLowerCase();
            var value_tab  = [];

            if ( search_value.length ) {
                close.style.display = 'flex';
                content.forEach(function (row, index) {

                    var content_id = row.closest('div').getAttribute('id');
                    var tab_id     = content_id + '-tab';
                    var found_value = row.innerText.toLowerCase().includes( search_value );

                    if ( found_value ){
                        row.closest('tr').style.display = 'table-row';
                    }else {
                        row.closest('tr').style.display = 'none';
                    }

                    if ( 'wpuf_mails' === content_id ){
                        row.closest('tbody').querySelectorAll('tr').forEach(function (tr) {
                            tr.style.display = '';
                        });
                    }

                    if ( found_value === true && ! value_tab.includes( tab_id ) ) {
                        value_tab.push(tab_id);
                    }
                })

                if ( value_tab.length ) {
                    document.getElementById(value_tab[0]).click();
                }

                tabs.forEach(function (tab) {
                    var tab_id = tab.getAttribute('id');
                    if ( ! value_tab.includes( tab_id ) ){
                        document.getElementById(tab_id).style.display = 'none';
                    }else {
                        document.getElementById(tab_id).style.display = 'block';
                    }
                });

            }else {
                wpuf_search_reset();
            }
        })

        close.addEventListener('click',function (event) {
            wpuf_search_reset();
            search_input.value = '';
            close.style.display = 'none';
        })

        function wpuf_search_reset() {
            content.forEach(function (row, index) {
                var content_id = row.closest('div').getAttribute('id');
                var tab_id     = content_id + '-tab';
                document.getElementById(content_id).style.display = '';
                document.getElementById(tab_id).style.display = '';
                document.getElementById('wpuf_general-tab').click();
            })
            document.querySelector('.wpuf-settings-wrap .metabox-holder').querySelectorAll('tr').forEach(function (row) {
                row.style.display = '';
            });

        }
    });
})();
