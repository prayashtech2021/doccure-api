$(document).ready(function() {
    var table = [];
    var cols = [
        { 'data': "id", "name": "appointments.id", "searchable": false },
        { 'data': "description", "name": "appointments.description","orderable": false },
        { 'data': "status",  "name":"appointments.status","searchable": false },
        { 'data': "email_id",  "name":"users.email_id","searchable": false },
        { 'data': "action", "searchable": false, "class": "action" },
    ];

    var category_table = $('.category_table').DataTable({
        "language": {
            "search": "",
            "lengthMenu": "_MENU_",
            "paginate": {
                "next": '<i class="icon ion-ios-arrow-forward"></i>',
                "previous": '<i class="icon ion-ios-arrow-back"></i>'
            },
        },
        pageLength: 10,
        processing: true,
        serverSide: true,
        ordering: true,
        searching: false,
        method: "GET",
        ajax: {
            url: get_list_app_url,
            data: function(d) {
                console.log(d);
            },
        },
        columns: cols,
    });
    $('body').on('click', '.delete_category', function() {
        var id = $(this).val();
        if ($('#category_id').val('') != '') {
            $('#category_id').val('');
            $('#category_id').val(id);
        } else {
            $('#category_id').val(id);
        }
    });

    $('body').on('click', '.confirm_delete', function() {
        var id = $('#category_id').val();
        if (id) {
            $.ajax({
                url: delete_category_url+'/'+id,
                method: 'GET',
                data: {
                    
                },
                success: function(d) {
                    if (d.success == true) {
                        new Noty({
                            type: 'success',
                            layout: 'topRight',
                            text: 'Category Deleted Sucessfully.',
                            timeout:3000
                        }).show();
                          $('#DeleteModal').modal("hide");
                          $('.category_table').DataTable().ajax.reload();

                    } else {
                        new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: d.errors,
                            timeout:3000
                        }).show();
                    }
                }
            })
        }
    });
});