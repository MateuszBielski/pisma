import jQuery from 'jquery';
import $ from 'jquery';
import autocomplete from 'jquery-ui/ui/widgets/autocomplete';

jQuery(document).ready(function () {
    var input_sciezka_do_folderu = $('#folder_sciezkaMoja');
    var div_lista_plikow = $('#div_lista_plikow');
    var adresFoldery = "/folder/nazwyFolderowDlaAutocomplete";
    var adresPliki = "/folder/odczytZawartosciAjax";
    var ostatniFolder = "ostatni";
    var szerokoscWyswietlanegoElementu = div_lista_plikow.width();
    var poprzedniaSciezka = '';
    var listaPlikowOdczytana = '';
    console.log();
    $(window).on('resize', function () {
        szerokoscWyswietlanegoElementu = div_lista_plikow.width();
    });

    input_sciezka_do_folderu.autocomplete({
        source:
            function (request, response) {
                $.ajax({
                    url: adresFoldery,
                    type: 'get',
                    dataType: "json",
                    data: {
                        sciezkaWpisana: request.term,
                        sciezkaOdcietaDoFolderuDotychczas: ostatniFolder,
                    },
                    success: function (data) {
                        // response($.ui.autocomplete.filter(data.foldery,request.term));//filtruje po stronie przeglądarki
                        response(data.foldery);
                        ostatniFolder = data.pelneFoldery;
                    }
                });

            },
        select: function (event, ui) {
            ostatniFolder += ui.item.label;
            input_sciezka_do_folderu.val(ostatniFolder);
            $('#div_sciezka_tu_jestem').text(ostatniFolder);
            div_lista_plikow.html(listaPlikowOdczytana);
            return false;
        },
        focus: function (event, ui) {
            var sciezkaDoFolderu = ostatniFolder + ui.item.label;
            if (poprzedniaSciezka != sciezkaDoFolderu) {
                input_sciezka_do_folderu.val(sciezkaDoFolderu);
                $.ajax({
                    url: adresPliki,
                    type: "GET",
                    data: {
                        fraza: sciezkaDoFolderu,
                        rozmiar: szerokoscWyswietlanegoElementu
                    },
                    success: function (msg) {
                        listaPlikowOdczytana = msg;
                        div_lista_plikow.html(listaPlikowOdczytana);
                    }
                    , error: function (err) {
                        div_lista_plikow.text(err.Message);
                    }
                });
                poprzedniaSciezka = sciezkaDoFolderu;
            }

            return false;
        },
    });


});
// https://jqueryui.com/autocomplete/#folding
