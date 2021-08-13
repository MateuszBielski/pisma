function ZapytanieAjax()
{
    // var input_opisPisma = $('#input_find_pismo_wgOpisu');
    // var input_opisSprawy = $('#input_find_pismo_wgSprawy');
    // var input_nazwaKontrahenta = $('#input_find_pismo_wgKontrahenta');
    var input_opisPisma = $('#wyszukiwanie_dokumentow_dokument');
    var input_opisSprawy = $('#wyszukiwanie_dokumentow_sprawa');
    var input_nazwaKontrahenta = $('#wyszukiwanie_dokumentow_kontrahent');
    var token = $('#wyszukiwanie_dokumentow__token');
    
    var poczDay = $('#wyszukiwanie_dokumentow_poczatekData_day');
    var poczMon = $('#wyszukiwanie_dokumentow_poczatekData_month');
    var poczYear = $('#wyszukiwanie_dokumentow_poczatekData_year');
    var konDay = $('#wyszukiwanie_dokumentow_koniecData_day');
    var konMon = $('#wyszukiwanie_dokumentow_koniecData_month');
    var konYear = $('#wyszukiwanie_dokumentow_koniecData_year');
    data = {};
        // opisPisma: input_opisPisma.val(),
        // opisSprawy: input_opisSprawy.val(),
        // nazwaKontrahenta: input_nazwaKontrahenta.val(),
    data[input_opisPisma.attr('name')] = input_opisPisma.val();
    data[input_opisSprawy.attr('name')] = input_opisSprawy.val();
    data[input_nazwaKontrahenta.attr('name')] = input_nazwaKontrahenta.val();
    data[token.attr('name')] = token.val();
    var uwzglednijDaty = $('#wyszukiwanie_dokumentow_czyDatyDoWyszukiwania').is(":checked");
    // console.log(uwzglednijDaty);
    if(uwzglednijDaty){
        // console.log(uwzglednijDaty);
        data[poczDay.attr('name')] = poczDay.val();
        data[poczMon.attr('name')] = poczMon.val();
        data[poczYear.attr('name')] = poczYear.val();
        data[konDay.attr('name')] = konDay.val();
        data[konMon.attr('name')] = konMon.val();
        data[konYear.attr('name')] = konYear.val();
    }
    $.ajax({
            url: "/pismo/indexAjaxWgOpisuKontrahIsprawy",
            type: "POST",
            data: data,
            
            success: function (msg) {
               
                var kolumnyDoWstawienia = $(msg).find('#div_kolumny');
                var tokenDoWstawienia = $(msg).find("#wyszukiwanie_dokumentow__token").val();
                
                $('#div_kolumny').replaceWith(kolumnyDoWstawienia);
                $("#wyszukiwanie_dokumentow__token").val(tokenDoWstawienia);
                $('#wyszukiwanie_dokumentow_poczatekData').replaceWith($(msg).find('#wyszukiwanie_dokumentow_poczatekData'));
                $('#wyszukiwanie_dokumentow_koniecData').replaceWith($(msg).find('#wyszukiwanie_dokumentow_koniecData'));
                // $('#div_kolumny').html(msg);
                var daty = $("#wyszukiwanie_dokumentow_poczatekData select, #wyszukiwanie_dokumentow_koniecData select");
                daty.on('change',ZapytanieAjax);
            }
            ,error: function (err) {
                $("#div_kolumny").text(err.Message);
                // $("#div_lista_rej").text(err.Message);
            }
    });
}
