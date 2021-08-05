jQuery(document).ready(function() {
    var obrazPng = $('img');
    var div_img_js = document.getElementById('div_img');
    var rysowanie = false;
    var element = null;
    var elementNarysowany = null;

    var adresObrazu = obrazPng.attr("src_bez_fg");
    var polaDoUzupelnienia = $("td");
    var x0,x1,y0,y1;
    var img_szerokosc = obrazPng.width();
    var img_wysokosc = obrazPng.height();
    var rozpoznanyTekst = '';
    
    var kursor = {
        x: 0,
        y: 0,
        startX: 0,
        startY: 0
    };
    function ustalPozycjeKursora(e)
    {
        var ev = e || window.event; //Moz || IE
        if (ev.pageX) { //Moz
            
            kursor.x = ev.pageX;// + window.pageXOffset;
            kursor.y = ev.pageY;// + window.pageYOffset;
        } else if (ev.clientX) { //IE
            kursor.x = ev.clientX + document.body.scrollLeft;
            kursor.y = ev.clientY + document.body.scrollTop;
        }
    }
    div_img_js.onclick = function (e) 
    {
        kursor.startX = kursor.x;
        kursor.startY = kursor.y;
        // console.log(kursor.x+', '+kursor.y);
        if(element !== null){
            elementNarysowany = element;
            element = null;
            div_img_js.style.cursor = "default";
            rysowanie = false;

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
        }
        else{
            div_img_js.style.cursor = "crosshair";
            if(elementNarysowany)elementNarysowany.remove();
            element = document.createElement('div');
            element.className = 'obszar_do_rozpoznania';
            element.style.left = kursor.x + 'px';
            element.style.top = kursor.y + 'px';
            div_img_js.appendChild(element)
            rysowanie = true;

            x0 = e.pageX - this.offsetLeft;
            y0 = e.pageY - this.offsetTop;
            rozpoznanyTekst = '';
            polaDoUzupelnienia.off('click');
        }
    }
    div_img_js.onmousemove = function (e)
    {
        ustalPozycjeKursora(e);
        if (element !== null) {
            element.style.left = Math.min(kursor.startX,kursor.x) + 'px';
            element.style.top = Math.min(kursor.startY,kursor.y) + 'px';
            element.style.width = Math.abs(kursor.x - kursor.startX) + 'px';
            element.style.height = Math.abs(kursor.y - kursor.startY) + 'px';
        }
    }

});