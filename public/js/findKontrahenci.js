jQuery(document).ready(function() {
    var input_find_kontrahent = $('#input_find_kontrahent');
    // console.log(input_find_pismo.val());
    // console.log('początek');
    input_find_kontrahent.on('input',function(){
        var tekst = input_find_kontrahent.val();
        var adres = "/kontrahent/indexAjax";
        $.ajax({
                url: adres,
                type: "GET",
                data: {
                    fraza: tekst
                },
                success: function (msg) {
                    $('#div_lista_kontrahentow').html(msg);
                }
                ,error: function (err) {
                    $("#div_lista_kontrahentow").text(err.Message);
                }
        });
    });
   
});