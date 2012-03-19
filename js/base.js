$(document).ready(function() {
    $('#ssw').liteAccordion({
        onTriggerSlide : function() {
            this.find('figcaption').fadeOut();
        },
        onSlideAnimComplete : function() {
            this.find('figcaption').fadeIn();
        },
        autoPlay : false,
        pauseOnHover : true,
        theme : 'stitch',
        rounded : true,
        enumerateSlides : true
    }).find('figcaption:first').show();

    function validatePi(){
	    var cant = $('.individual').size();
        var ok = 0;
	    $('.individual').each(function(){
		    var num = parseInt($(this).val());
		    if(num === ~~num){
			    ok++;
			    $(this).removeClass("error"); 
		    } else {
			    $(this).addClass("error"); 
			    return true;
		    }
		});
		return ok >= cant;
    }

    $('#add').click(function (){
        var cant = $('.individual').size();
        if(!validatePi()){
			alert("Antes de agregar un nuevo Pi todos los compos deben tener datos");
			return;
		}
        var add = "<div id=\"container_"+cant+"\"><input class=\"individual\" type=\"text\" id=\"individual_"+cant+"\"></input> "+
                  "<img id=\"remove_"+cant+"\" src=\"images/rm.png\"/></div>";
        $('#individual_set').append(add);
        $("#remove_"+cant).click(function() {
            $('#container_'+cant).remove();
        });
    });

    $('#calculate').click(function(){
	    if(!validatePi()){
		    alert("Hay errores en los Pi");
		    $("#ssw").liteAccordion('prev');
		    return;
		}
        $("#loading").show();
        var str = "";
        var comma = "";
        $('.individual').each(function(){
            str += comma + $(this).val();
            comma = ",";
        });
        var formula = $('input:radio[name=formula]:checked').val();
        $.post('calculate', {individuals: str, formula: formula}, function(data) {
            $('#result').html(data);
            $("#loading").hide();
        });
    });

    $("#loading").hide();
});