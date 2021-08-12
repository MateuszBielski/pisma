function ZapytanieAjax()
{
    // var input_opisPisma = $('#input_find_pismo_wgOpisu');
    // var input_opisSprawy = $('#input_find_pismo_wgSprawy');
    // var input_nazwaKontrahenta = $('#input_find_pismo_wgKontrahenta');
    var input_opisPisma = $('#wyszukiwanie_dokumentow_dokument');
    var input_opisSprawy = $('#wyszukiwanie_dokumentow_sprawa');
    var input_nazwaKontrahenta = $('#wyszukiwanie_dokumentow_kontrahent');
    var token = $('#wyszukiwanie_dokumentow__token');
    data = {};
        // opisPisma: input_opisPisma.val(),
        // opisSprawy: input_opisSprawy.val(),
        // nazwaKontrahenta: input_nazwaKontrahenta.val(),
    data[input_opisPisma.attr('name')] = input_opisPisma.val();
    data[input_opisSprawy.attr('name')] = input_opisSprawy.val();
    data[input_nazwaKontrahenta.attr('name')] = input_nazwaKontrahenta.val();
    data[token.attr('name')] = token.val();
    $.ajax({
            url: "/pismo/indexAjaxWgOpisuKontrahIstrony",
            type: "POST",
            data: data,
            
            success: function (msg) {
               
                var kolumnyDoWstawienia = $(msg).find('#div_kolumny');
                var tokenDoWstawienia = $(msg).find("#wyszukiwanie_dokumentow__token").val();
                $('#div_kolumny').replaceWith(kolumnyDoWstawienia);
                $("#wyszukiwanie_dokumentow__token").val(tokenDoWstawienia);
                
            }
            ,error: function (err) {
                $("#div_kolumny").text(err.Message);
                // $("#div_lista_rej").text(err.Message);
            }
    });
}