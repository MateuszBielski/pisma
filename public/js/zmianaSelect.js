$(document).ready(function() {
    $('.dlaSelect2').select2({
        
        tags: true,
        ajax: {
            url: function (params) {
                return $(this).attr('adresAjax');
            },
            dataType: 'json',
            data: function (params) {
                // var term = (params.term != null)? params.term : '';
                var query = {
                  fraza: params.term,
                //   type: 'public'
                }
          
                // Query parameters will be ?search=[term]&type=public
                return query;
            },
            
        }
    });
});