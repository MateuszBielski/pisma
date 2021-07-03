jQuery(document).ready(function() {
    var input_find_kontrahent = $('#input_find_sprawa');
    // console.log(input_find_pismo.val());
    // console.log('początek');
    input_find_kontrahent.on('input',function(){
        var tekst = input_find_kontrahent.val();
        var adres = "/sprawa/indexAjax";
        $.ajax({
                url: adres,
                type: "GET",
                data: {
                    fraza: tekst
                },
                success: function (msg) {
                    $('#div_lista_spraw').html(msg);
                }
                ,error: function (err) {
                    $("#div_lista_spraw").text(err.Message);
                }
        });
    });
   
});