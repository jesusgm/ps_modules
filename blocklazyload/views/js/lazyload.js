$(document).ready(function(){
  $(img_selector).not(img_exclude_selector).each(function(){
    $(this).attr('data-original', $(this).attr('src')).removeAttr('src');
  })
});
$(window).bind("load", function() {
    $(img_selector).not(img_exclude_selector).lazyload({
      effect : 'fadeIn'
    });
});
