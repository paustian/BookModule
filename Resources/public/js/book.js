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
        $("#panel-fullscreen").click(function (e) {
            e.preventDefault();

            var $this = $(this);

            if ($this.children('i').hasClass('fa-expand')) {
                $this.children('i').removeClass('fa-expand');
                $this.children('i').addClass('fa-compress');
            }
            else if ($this.children('i').hasClass('fa-compress')) {
                $this.children('i').removeClass('fa-compress');
                $this.children('i').addClass('fa-expand');
            }
            $(this).closest('.panel').toggleClass('panel-fullscreen');
        });
    });
})(jQuery);