$(document).ready(function(){
    $('.grid').isotope({
      // options
      itemSelector: '.grid-item',
      layoutMode: 'fitRows'
    });

    $('.modal').on('shown.bs.modal', function(e) {
        $(this).find('.instaslider').owlCarousel({
            items: 1,
            nav: false,
        });

        //reasign close event
        $(this).find('.close').on('click', function(){
            $(this).closest('.modal').modal('hide');
        });
    });

});
