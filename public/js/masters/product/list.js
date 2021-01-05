$(document).ready(function() {
    //$('#user').attr('class', 'active');
    var table = [];
    var cols = [
        { 'data': "id", "searchable": false},
        { 'data': "name", "name": "products.name","orderable": false },
        { 'data': "category.name", "name": "categories.name","orderable": false},
        { 'data': "price", "name": "products.price","orderable": false },
        { 'data': "status", "searchable": false },
        { 'data': "action", "searchable": false, "class": "action" },
    ];

    var product_table = $('.product_table').DataTable({
       'pageLength': 10,
        processing: true,
        serverSide: true,
        ordering: true,
        searching: false,
        method: "GET",
        ajax: {
            url: get_list_product_url,
            data: function(d) {
                console.log(d);
            },
        },
        columns: cols,
    });
    $('body').on('click', '.delete_product', function() {
        var id = $(this).val();
        if ($('#product_id').val('') != '') {
            $('#product_id').val('');
            $('#product_id').val(id);
        } else {
            $('#product_id').val(id);
        }
    });

 $('body').on('click', '.confirm_delete', function() {
        var id = $('#product_id').val();
        //alert(id);
        if (id) {
            $.ajax({
                url: delete_product_url+'/'+id,
                method: 'GET',
                data: {
                    
                },
                success: function(d) {
                    if (d.success == true) {
                        new Noty({
                            type: 'success',
                            layout: 'topRight',
                            text: 'Product Deleted Sucessfully.',
                            timeout:3000
                        }).show();
                          $('#DeleteModal').modal("hide");
                          $('.product_table').DataTable().ajax.reload();

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