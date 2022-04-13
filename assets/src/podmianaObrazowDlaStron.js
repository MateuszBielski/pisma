jQuery(document).ready(function() {
    var numery_stron = $("a[name='numery_stron']");
    // numery_stron.removeAttr('href'); //to wyłącza zmianę kursora przy hover
    
    var podglad_do_zmiany = $('#div_podglad');
    numery_stron.on('click',function(e){
            e.preventDefault();//wyłącza link
            var linkKlikniety = $(this);
            var adres = linkKlikniety.attr("href");
            window.history.pushState('', 'New Page Title', adres);
            $.ajax({
                url: adres,
                type: "GET", 
                success: function (response) {
                //    console.log(adres);
                   podglad_do_zmiany.replaceWith(
                        $(response).find('#div_podglad')

                        );
                }
            });
    });
});