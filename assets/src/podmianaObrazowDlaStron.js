export default function Podmiana() {
    var numery_stron = $("a[name='numery_stron']");
    var podglad_do_zmiany = $('#div_podglad');
    numery_stron.on('click', function (e) {
        e.preventDefault();//wyłącza link
        var linkKlikniety = $(this);
        var adres = linkKlikniety.attr("href");
        window.history.pushState('', 'New Page Title', adres);
        $.ajax({
            url: adres,
            type: "GET",
            success: function (response) {
                podglad_do_zmiany.replaceWith(
                    $(response).find('#div_podglad')
                );
                Podmiana();// bez tej rekurencji po wywwołaniu ajax, linki numery_stron działają domyślnie
            }
        });
    });
}