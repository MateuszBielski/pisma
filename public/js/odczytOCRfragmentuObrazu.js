jQuery(document).ready(function() {
    var obrazPng = $('img');
    obrazPng.on("dragstart", function() {
        return false;//wyłącza ikonkę przeciągania obrazu
    });
    
   var x0,x1,y0,y1;
   var img_szerokosc = obrazPng.width();
   var img_wysokosc = obrazPng.height();
    obrazPng.on('mousedown',function(e){
    x0 = e.pageX - this.offsetLeft;
    y0 = e.pageY - this.offsetTop;
    
    });


    obrazPng.on('mouseup',function(e){
    x1 = e.pageX - this.offsetLeft;
    y1 = e.pageY - this.offsetTop;

    var xl,xp,yg,yd;
    xl = Math.min(x0,x1);
    xp = Math.max(x0,x1);
    yg = Math.min(y0,y1);
    yd = Math.max(y0,y1);
    
    console.log("start"+xl+", "+yg);
    console.log("stop"+xp+", "+yd);
    console.log("wymiary: "+img_szerokosc+", "+img_wysokosc);
    });
    
});