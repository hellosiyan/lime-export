jQuery(function($) {
	// pass
	var form = $('#export-filters');
	$('input:radio[name=wple_preset]', form).change(function() {
		if ( $(this).val() == 'standard' ) {
			$('.export-settings', form).slideUp();
		} else {
			$('.export-settings', form).slideDown();
		}
	}).filter(':checked').each(function() {
		if ( $(this).val() == 'standard' ) {
			$('.export-settings', form).hide();
		} else {
			$('.export-settings', form).show();
		}
	});

	$('.tables-list li input:checkbox').change(function() {
		if ( $(this).is(':checked') ) {
			$(this).closest('li').removeClass('inactive');
		} else {
			$(this).closest('li').addClass('inactive');
		}
	}).change();

});