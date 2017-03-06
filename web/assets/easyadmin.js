;(function($) {
		$('form[name=event] .repeating-occurrences .box-header').click(function () {
				$(this).closest('.repeating-occurrences').toggleClass('expanded');
		});

		window.confirmCreateRepeatingOccurrences = function(form) {
				return confirm('Please confirm creation of repeating occurrences');
		}
}(jQuery));
