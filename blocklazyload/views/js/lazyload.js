$(document).ready(function(){
  /*
   *  Change the src attribute of target images to data-original attribute
   *  for the lazyload plugin
   */
  $(img_selector).not(img_exclude_selector).each(function(){
    $(this).attr('data-original', $(this).attr('src')).removeAttr('src');
  });

  /*
   *  Init the lazyload plugin to target images
   */
  $(img_selector).not(img_exclude_selector).lazyload({
    effect : 'fadeIn'
  });
  
});
