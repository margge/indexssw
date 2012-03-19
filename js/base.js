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

    $('#add').click(function (){
        var cant = $('.individual').size();
        var add = "<div id=\"container_"+cant+"\"><input class=\"individual\" type=\"text\" id=\"individual_"+cant+"\"></input> <button id=\"remove_"+cant+"\">remover</button></div>";
        $('#individual_set').append(add);
        $("#remove_"+cant).click(function() {
            $('#container_'+cant).remove();
        });
    });

    $('#calculate').click(function(){
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