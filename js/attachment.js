jQuery(document).ready(function($) {

    var WPUF_Attachment = {
        init: function () {
            window.wpufFileCount = typeof window.wpufFileCount == 'undefined' ? 0 : window.wpufFileCount;
            this.maxFiles = parseInt(wpuf_attachment.number);

            $('#wpuf-attachment-upload-filelist').on('click', 'a.track-delete', this.removeTrack);
            $('#wpuf-attachment-upload-filelist ul.wpuf-attachment-list').sortable({
                cursor: 'crosshair',
                handle: '.handle'
            });

            this.attachUploader();
            this.hideUploadBtn();
        },
        hideUploadBtn: function () {

            if(WPUF_Attachment.maxFiles !== 0 && window.wpufFileCount >= WPUF_Attachment.maxFiles) {
                $('#wpuf-attachment-upload-pickfiles').hide();
            }
        },
        attachUploader: function() {
            if(typeof plupload === 'undefined') {
                return;
            }

            var audio_uploader = new plupload.Uploader(wpuf_attachment.plupload);

            $('#wpuf-attachment-upload-pickfiles').click(function(e) {
                audio_uploader.start();
                e.preventDefault();
            });

            audio_uploader.init();

            audio_uploader.bind('FilesAdded', function(up, files) {
                $.each(files, function(i, file) {
                    $('#wpuf-attachment-upload-filelist').append(
                        '<div id="' + file.id + '">' +
                        file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                        '</div>');
                });

                up.refresh(); // Reposition Flash/Silverlight
                audio_uploader.start();
            });

            audio_uploader.bind('UploadProgress', function(up, file) {
                $('#' + file.id + " b").html(file.percent + "%");
            });

            audio_uploader.bind('Error', function(up, err) {
                $('#wpuf-attachment-upload-filelist').append("<div>Error: " + err.code +
                    ", Message: " + err.message +
                    (err.file ? ", File: " + err.file.name : "") +
                    "</div>"
                    );

                up.refresh(); // Reposition Flash/Silverlight
            });

            audio_uploader.bind('FileUploaded', function(up, file, response) {
                var resp = $.parseJSON(response.response);
                $('#' + file.id).remove();
                //console.log(resp);
                if( resp.success ) {
                    window.wpufFileCount += 1;
                    $('#wpuf-attachment-upload-filelist ul').append(resp.html);

                    WPUF_Attachment.hideUploadBtn();
                }
            });
        },
        removeTrack: function(e) {
            e.preventDefault();

            if(confirm(wpuf.confirmMsg)) {
                var el = $(this),
                data = {
                    'attach_id' : el.data('attach_id'),
                    'nonce' : wpuf_attachment.nonce,
                    'action' : 'wpuf_attach_del'
                };

                $.post(wpuf.ajaxurl, data, function(){
                    el.parent().parent().remove();

                    window.wpufFileCount -= 1;
                    if(WPUF_Attachment.maxFiles !== 0 && window.wpufFileCount < WPUF_Attachment.maxFiles ) {
                        $('#wpuf-attachment-upload-pickfiles').show();
                    }
                });
            }
        }
    };

    //run the bootstrap
    WPUF_Attachment.init();

});