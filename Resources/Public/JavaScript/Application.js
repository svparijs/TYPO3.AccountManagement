$(document).ready(function() {

	var setModuleHtml = function(html) {
		$('.modal-body').html(html).find('form').ajaxForm({
			dataType: 'json',
			success: function(result) {
				setModuleHtml(result.html);
			}
		});
	};

	var setFormValidation = function(errors) {
		// Remove the errors to rerun error messaging
		$('.control-group').removeClass('error');
		$('.help-inline').remove();
		for (property in errors) {
			for(errorKey in errors[property]) {
				var $propertyControls = $('#'+ property).parent();
				var $controlGroup = $propertyControls.parent();
				$controlGroup.addClass('error');
				$propertyControls.append('<span class="help-inline">'+ errors[property][errorKey].message +'</span>');
			}
		}
	};

	$('.module a').live('click', function(event) {
		if ($(this).attr('href').match(/#/) == null) {
			$.ajax({
				context: this,
				url: $(this).attr('href'),
				success: function(result) {
					setModuleHtml(result.html);
				}
			});
			return false;
		}
	});

	$('.confirm').live('click', function(event) {
		event.preventDefault();

		var $modal = $('#modal-from-dom');
		$modal.find('h3').html($(this).attr('data-confirm-header'));
		$modal.find('p').html($(this).attr('data-confirm-message'));
		$modal.find('.modal-footer a:first').attr('href', $(this).attr('href'));

		$modal.modal('show');
	});

	$('.modal-action').live('click', function(event) {
		event.preventDefault();

		var $url = $(this).attr('href');

		$.ajax({
			url: $url,
			context: this,
			dataType: 'html',
			success: function(response) {
				var $modal = $('#modal');
				$modal.html(response).find('form').ajaxForm({
					dataType: 'json',
					success: function(result) {
						var $result = result;
							// Check for response
						if($result.response == 'OK' ) {
							$modal.modal('hide');
							location.reload();
						} else if ( $result.errors) {
							// Error handling
							setFormValidation($result.errors);
						}
					}
				});
				$modal.modal('show');
			}
		});
		return false;
	});
});