jQuery(document).ready(function() {
    var input_find_pismo = $('#input_find_pismo');
    // console.log(input_find_pismo.val());
    // console.log('początek');
    input_find_pismo.on('input',function(){
        var tekst = input_find_pismo.val();
        // var kosztorys_id = $('#input_find_pismo').attr('kosztorys_id');
        var adres = "/pismo/indexAjax";
        // if(typeof kosztorys_id !== 'undefined')
        // adres += '?kosztorys_id='+kosztorys_id;
        $.ajax({
                url: adres,
                type: "GET",
                data: {
                    fraza: tekst
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