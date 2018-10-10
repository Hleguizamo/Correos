
function animarEfecto(identificador){
    $(identificador).animate({
        opacity: 0.3
    }, 0, function() {
        $( this ).after( 
            $(identificador).animate({
                opacity: 1
            }, 1000)
        )
    });
}
function AlertaPersonalizable(mensaje, tiempo, tipo){
    return noty({layout:'center',text:mensaje,modal:true,type:tipo,buttons:false,timeout:tiempo});
}
function MensajeConfirmacionError(mensaje){
    noty({layout:'center',text:'<div style="text-align:justify;">'+mensaje+'</div>',modal:true,buttons:[{addClass:'btn btn-danger',text:'Aceptar',onClick:function($noty){$noty.close();}}],animation:{open:'animated bounceInLeft',close:'animated bounceOutLeft',easing:'swing',speed:500},type:'error'});
}
function MensajeConfirmacion(mensaje){
    noty({layout:'center',text:'<div style="text-align:justify;">'+mensaje+'</div>',modal:true,buttons:[{addClass:'btn btn-primary',text:'Aceptar',onClick:function($noty){$noty.close();}}],type:'confirm',animation:{open:'animated bounceInLeft',close:'animated bounceOutLeft',easing:'swing',speed:500}});
}
function Cargando(msg){
    return noty({layout:'center',text:msg,modal:true,type:'alert',timeout:false,closeWith:['none']});
}
function LoaderWapp(msg){
    return noty({layout:'center',text:msg,modal:true,type:'alert',timeout:false,closeWith:['none']});
}
function animar(x,elemento){$('#'+elemento).removeClass().addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){$(this).removeClass();});}
