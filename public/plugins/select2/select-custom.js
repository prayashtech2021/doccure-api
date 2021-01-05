function dtInitComplete(dt,common_filter,filter) {
   
    $('.select-filter').each(function(){
        var parent = $(this).attr('data-parent');
        var column_name = $(this).attr('data-column');
        var default_value = $(this).attr('data-default');
        var select = $('<select class="form-control filter-input"><option value="">'+default_value+'</option></select>')
            .on( 'change', function () {
                var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                );
                dt.api().column('.'+column_name).search( val ? '^'+val+'$' : '', true, false ).draw();
            });
        select.appendTo( $('.'+filter+' .'+parent).show().empty() )

        dt.api().column('.'+column_name).data().unique().sort().each( function ( d, j ) {
            select.append( '<option value="'+d+'">'+d+'</option>' )
        } );
    });

}

$(function(){
    $(document).ready( function () {
        $('select').not('.not_style').select2();
    });
});

