$(document).ready(function(){
    $(document).on('click', '.img-container .instapic', function(){
        $('#popupoverlay').fadeIn();
        $(this).parent().find('.popup').fadeIn(400, function(){
            $(this).find('.slider').owlCarousel({
                items: 1,
                nav: true,
                dots: true

            });
        });
    });

    $(document).on('click', '.popup .closepopup', function(){
        $(this).closest('.popup').fadeOut();
        $('#popupoverlay').fadeOut();
    });
    $(document).on('click', '#popupoverlay', function(){
        $('.popup').fadeOut();
        $('#popupoverlay').fadeOut();
    });
});
