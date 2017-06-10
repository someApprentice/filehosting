$(document).ready(function() {
    $.each($('.comment form'), function(i, form) {
        $(form).css('display', 'none');
    });

    $('.comment .reply').on('click', function(e) {
        e.preventDefault();

        if ($(this).next('form').css('display') == 'none') {
            $(this).next('form').css('display', 'block');
        } else {
            $(this).next('form').css('display', 'none');
        }
    });
});