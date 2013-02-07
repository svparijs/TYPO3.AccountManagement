$(document).ready(function() {

	// @todo handle JsonP format
	// Link Action to get Login Panel
	$('.login-panel').click(function(el) {
		el.preventDefault();
		var url = $(this).attr('href');
		$.get(url, function(response){
			$('#modal-login').html(response);
			$('#modal-login').updateModal();
		});
	});

	// On Loading a request add a spinner icon
	$('#modal-login').on('show', function () {
		$('#modal-login').html('<div class="modal-body"><p align="center"><i class="icon-spin icon-spinner icon-4x muted"></i></p></div>');
	});

	// When DOM is ready show button
	$('.btn').removeClass('hide');

	// Make sure clicks stays in the modal
	$('#modal-login').updateModal();
});

 (function($){
 	$.fn.updateModal = function(e) {
		 var form = $('#login-form');
		 $(this).find('form').ajaxForm({
			type: form.attr('method'),
			url: form.attr('action'),
			dataType: 'json',
			beforeSubmit: function(arr, form) {
				$('.alert').remove();
				form.hide();
				$('.modal-body').append('<p align="center"><i class="icon-spin icon-spinner icon-4x muted"></i></p>');
			},
			success: function(response) {
				if (response.status === 'OK') {
					window.location = response.redirect;
				} else if (response.status === 'FAILED') {
				var error;
				for (error in response.errors) {
					$('.modal-body').prepend('<div class="alert alert-error"><a class="close" href="#" data-dismiss="alert">×</a>'+ response.errors[error].message +'</div>');
				}
				$('.control-group').addClass('error');
				$('p .icon-spin').remove();
				form.show(800, 'swing');
				}
			}
		 })
 	};
 })(jQuery);