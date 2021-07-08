jQuery(document).ready(function() {
    var input_opisPisma = $('#input_find_pismo_wgOpisu');
    var input_opisSprawy = $('#input_find_pismo_wgSprawy');
    var input_nazwaKontrahenta = $('#input_find_pismo_wgKontrahenta');
    var inputs_Pisma_Sprawy_Kontrahent = $('#input_find_pismo_wgOpisu, #input_find_pismo_wgSprawy, #input_find_pismo_wgKontrahenta');//
    // console.log(input_find_pismo.val());
    // console.log('początek');
    inputs_Pisma_Sprawy_Kontrahent.on('input',function(){
    // $(document).on('input',)
        $.ajax({
                url: "/pismo/indexAjaxWgOpisuKontrahIstrony",
                type: "GET",
                data: {
                    opisPisma: input_opisPisma.val(),
                    opisSprawy: input_opisSprawy.val(),
                    nazwaKontrahenta: input_nazwaKontrahenta.val(),
                },
                success: function (msg) {
                    // console.log('sukces'); 
                    // $("#kontener").text(tekst);
                    $('#div_lista_rej').html(msg);
                     //var users_list = $("#users_list");
                     //users_list.replaceWith(response.find('#users_list'));
                }
                ,error: function (err) {
                    $("#div_lista_rej").text(err.Message);
                }
        });
    });
   
});