$(document).ready(function() {
    $('#name').focus();
    var form_id = '#form';
    var v = jQuery(form_id).validate({
        ignore: "",
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 191,
            },
        },
        submitHandler: function(form) {
            var valid = true;
            let formData = new FormData($(form_id)[0]);
            $('#submit').button('loading');
            $.ajax({
                    url: save_category_url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                })
                .done(function(res) {
                    console.log(res.success);
                    if (!res.success) {
                        $('#submit').button('reset');
                        var errors = '';
                        for (var i in res.errors) {
                            errors += '<li>' + res.errors[i] + '</li>';
                        }
                        console.log(errors)
                        new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: errors,
                            timeout: 3000
                        }).show();
                    } else {
                        console.log(res);
                        window.location = list_category_url;
                    }
                })
                .fail(function(xhr) {
                    $('#submit').button('reset');
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'Something went wrong at server.'
                    }).show();
                })
        }
    });
});