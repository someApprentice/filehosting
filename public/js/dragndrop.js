$(document).ready(function() {
    $('form[action="/"]').css('display', 'none');
    $('.dropzone').css('display', 'block');

    function upload(files) {
        var formdata = new FormData();

        formdata.append('file', files[0]);

        $.each($('form[action="/"] input[type="hidden"]'), function(i, csrf) {
            formdata.append($(csrf).attr('name'), $(csrf).attr('value'));
        });

        $.ajax({
            url: '/',
            method: 'post',
            data: formdata,
            contentType: false,
            processData: false,

            xhr: function() {
                var xhr = new window.XMLHttpRequest();

                xhr.upload.onprogress = function(e) {
                    $('.dropzone').css('display', 'none');
                    $('progress').css('display', 'inline');

                    var percent = Math.round(e.loaded / e.total * 100);

                    $('progress').attr('value', percent);
                };

                return xhr;
            },

            success: function(data) {
                window.location.replace('/');
            }
        });
    }

    $('.dropzone').on('drop', function(e) {
        e.preventDefault();

        var files = e.originalEvent.dataTransfer.files;

        upload(files);

        $(this).css({'border-color':'grey', 'box-shadow':'inset 0 0 5px grey'});
    });

    $('.dropzone').on('dragover', function() {
        $(this).css('border-color', 'black');

        return false;
    });

    $('.dropzone').on('dragleave', function() {
        $(this).css('border-color', 'grey');

        return false;
    });

    $('.dropzone').on('click', function() {
        $('input[name="file"]').click();
        
        $('input[name="file"]').on('change', function() {
            upload($('input[name="file"]').prop('files'));
        });
    });
});