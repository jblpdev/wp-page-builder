/*

*/
(function($) {

$(document).ready(function() {

	if (window.location.hash == '#block_saved') {
		window.top.wpbHideBlockEditModal()
	}

	$('#publish').on('click', function() {
		$(document.body).removeClass('loaded')
	})
})

$(window).load(function() {
	$(document.body).addClass('loaded')
})

})(jQuery);