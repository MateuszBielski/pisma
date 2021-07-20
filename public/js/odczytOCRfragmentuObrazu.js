jQuery(document).ready(function() {
    var obrazPng = $('img');
    obrazPng.on("dragstart", function() {
        return false;//wyłącza ikonkę przeciągania obrazu
    });
    /*
    obrazPng.on('click',function(e){
        var x = e.pageX - this.offsetLeft;
        var y = e.pageY - this.offsetTop;
        console.log(x+", "+y);
    });
    */
   /**/
   obrazPng.on('mousedown',function(e){
    var x = e.pageX - this.offsetLeft;
    var y = e.pageY - this.offsetTop;
    console.log("start"+x+", "+y);
    });
    
   
   obrazPng.on('mouseup',function(e){
    var x = e.pageX - this.offsetLeft;
    var y = e.pageY - this.offsetTop;
    console.log("stop"+x+", "+y);
    });
    
});