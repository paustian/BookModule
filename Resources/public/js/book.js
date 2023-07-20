function getSelectionHtml() {
    var html = "";
    if (typeof window.getSelection != "undefined") {
        var sel = window.getSelection();
        if (sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                container.appendChild(sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;
        }
    } else if (typeof document.selection != "undefined") {
        if (document.selection.type == "Text") {
            html = document.selection.createRange().htmlText;
        }
    }
    return html;
}

function getSelectionHtml2() {
    var htmlContent = ''

    // IE
    if ($.browser.msie) {
        htmlContent = document.selection.createRange().htmlText;
    } else {
        var range = window.getSelection().getRangeAt(0);
        var content = range.cloneContents();

        $('body').append('<span id="selection_html_placeholder"></span>');
        var placeholder = document.getElementById('selection_html_placeholder');

        placeholder.appendChild(content);

        htmlContent = placeholder.innerHTML;
        $('#selection_html_placeholder').remove();
    }
    return htmlContent;
}


(function($) {
    $(document).ready(function () {
        //Toggle fullscreen
        $("#panel-fullscreen").click(window.doFullScreen);
        $("#print-icon").click(window.doFullScreen);
        $("#print-icon2").click(window.doFullScreen);
    });
    window.doFullScreen = function (){
        if ( $("#themeLeftColumn").css("display") === "none" || $("#themeLeftColumn").css("visibility") === "hidden"){
            $("#themeLeftColumn").show();
            $(".navbar").show();
            $(".TTM-footer").show();
            $(".row").css("display", "flex");
            $("#themeMainContent").css("max-width", "");
        } else {
            $("#themeLeftColumn").hide();
            $(".navbar").hide();
            $(".TTM-footer").hide();
            $(".row").css("display", "contents");
            $("#themeMainContent").css("max-width", "4000px");
        }
    };
   /*
   This code is toying with the idea of highlighing where the reading spot when the text scrolls. It may be as simple as
   having a div in an absolute position on the page and then flashing a highlight color to show the reading spot*/
   var iScrollPos = 0;
   var iFirstVisible = 0;
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        var clientHeight = document.documentElement.clientHeight;
        var windowWheight = window.innerHeight;

        if( rect.height <= 0 || rect.width <= 0){
            return false;
        }
        return (
            rect.top >= window.screenTop &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    $(window).scroll(function () {
        //This gets called with the scrolled positions, not before scroll.
        var iCurScrollPos = $(this).scrollTop();
        if(iFirstVisible){
            iFirstVisible.removeClass("showposition");
        }
        //scroll down
        //walk down until we see the first visible element.
        if (iCurScrollPos > iScrollPos) {
            var panel_elements = $("div.panel-body").children();
            panel_elements.each(function(i){
                if(isInViewport(this)){
                    iFirstVisible = $(this);
                    return false;
                }
            });
            //Now hightlight it for a second and then fade away.
            iFirstVisible.addClass("showposition");
            setTimeout(() => {iFirstVisible.removeClass("showposition");}, 1000);
        }

        iScrollPos = iCurScrollPos;
    });
})(jQuery);

