jQuery(document).ready(function() {
    var $id_pismo_kierunek = $('#pismo_kierunek');

    // When sport gets selected ...
    $id_pismo_kierunek.change(function() {
    // ... retrieve the corresponding form.
    var $form = $(this).closest('form');
    // Simulate form data, but only include the selected sport value.
    var data = {};
    
    $pismo_kierunek_checked = $id_pismo_kierunek.find("input:checked");
    $wybranyKierunek = $pismo_kierunek_checked.val();
    // $('#pismo_oznaczenie').val($wybranyKierunek);

    // Submit data via AJAX to the form's action path.
    
    $.ajax({
        url : '/pismo/indexAjaxOznaczenie',
        // url : 'indexAjaxWgOpisuKontrahIstrony',
        // type: $form.attr('method'),
        type: 'GET',
        data : {
            kierunek : $wybranyKierunek ,
        },
        success: function(jsonResp) {
        // Replace current position field ...
            $('#pismo_oznaczenie').val(jsonResp.odp
                /*
            $('#pismo_oznaczenie').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#meetup_position')
                */
            );
            // Position field now displays the appropriate positions.
            
            }
        });
    
    });
});