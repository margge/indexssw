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
});