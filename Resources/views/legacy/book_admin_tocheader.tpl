{pageaddvar name='javascript' value='jquery-ui'}
{pageaddvar name='javascript' value='modules/Book/javascript/jquery.cookie.js'}
{pageaddvar name='javascript' value='modules/Book/javascript/book.js'}
{pageaddvar name='stylesheet' value='javascript/jquery-ui/themes/base/jquery-ui.css'}
<script>                                                                               
$(document).ready(function() {                                                               
  jQuery("#tree ul").hide();                                                       

  jQuery("#tree li").each(function() {                                                  
    var handleSpan = jQuery("<span></span>");                            
    handleSpan.addClass("handle");                                       
    handleSpan.prependTo(this);                                          

    if(jQuery(this).has("ul").size() > 0) {                              
      handleSpan.addClass("collapsed");                        
      handleSpan.click(function() {                            
        var clicked = jQuery(this);                  
        clicked.toggleClass("collapsed expanded");   
        clicked.siblings("ul").toggle();             
      });                                                      
    }                                                                    
  });                                                                              
}
</script>  