/**
 * scripts.js
 * 
 * @since	2011-12-18
 * @version	1.0
 */

/**
 *  Main Drop-down menu
 */
$(document).ready(function() {
	$('#navigation ul li').hover(function() {
		$('ul', this).slideDown(200);
		$(this).children('a:first').addClass("hover");
	}, function() {
		$('ul', this).fadeOut(200);
		$(this).children('a:first').removeClass("hover");
	});
});

/**
 *  Header Drop-down menu
 */
$(document).ready(function() {
	
	// if exists..
	$(".submenu").width($(".my-account").outerWidth());
	$(".my-account").click(function() {
		var X = $(this).attr('id');
		if (X == 1) {
			$(".submenu").hide();
			$(this).attr('id', '0');
			$(this).css('color', '#fff');
			$(this).css('background-image', 'url(/assets/img/template/my-account-invert.png)');
		} else {
			$(".submenu").show();
			$(this).attr('id', '1');
			$(this).css('color', '#333');
			$(this).css('background-image', 'url(/assets/img/template/my-account.png)');
		}
	});

	// Mouse click on sub menu
	$(".submenu").mouseup(function() {
		return false;
	});

	// Mouse click on my account link
	$(".my-account").mouseup(function() {
		return false;
	});

	// Document Click
	$(document).mouseup(function() {
		$(".submenu").hide();
		$(".my-account").attr('id', '');
		$(".my-account").css('color', '#fff');
		$(".my-account").css('background-image', 'url(/assets/img/template/my-account-invert.png)');
	});
});
