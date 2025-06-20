require([ "jquery" ], function($){
    $(document).scroll(function () {
        var $window = $(window);
        var windowsize = $window.width();
        var height = $(document).scrollTop();
            if(height  > 150 && windowsize >= 768) {
                $('.nav-sections').addClass('fixed-menu');
            } else {
                $('.nav-sections').removeClass('fixed-menu');
            }
    });
});