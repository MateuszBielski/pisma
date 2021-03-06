function ZapytanieAjax()
{
    var input_opisPisma = $('#input_find_pismo_wgOpisu');
    var input_opisSprawy = $('#input_find_pismo_wgSprawy');
    var input_nazwaKontrahenta = $('#input_find_pismo_wgKontrahenta');
    // console.log('ZapytanieAjax'+input_opisPisma.val());
    $.ajax({
            url: "/pismo/indexAjaxWgOpisuKontrahIstrony",
            type: "GET",
            data: {
                opisPisma: input_opisPisma.val(),
                opisSprawy: input_opisSprawy.val(),
                nazwaKontrahenta: input_nazwaKontrahenta.val(),
            },
            success: function (msg) {
                $('#div_kolumny').html(msg);
                // $('#div_lista_rej').html(msg);
            }
            ,error: function (err) {
                $("#div_kolumny").text(err.Message);
                // $("#div_lista_rej").text(err.Message);
            }
    });
}

$(window).on('load',function(){
    // console.log('przeładowanie');
    ZapytanieAjax();
});

jQuery(document).ready(function() {
    
    var inputs_Pisma_Sprawy_Kontrahent = $('#input_find_pismo_wgOpisu, #input_find_pismo_wgSprawy, #input_find_pismo_wgKontrahenta');//
    inputs_Pisma_Sprawy_Kontrahent.on('input',ZapytanieAjax);
});