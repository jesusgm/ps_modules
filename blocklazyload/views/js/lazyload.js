$(document).ready(function(){
  $(img_selector).not(img_exclude_selector).each(function(){
    $(this).attr('data-original', $(this).attr('src')).removeAttr('src');
  })
});
$(window).bind("load", function() {
  if(lz_timeout == 0){
    $(img_selector).not(img_exclude_selector).lazyload({
      effect : lz_effect,
      event : lz_event,
      threshold : lz_threshold,
    });
  }else{
    $(img_selector).not(img_exclude_selector).lazyload({
        event : "sporty"
    });
    setTimeout(function() {
      $(img_selector).not(img_exclude_selector).trigger("sporty");
    }, lz_timeout);
  }
});
