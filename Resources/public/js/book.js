


(function($) {
    class TextManager {

        init() {
            this.ajaxSettings = {
                "dataType": "json",
                "error": this.ajaxError,
                "timeout": 10000
            };
            this.cacheDomAndBindEvents();
        }

        cacheDomAndBindEvents(){
            this.cacheDom();
            this.bindEvents();
        }

        cacheDom() {
            this.$doHighlight = $("input[id=highlight]");
            this.$doDef = $("input[id=dodef]");
            this.$collectHighlight = $("input[id=collecthighlights]");
            this.$aid = $("input[id=aid]");
            this.$content = $("div[id=article_content]");
        }

        bindEvents() {
            this.$doHighlight.on("mouseup",  this.doHighlight.bind(this));
            this.$doDef.on("mouseup",  this.doDef.bind(this));
            this.$collectHighlight.on("mouseup",  this.collectHighlight.bind(this));
        }
        getSelectionHtml(){
            var html = "";
            var simple_text = "";
            if (typeof window.getSelection != "undefined") {
                var sel = window.getSelection();
                if (sel.rangeCount) {
                    var container = document.createElement("div");
                    for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                        container.appendChild(sel.getRangeAt(i).cloneContents());
                    }
                    html = container.innerHTML;
                    simple_text = container.innerText;
                }
            } else if (typeof document.selection !== "undefined") {
                if (document.selection.type === "Text") {
                    html = document.selection.createRange().htmlText;
                }
            }
            if(html === ""){
                return [html, 0];
            }
            var range = window.getSelection().getRangeAt(0);
            var the_text = document.getElementById("article_content");
            var preCaretRange = range.cloneRange();
            var selected = range.toString().length;
            preCaretRange.selectNodeContents(the_text);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            var pre_text = this.$content[0].innerHTML;
            var position = preCaretRange.toString().length - selected;
            return  [html, position, pre_text, simple_text];
        }

        doHighlight(evt){
            var selection = this.getSelectionHtml();
            if (selection[0] === ""){
                window.alert("You have to make a selection to be able to highlight it.");
                return;
            }
            this.sendAjax(
                "paustianbookmodule_user_customizetext",
                {"text" : selection[0],
                    "position": selection[1],
                    "pre_text": selection[2],
                    "function": "dohighlight",
                    "aid": this.$aid.val()},
                {"success": this.displayNewText.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        }

        doDef(evt){
            var selection = this.getSelectionHtml();
            if (selection[0] === ""){
                window.alert("You have to make a selection for it to be defined.");
                return;
            }
            var aid = this.$aid.val();
            //send a message to preview that item
            this.sendAjax(
                "paustianbookmodule_user_customizetext",
                {"text" : selection[3],
                        "function": "dodef",
                        "aid": aid},
                {"success": this.displayNewText.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        }

        collectHighlight(evt){
            var aid = this.$aid.val();
            //send a message to preview that item
            this.sendAjax(
                "paustianbookmodule_user_customizetext",
                {"function": "collecthighlights",
                    "aid": aid},
                {"success": this.redirectPage.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        }

        sendAjax(url, data, options) {
            //push the data object into the options
            options.data = data;
            $.extend(options, this.ajaxSettings);
            var theRoute = Routing.generate(url);
            $.ajax(theRoute, options);
        }

        displayNewText(result, textStatus, jqXHR){
            if(result.error !== ""){
                window.alert(result.error);
            } else {
                location.reload();
            }
            /*var htmlText = this.$content.html;
            var newText = htmlText.slice(0, result.end) + "</span>" + htmlText.slice(result.end);
            this.$content.html = newText.slice(0, result.start) + "<span class=\"highlight\">" + newText.slice(result.start);*/
        }

        redirectPage(result, textStatus, jqXHR){
            if(result.error !== ""){
                window.alert(result.error);
            } else {
                window.location.replace(result.url);
            }
        }
        ajaxError(jqXHR, textStatus, errorThrown){
            window.alert(textStatus + "\n" +errorThrown);
        }
    }

    $(document).ready(function () {
        //Toggle fullscreen
        $("#panel-fullscreen").click(window.doFullScreen);
        $("#print-icon").click(window.doFullScreen);
        $("#print-icon2").click(window.doFullScreen);
        let manager = new TextManager();
        manager.init();
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
            setTimeout(() => {iFirstVisible.removeClass("showposition");}, 2000);
        }

        iScrollPos = iCurScrollPos;
    });
})(jQuery);

