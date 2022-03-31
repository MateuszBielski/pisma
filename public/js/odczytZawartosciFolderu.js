jQuery(document).ready(function() {
    var input_sciezka_do_folderu = $('#folder_sciezkaMoja');
    // console.log(input_find_pismo.val());
    // console.log('początek');
    input_sciezka_do_folderu.on('input',function(){
        var tekst = input_sciezka_do_folderu.val();
        var adres = "/folder/odczytZawartosciAjax";
        $.ajax({
                url: adres,
                type: "GET",
                data: {
                    fraza: tekst
                },
                success: function (msg) {
                    $('#div_lista_plikow').html(msg);
                }
                ,error: function (err) {
                    $("#div_lista_plikow").text(err.Message);
                }
        });
    });
   
});