(function($) {

    GRID.init();

    $(".tooltip").tooltip();

    $("#main").css("min-height", window.outerHeight - $("#header").outerHeight(true) - $("#footer").outerHeight(true));

})(jQuery);