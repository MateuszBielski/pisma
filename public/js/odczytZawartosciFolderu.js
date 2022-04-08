// jQuery(document).ready(function () {
//     var input_sciezka_do_folderu = $('#folder_sciezkaMoja');
//     input_sciezka_do_folderu.on('input', function () {
//         var tekst = input_sciezka_do_folderu.val();
//         var adres = "/folder/odczytZawartosciAjax";
//         $.ajax({
//             url: adres,
//             type: "GET",
//             data: {
//                 fraza: tekst
//             },
//             success: function (msg) {
//                 input_sciezka_do_folderu.autocomplete({
//                         source: msg.dataAutocomplete
//                     });
//                 $('#div_lista_plikow').html(msg.dataAutocomplete);
//             }
//             , error: function (err) {
//                 $("#div_lista_plikow").text(err.Message);
//             }
//         });
//     });
// });


jQuery(document).ready(function () {
    var input_sciezka_do_folderu = $('#folder_sciezkaMoja');
    // input_sciezka_do_folderu.on('input', function () {
    //     var tekst = input_sciezka_do_folderu.val();
    var adres = "/folder/nazwyFolderowDlaAutocomplete";
    var ostatniFolder = "ostatni";

    // });

    // Single Select
    input_sciezka_do_folderu.autocomplete({
        source:
            function (request, response) {
                // Fetch data
                $.ajax({
                    url: adres,
                    type: 'get',
                    dataType: "json",
                    data: {
                        fraza: request.term
                    },
                    success: function (data) {
                        // response($.ui.autocomplete.filter(data.foldery,request.term));//filtruje po stronie przeglądarki
                        response(data.foldery);
                        ostatniFolder = data.pelneFoldery;
                    }
                });
                
            },
        select: function (event, ui) {
            // console.log(ui.item.label);
            input_sciezka_do_folderu.val(ostatniFolder+ui.item.label); // display the selected text
            // $('#selectuser_id').val(ui.item.value); // save selected id to input
            // input_sciezka_do_folderu.autocomplete();
            return false;
        },
        // focus: function (event, ui) {
        //     console.log("focus");
        //     input_sciezka_do_folderu.val(ui.item.foldery);
        //     // $( "#selectuser_id" ).val( ui.item.value );

        //     return false;
        // },
    });


});
// https://jqueryui.com/autocomplete/#folding
// source: function( request, response ) {
//     var matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
//     response( $.grep( names, function( value ) {
//       value = value.label || value.value || value;
//       return matcher.test( value ) || matcher.test( normalize( value ) );
//     }) );
//   }