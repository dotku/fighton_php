
/*

Tera JS
Version 2.3
Made by Themanoid

*/


(function($) {

    "use strict"; // Strict mode

    /* 
        Portfolio scripts 
    */

    //  Define the portfolio grid
    var $grid = $('#grid');

    //  Show filter options on trigger click
    $('#filter-trigger').on('tap', function(){
        $('#filter-trigger').fadeOut(200, function(){
             $('#filters').fadeIn(500);
        });
    });

    //  On filter click, filter grid
    $('#filters').on( 'tap', 'button', function(e) {
        e.stopPropagation();
        var filterValue = $(this).attr('data-filter');
        $grid.isotope({ filter: filterValue });
        $('.item').addClass('visible');
        e.preventDefault();
    });

    // Back to top button

    var $toTop = $('<div class="back-to-top"></div>');
    $('body').append($toTop);
    $('body').on('tap', '.back-to-top', function(e){
        $('html,body').animate({scrollTop : 0});
        e.preventDefault();
    });

    //  Scroll effects
    $(window).scroll(function(){
        var scrolled = $(window).scrollTop();
        var scrolledPercentage = (100-(scrolled/$(window).height()*100))/100;
        $('.jumbotron').css('opacity', scrolledPercentage);
        if(scrolled > 200)
            $toTop.addClass('active'); // Back to top button
        else
            $toTop.removeClass('active');
    });

    $(window).load(function(){
        $('.container-fluid').addClass('loaded'); // Initialize the container
        $grid.isotope(); // Set the grid to isotope

        $('.item').waypoint(function(){
            $(this).addClass('visible'); // Show items
            $grid.isotope(); // Reload isotope items
        }, {offset : '70%'});

        $('.fadeIn').waypoint(function(){ // Fade in every .fadeIn class element
            $(this).addClass('visible');
        }, {offset : '70%'});

        var scrolled = $(window).scrollTop();
        if(scrolled > 200)
            $toTop.addClass('active'); // Back to top button

        // Placeholder fix for older browsers
        $('input, textarea').placeholder();
    });

    $('header').affix(); // Affix the header

    $('.trigger').on('tap', function(e){
        e.stopPropagation();
        $('#navigation').toggleClass('active'); // Toggle responsive menu
    });

    $('html').on('tap', function(){
        // Used to hide the responsive navigation on click outside
        $('#navigation').removeClass('active');
    });

    // Fade effect on navigation / header links
    $('a').on('tap', function(e){ 
        e.stopPropagation();
        var href = $(this).attr('href');
        if(href != '#' && !$(this).hasClass('lightbox') && !$(this).hasClass('anchor')){
            $('body').fadeOut(400, function(){
                window.location = href; // Go to url after smooth transition
            });
            e.preventDefault();
        }
        if($(this).hasClass('anchor')){
            var href = $(this).attr('href');
            $('html,body').animate({
                scrollTop : ($(href).offset().top)-50+'px'
            }, 800);
            $('#navigation').removeClass('active');
            e.preventDefault();
        }
    })

    //Fixes Firefox back button issue
    $(window).bind("unload", function() {
        // Nothing needed here :-)
    });

})(jQuery);
