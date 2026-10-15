;(function($) {

    /**
     * Upload handler helper
     *
     * @param string {browse_button} browse_button ID of the pickfile
     * @param string {container} container ID of the wrapper
     * @param int {max} maximum number of file uplaods
     * @param string {type}
     */
    window.WPUF_Uploader = function (browse_button, container, max, type, allowed_type, max_file_size) {
        this.removed_files = [];
        this.container = container;
        this.browse_button = browse_button;
        this.max = max || 1;
        this.count = $('#' + container).find('.wpuf-attachment-list > li').length; //count how many items are there
        this.perFileCount = 0; //file count on each upload
        this.UploadedFiles = 0; //file count on each upload

        //if no element found on the page, bail out
        if( !$('#'+browse_button).length ) {
            return;
        }

        // enable drag option for ordering
        $( "ul.wpuf-attachment-list" ).sortable({
            placeholder: "highlight"
        });
        $( "ul.wpuf-attachment-list" ).disableSelection();

        //instantiate the uploader
        this.uploader = new plupload.Uploader({
            runtimes: 'html5,html4',
            browse_button: browse_button,
            container: container,
            multipart: true,
            // REST POST wpuf/v1/uploads (Platform\REST\Controllers\UploadsController).
            multipart_params: {
                form_id: $( '#' + browse_button ).data('form_id'),
                type: type,
                wpuf_nonce: wpuf_upload.upload_nonce
            },
            headers: { 'X-WP-Nonce': wpuf_upload.rest.nonce },
            urlstream_upload: true,
            file_data_name: 'wpuf_file',
            max_file_size: max_file_size + 'kb',
            url: wpuf_upload.rest.root + 'uploads',
            flash_swf_url: wpuf_upload.flash_swf_url,
            filters: [{
                title: 'Allowed Files',
                extensions: allowed_type
            }]
        });

        //attach event handlers
        this.uploader.bind('Init', $.proxy(this, 'init'));
        this.uploader.bind('FilesAdded', $.proxy(this, 'added'));
        this.uploader.bind('QueueChanged', $.proxy(this, 'upload'));
        this.uploader.bind('UploadProgress', $.proxy(this, 'progress'));
        this.uploader.bind('Error', $.proxy(this, 'error'));
        this.uploader.bind('FileUploaded', $.proxy(this, 'uploaded'));

        this.uploader.init();

        $('#' + container).on('click', 'a.attachment-delete', $.proxy(this.removeAttachment, this));

        return this.uploader;
    };

    WPUF_Uploader.prototype = {

        init: function (up, params) {
            this.showHide();
            $('#' + this.container).prepend('<div class="wpuf-file-warning"></div>');
        },

        showHide: function () {
            // The upload prompt is either the legacy `.file-selector` button or the
            // redesigned `.wpuf-file-upload-zone`. Toggle whichever is present so the
            // single-vs-multi rule holds for both markups. Use setProperty(..., 'important')
            // because the template CSS forces `display: flex !important`, which a plain
            // .hide()/.show() (inline display) cannot override.
            var $selector = $('#' + this.container).find('.file-selector, .wpuf-file-upload-zone');

            if ( this.count >= this.max ) {
                $('#' + this.container + ' .wpuf-file-warning').html( wpuf_upload.warning );
                $selector.each(function () { this.style.setProperty('display', 'none', 'important'); });

                return;
            }

            $('#' + this.container + ' .wpuf-file-warning').html( '' );
            $selector.each(function () { this.style.removeProperty('display'); });
        },

        added: function (up, files) {
            var $container = $('#' + this.container).find('.wpuf-attachment-upload-filelist');

            this.showHide();

            $.each(files, function(i, file) {
                $(".wpuf-submit-button").attr("disabled", "disabled");

                $container.append(
                    '<div class="upload-item" id="' + file.id + '"><div class="progress progress-striped active"><div class="bar"></div></div><div class="filename original">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div></div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
            up.start();
        },

        upload: function (uploader) {
            this.count = uploader.files.length - this.removed_files.length ;
            this.showHide();
        },

        progress: function (up, file) {
            var item = $('#' + file.id);

            $('.bar', item).css({ width: file.percent + '%' });
            $('.percent', item).html( file.percent + '%' );
        },

        error: function (up, error) {
            $('#' + this.container).find('#' + error.file.id).remove();

            var msg = '';
            switch (error.code) {
                case -600:
                    msg = wpuf_upload.plupload.size_error;
                    break;

                case -601:
                    msg = wpuf_upload.plupload.type_error;
                    break;

                default:
                    msg = 'Error #' + error.code + ': ' + error.message;
                    break;
            }

            alert(msg);

            this.count -= 1;
            this.showHide();
            this.uploader.refresh();
        },

        uploaded: function (up, file, response) {
            // REST answers JSON: the attachment (`html`, `attach_id`, ...) or an error (`message`).
            var res = null;

            try {
                res = JSON.parse(response.response);
            } catch (e) {
                res = null;
            }

            var self = this;

            $('#' + file.id + " b").html("100%");
            $('#' + file.id).remove();

            if ( res && res.html ) {
                this.perFileCount++;
                this.UploadedFiles++;
                var $container = $('#' + this.container).find('.wpuf-attachment-list');
                $container.append(res.html);
                if ( this.perFileCount > this.max ) {
                    var attach_id = $('.wpuf-image-wrap:last a.attachment-delete',$container).data('attach-id');
                    self.removeExtraAttachment(attach_id);
                    $('.wpuf-image-wrap',$container).last().remove();
                    this.perFileCount--;
                }
            } else {
                alert( String( ( res && res.message ) || '' ).replace( /(<([^>]+)>)/ig, '' ) );

                up.files.pop();
                this.count -= 1;
                this.showHide();

            }

            var uploaded        = this.UploadedFiles,
                FileProgress    = up.files.length;

            this.count = $('#' + this.container).find('.wpuf-attachment-list > li').length;

            // Hide/show the upload prompt for both legacy and redesigned markups.
            this.showHide();

            if ( FileProgress === uploaded ) {
                if ( typeof grecaptcha !== 'undefined' && $('.wpuf-form-add #g-recaptcha-response').length ) {
                    if ( !grecaptcha.getResponse().length ){
                        return;
                    }
                }

                $(".wpuf-submit-button").removeAttr("disabled");
            }
        },

        removeAttachment: function(e) {
            e.preventDefault();

            var self = this,
            el = $(e.currentTarget);

            window.wpufDialog.confirm( {
                text: wpuf_upload.confirmMsg,
                tone: 'danger',
                confirmText: wpuf_upload.delete_it,
                cancelText: wpuf_upload.cancel_it
            } ).then( function ( confirmed ) {
                if ( ! confirmed ) {
                    return;
                }
                var data = {
                    'attach_id' : el.data('attach-id')
                };
                self.removed_files.push(data);
                jQuery('#del_attach').val(el.data('attach-id'));
                window.wpufRest.request( wpuf_upload.rest, 'uploads/' + parseInt( data.attach_id, 10 ), { method: 'DELETE', data: { wpuf_nonce: wpuf_upload.nonce } } ).then( function() {
                    self.perFileCount--;
                    el.parent().parent().remove();

                    self.count -= 1;
                    self.showHide();
                    self.uploader.refresh();
                } );
            });
        },

        removeExtraAttachment : function( attach_id ) {
            var self = this;

            var data = {
                'attach_id' : attach_id
            };
            this.removed_files.push(data);
            window.wpufRest.request( wpuf_upload.rest, 'uploads/' + parseInt( attach_id, 10 ), { method: 'DELETE', data: { wpuf_nonce: wpuf_upload.nonce } } ).then( function() {
                self.count -= 1;
                self.showHide();
                self.uploader.refresh();
            } );
        }

    };
})(jQuery);
