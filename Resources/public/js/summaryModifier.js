(function ($) {
    $(document).ready(function () {
        summaryModifier.init();
    });

    var summaryModifier = {
        ajaxSettings: {
            "dataType": "json",
            "error": this.ajaxError,
            "timeout": 10000
        },

        init: function () {
            this.cacheDomAndBindEvents();
        },

        cacheDomAndBindEvents: function(){
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function () {
            this.$summaryLevel = $("input[id=summaryVal]");
            this.$contentDiv = $(".panel-body");
            this.$aid = $("input[id=aid]");
        },

        bindEvents: function () {
            this.$summaryLevel.on("mouseup",  this.changeSummmary.bind(this));
        },

        changeSummmary: function (evt){
            var sumLevel = this.$summaryLevel.val();
            var aid = this.$aid.val();
            //send a message to preview that item
            this.sendAjax(
                "paustianbookmodule_user_sumlevelchange",
                {"sumLevel" : sumLevel,
                        "aid": aid},
                {"success": this.displayNewText.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        },

        displayNewText: function (result, textStatus, jqXHR){
            //extract the panel-body content from the returning text
            var content = $(".panel-body", result.html);
            this.$contentDiv.html(content.html());
            //We need to redo this since we just replaced DOMS
            this.cacheDomAndBindEvents();
            //reset the sumlevel
            this.$summaryLevel.val(result.sumLevel);
            $("#panel-fullscreen").click(window.doFullScreen);
            $("#print-icon").click(window.doFullScreen);
            $("#print-icon2").click(window.doFullScreen);

        },

        sendAjax: function (url, data, options) {
            //push the data object into the options
            options.data = data;
            $.extend(options, this.ajaxSettings);
            var theRoute = Routing.generate(url);
            $.ajax(theRoute, options);
        },

        ajaxError: function(jqXHR, textStatus, errorThrown){
            window.alert(textStatus + "\n" +errorThrown);
        },
    };
})(jQuery);