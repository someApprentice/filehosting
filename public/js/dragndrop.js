(function(){
    var dropzone = document.querySelector('.dropzone');
    var progressbar = document.querySelector('progress');
    var form = document.querySelector('form[action="/"]');
    var csrf = document.querySelectorAll('form[action="/"] input[type="hidden"]');

    form.style.display = 'none';
    dropzone.style.display = 'block';

    function upload(files) {
        var formdata = new FormData();
        var xhr = new XMLHttpRequest();

        formdata.append('file', files[0]);

        for(var i = 0; i < csrf.length; i++) {
            formdata.append(csrf[i].name, csrf[i].value);
        }

        xhr.upload.onprogress = function(e) {
            dropzone.style.display = 'none';
            progressbar.style.display = 'inline';

            var percent = Math.round(e.loaded / e.total * 100);

            progressbar.value = percent;
        };

        xhr.onload = function() {
            window.location.replace('/');
        };

        xhr.open('post', '/');
        xhr.send(formdata);
    }

    dropzone.ondrop = function(e) {
        e.preventDefault();

        var files = e.dataTransfer.files;

        upload(files);

        this.style.borderColor = 'grey';
        this.style.boxShadow = 'inset 0 0 5px grey';
    }

    dropzone.ondragover = function() {
        this.style.borderColor = 'black';

        return false;
    }

    dropzone.ondragleave = function() {
        this.style.borderColor = 'grey';

        return false;
    }

    dropzone.onclick = function() {
        var input = document.querySelector('input[name="file"]');

        input.click();
        
        input.onchange = function() {
            var files = input.files;

            upload(files);
        }
    }
}());