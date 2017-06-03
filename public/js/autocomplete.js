$(document).ready(function() {
    $('input[name="q"]').autocomplete(
        {
            source: function(request, response) {
                $.ajax({
                url : '/suggest',
                dataType : 'json',
                data : {
                    term : request.term
                },

                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label : item.label,
                            value : item.label
                        };
                    }));
                }
            });
        },

        minLength: 3,

        select : function(event, ui) {
            $('form[action="/search"] input[type="submit"]').submit();
        }
    }).keydown(function(e) {
        if (e.keyCode === 13) {
            $('form[action="/search"]').trigger('submit');
        }
    })
});