jQuery(document).ready(function() {
    var obrazPng = $('img');

    obrazPng.on("dragstart", function() {
        return false;//wyłącza ikonkę przeciągania obrazu
    });
    


    var adresObrazu = obrazPng.attr("src_bez_fg");
    var polaDoUzupelnienia = $("td");
   var x0,x1,y0,y1;
   var img_szerokosc = obrazPng.width();
   var img_wysokosc = obrazPng.height();
   var rozpoznanyTekst = '';

  
   
   obrazPng.on('mousedown',function(e){
       x0 = e.pageX - this.offsetLeft;
       y0 = e.pageY - this.offsetTop;
       rozpoznanyTekst = '';
       polaDoUzupelnienia.off('click');
       //    polaDoUzupelnienia.off('click'); uruchomić
    // console.log("adres"+adresObrazu);
    });


    obrazPng.on('mouseup',function(e){
        x1 = e.pageX - this.offsetLeft;
        y1 = e.pageY - this.offsetTop;

        var xl,xp,yg,yd;
        xl = Math.min(x0,x1)/img_szerokosc;
        xp = Math.max(x0,x1)/img_szerokosc;
        yg = Math.min(y0,y1)/img_wysokosc;
        yd = Math.max(y0,y1)/img_wysokosc;
        
        $.ajax({
            url : '/pismo/rozpoznawanieAjax',
            type: 'GET',
            data : {
                wycinekUlamkowo :  {
                    xl : xl,
                    xp : xp,
                    yg : yg,
                    yd : yd,
                }    ,
                adresObrazu : adresObrazu
            },
            success: function(jsonResp) {
                // $('#pismo_oznaczenie').val(jsonResp.odp);
                rozpoznanyTekst = jsonResp.odp;
                // console.log(rozpoznanyTekst);
                polaDoUzupelnienia.on('click',function(){
                    // console.log(jsonResp.odp);
                    wybranePoleDoUzupelnienia = $(this).find("textarea");
                    wybranePoleDoUzupelnienia.val(function() {
                        
                        //czy spacja bo to kolejny dodawany fragment
                        var istniejacyTekst = this.value;
                        
                        return istniejacyTekst.length ? istniejacyTekst + " " + rozpoznanyTekst : istniejacyTekst + rozpoznanyTekst;
                    });
                    polaDoUzupelnienia.off('click');
                });

                    
            }
        });
    });
    
});