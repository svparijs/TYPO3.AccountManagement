$(document).ready(function() {
	// Make sure clicks stays in the modal
	$('#loginpanel form').on('submit', function(el) {
		$('.modal-body').append('<p align="center"><i class="icon-spin icon-spinner icon-4x muted"></i></p>');
		$('.alert').remove();
		$('.login').hide();
		var form = $(this);
		var target = $(form.attr('data-target'));

		$.ajax({
			type: form.attr('method'),
			url: form.attr('action'),
			data: form.serialize(),
			dataType: 'json',
			success: function(response) {
					// Redirect upon successful login
				if (response.status === 'OK') {
					window.location = response.redirect;
				} else if (response.status === 'FAILED') {
					for (error in response.errors) {
						$('.modal-body').prepend('<div class="alert alert-'+ response.errors[error].severity.toLowerCase() +'"><a class="close" href="#" data-dismiss="alert">×</a>'+ response.errors[error].message +'</div>');
					}
					$('.control-group').addClass('error');
					$('p .icon-spin').remove();
					$('.login').show();
				}
			}
		});

		el.preventDefault();
	});
	$('.btn').removeClass('hide');

});