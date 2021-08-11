jQuery(document).ready(function() {
    
    // var inputs_Pisma_Sprawy_Kontrahent = $('#input_find_pismo_wgOpisu, #input_find_pismo_wgSprawy, #input_find_pismo_wgKontrahenta');
    var inputs_Pisma_Sprawy_Kontrahent = $('#wyszukiwanie_dokumentow_dokument,#wyszukiwanie_dokumentow_sprawa, #wyszukiwanie_dokumentow_kontrahent');//
    inputs_Pisma_Sprawy_Kontrahent.on('input',ZapytanieAjax);
});