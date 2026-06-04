(function($) {

	wp.customize('sidemenu_overlay_opacity', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu_open>.cover').css('opacity', '0.6');
		    } else {
                $('.sidemenu_open>.cover').css('opacity', ((newval - 1) / 10));
		    }
        });
	});

	wp.customize('sidemenu_overlay_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('body>.cover').css('background-color', '#000000');
		    } else {
                $('body>.cover').css('background-color', newval);
		    }
        });
	});

	wp.customize('sidemenu_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu').css('background-color', '#666666');
		    } else {
                $('.sidemenu').css('background-color', newval);
		    }
        });
	});

	wp.customize('sidemenu_title_text_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section h2').css('text-align', 'left');
		    } else {
                $('.sidemenu section h2').css('text-align', newval);
		    }
        });
	});

	wp.customize('sidemenu_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section h2').css('text-transform', 'none');
		    } else {
                $('.sidemenu section h2').css('text-transform', newval);
		    }
        });
	});

	wp.customize('sidemenu_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section h2').css('font-size', '1.5em');
		    } else {
                $('.sidemenu section h2').css('font-size', (newval / 10) + 'em');
		    }
        });
	});

	wp.customize('sidemenu_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section h2').css('font-weight', '700');
		    } else {
                $('.sidemenu section h2').css('font-weight', newval);
		    }
        });
	});

	wp.customize('sidemenu_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section h2').css('color', '#ffffff');
		    } else {
                $('.sidemenu section h2').css('color', newval);
		    }
        });
	});

	wp.customize('sidemenu_text_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu p, .sidemenu li').css('font-size', '1.5em');
		    } else {
                $('.sidemenu p, .sidemenu li').css('font-size', (newval / 10) + 'em');
		    }
        });
	});

	wp.customize('sidemenu_text_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu').css('color', '#ffffff');
		    } else {
                $('.sidemenu').css('color', newval);
		    }
        });
	});

	wp.customize('sidemenu_link_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu section a').css('color', '#d3d3d3');
		    } else {
                $('.sidemenu section a').css('color', newval);
		    }
        });
	});

	wp.customize('sidemenu_z_index', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.sidemenu').css('z-index', '9999');
		    } else {
                $('.sidemenu').css('z-index', newval);
		    }
		});
	});

})(jQuery);
