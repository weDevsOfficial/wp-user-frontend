jQuery(document).ready(function($) {

    var WPUF_Obj = {
        init: function () {
            $('#wpuf_new_post_form, #wpuf_edit_post_form').on('submit', this.checkSubmit);
            $('.wpuf-post-form').on('click', 'a.wpuf-del-ft-image', this.removeFeatImg);

            //editprofile password strength
            $('#pass1').val('').keyup( this.passStrength );
            $('#pass2').val('').keyup( this.passStrength );
            $('#pass-strength-result').show();

            //initialize the featured image uploader
            this.featImgUploader();
            this.ajaxCategory();
        },
        checkSubmit: function () {
            var form = $(this);

            form.find('.requiredField').each(function() {
                if( $(this).hasClass('invalid') ) {
                    $(this).removeClass('invalid');
                }
            });

            var hasError = false;

            $(this).find('.requiredField').each(function() {
                var el = $(this),
                labelText = el.prev('label').text();

                if(jQuery.trim(el.val()) == '') {
                    el.addClass('invalid');
                    hasError = true;
                } else if(el.hasClass('email')) {
                    var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                    if(!emailReg.test($.trim(el.val()))) {
                        el.addClass('invalid');
                        hasError = true;
                    }
                } else if(el.hasClass('cat')) {
                    if( el.val() == '-1' ) {
                        el.addClass('invalid');
                        hasError = true;
                    }
                }
            });

            if( ! hasError ) {
                $(this).find('input[type=submit]').attr({
                    'value': wpuf.postingMsg,
                    'disabled': true
                });

                return true;
            }

            return false;
        },
        passStrength: function () {
            var pass1 = $('#pass1').val(), user = $('#user_login1').val(), pass2 = $('#pass2').val(), strength;

            $('#pass-strength-result').removeClass('short bad good strong');
            if ( ! pass1 ) {
                $('#pass-strength-result').html( pwsL10n.empty );
                return;
            }

            strength = passwordStrength(pass1, user, pass2);

            switch ( strength ) {
                case 2:
                    $('#pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
                    break;
                case 3:
                    $('#pass-strength-result').addClass('good').html( pwsL10n['good'] );
                    break;
                case 4:
                    $('#pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
                    break;
                case 5:
                    $('#pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
                    break;
                default:
                    $('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
            }
        },
        featImgUploader: function() {
            if(typeof plupload === 'undefined') {
                return;
            }

            if(wpuf.featEnabled !== '1') {
                return;
            }

            var uploader = new plupload.Uploader(wpuf.plupload);

            uploader.bind('Init', function(up, params) {
                //$('#cpm-upload-filelist').html("<div>Current runtime: " + params.runtime + "</div>");
                });

            $('#wpuf-ft-upload-pickfiles').click(function(e) {
                uploader.start();
                e.preventDefault();
            });

            uploader.init();

            uploader.bind('FilesAdded', function(up, files) {
                $.each(files, function(i, file) {
                    $('#wpuf-ft-upload-filelist').append(
                        '<div id="' + file.id + '">' +
                        file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                        '</div>');
                });

                up.refresh(); // Reposition Flash/Silverlight
                uploader.start();
            });

            uploader.bind('UploadProgress', function(up, file) {
                $('#' + file.id + " b").html(file.percent + "%");
            });

            uploader.bind('Error', function(up, err) {
                $('#wpuf-ft-upload-filelist').append("<div>Error: " + err.code +
                    ", Message: " + err.message +
                    (err.file ? ", File: " + err.file.name : "") +
                    "</div>"
                    );

                up.refresh(); // Reposition Flash/Silverlight
            });

            uploader.bind('FileUploaded', function(up, file, response) {
                var resp = $.parseJSON(response.response);
                //$('#' + file.id + " b").html("100%");
                $('#' + file.id).remove();
                //console.log(resp);
                if( resp.success ) {
                    $('#wpuf-ft-upload-filelist').append(resp.html);
                    $('#wpuf-ft-upload-pickfiles').hide();
                }
            });
        },
        removeFeatImg: function(e) {
            e.preventDefault();

            if(confirm(wpuf.confirmMsg)) {
                var el = $(this),
                    data = {
                        'attach_id' : el.data('id'),
                        'nonce' : wpuf.nonce,
                        'action' : 'wpuf_feat_img_del'
                    }

                $.post(wpuf.ajaxurl, data, function(){
                    $('#wpuf-ft-upload-pickfiles').show();
                    el.parent().remove();
                });
            }
        },
        ajaxCategory: function () {
            var el = '#cat-ajax',
                wrap = '.category-wrap';

            $(el).parent().attr('level', 0);
            if ($( wrap + ' ' + el ).val() > 0) {
                WPUF_Obj.getChildCats( $(el), 'lvl', 1, wrap, 'category');
            }

            $(wrap).on('change', el, function(){
                currentLevel = parseInt( $(this).parent().attr('level') );
                WPUF_Obj.getChildCats( $(this), 'lvl', currentLevel+1, wrap, 'category');
            });
        },
        getChildCats: function (dropdown, result_div, level, wrap_div, taxonomy) {
            cat = $(dropdown).val();
            results_div = result_div + level;
            taxonomy = typeof taxonomy !== 'undefined' ? taxonomy : 'category';

            $.ajax({
                type: 'post',
                url: wpuf.ajaxurl,
                data: {
                    action: 'wpuf_get_child_cats',
                    catID: cat,
                    nonce: wpuf.nonce
                },
                beforeSend: function() {
                    $(dropdown).parent().parent().next('.loading').addClass('wpuf-loading');
                },
                complete: function() {
                    $(dropdown).parent().parent().next('.loading').removeClass('wpuf-loading');
                },
                success: function(html) {

                    $(dropdown).parent().nextAll().each(function(){
                        $(this).remove();
                    });

                    if(html != "") {
                        $(dropdown).parent().addClass('hasChild').parent().append('<div id="'+result_div+level+'" level="'+level+'"></div>');
                        dropdown.parent().parent().find('#'+results_div).html(html).slideDown('fast');
                    }
                }
            });
        }
    };

    //run the bootstrap
    WPUF_Obj.init();

});
