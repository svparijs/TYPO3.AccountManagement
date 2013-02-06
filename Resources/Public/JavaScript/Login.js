$(document).ready(function() {
	$('.login').live('click', function(el) {
		var $url = $(this).attr('href');

		$.ajax({
			url: $url,
			context: this,
			dataType: 'html',
			success: function(response) {
				var $modal = $('#modal'),
					form = $('#login-form');

				$modal.html(response).find('form').ajaxForm({
					type: form.attr('method'),
					url: form.attr('action'),
					data: form.serialize(),
					dataType: 'json',
					success: function(response) {
						$('.form').hide();
						$('.modal-body').append('<p align="center"><i class="icon-spin icon-spinner icon-4x muted"></i></p>');
						$('.alert').remove();
						if (response.status === 'OK') {
							window.location = response.redirect;
						} else if (response.status === 'FAILED') {
							var error;
							//for (error in response.errors) {
							//	$('.modal-body').prepend('<div class="alert alert-error"><a class="close" href="#" data-dismiss="alert">×</a>'+ response.errors[error].message +'</div>');
							//}
							$('.control-group').addClass('error');
							$('p .icon-spin').remove();
							$('.form').show();
						}
					}
				});
				$modal.modal('show');
			}
		});
		el.preventDefault();
		return false;
	});

	// Make sure clicks stays in the modal
	$('#loginpanel').on('submit', function(el) {
		$('.modal-body').append('<p align="center"><i class="icon-spin icon-spinner icon-4x muted"></i></p>');
		$('.alert').remove();
		$('.login').hide();
		console.log('2 changed 1');
		var form = $(this);
		$.ajax({
			type: form.attr('method'),
			url: form.attr('action'),
			data: form.serialize(),
			dataType: 'json',
			success: function(response) {
				console.log('3 changed 1');
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