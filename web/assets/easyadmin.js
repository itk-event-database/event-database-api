;(function($) {
		$('form[name=event] .repeating-occurrences .box-header').click(function () {
				$(this).closest('.repeating-occurrences').toggleClass('expanded');
		});

		window.confirmCreateRepeatingOccurrences = function(form) {
				var message = 'Please confirm creation of repeating occurrences';
				try {
						message = AppBundle.Translations.translate(message);
				} catch (ex) {}
				return confirm(message);
		}

		// @see https://github.com/javiereguiluz/EasyAdminBundle/issues/1518#issuecomment-284824895
		$('.field-collection').on('easyadmin.collection.item-added', function() {
				createAutoCompleteFields();
		});
}(jQuery));
