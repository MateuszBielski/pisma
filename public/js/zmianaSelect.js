$(document).ready(function() {
    $('.dlaSelect2').select2({
        
        tags: true,
        ajax: {
            url: function (params) {
                return $(this).attr('adresAjax');
            },
            dataType: 'json',
            data: function (params) {
                var query = {
                  fraza: params.term,
                //   type: 'public'
                }
          
                // Query parameters will be ?search=[term]&type=public
                return query;
            },
            processResults: function (data) {
                // Transforms the top-level key of the response object from 'items' to 'results'
                return {
                  results: data.items
                };
            }
        }
    });
});