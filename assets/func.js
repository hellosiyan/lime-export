jQuery(function($) {
	var form = $('form#export-filters');

	if ( form.length ) {(function(){
		// Export Page
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

		$('.tables-list li input:checkbox', form).change(function() {
			if ( $(this).is(':checked') ) {
				$(this).closest('li').removeClass('inactive');
			} else {
				$(this).closest('li').addClass('inactive');
			}
		}).change();

		$('a[data-action=select-all]', form).click(function() {
			$(this).closest('.tables-list').find('input:checkbox:not(:checked)').click();
			return false;
		});

		$('a[data-action^=select-]', form).click(function() {
			switch ( $(this).data('Action') ) {
				case 'select-all':
					$(this).closest('.tables-list').find('input:checkbox:not(:checked)').click();
					break;
				case 'select-none':
					$(this).closest('.tables-list').find('input:checkbox:checked').click();
					break;
				case 'select-standard':
					$(this).closest('.tables-list').find('li.standard input:checkbox:not(:checked)').click();
					$(this).closest('.tables-list').find('li:not(.standard) input:checkbox:checked').click();
					break;
			}
			
			return false;
		});

		$('select[name=wple_dump_format]', form).change(function() {
			var format = $(this).val();
			if ( format == 'both' || format == 'structure' ) {
				$('input[name=wple_dump_add_drop]', form).removeAttr('disabled');
			} else {
				$('input[name=wple_dump_add_drop]', form).attr('disabled', 'disabled');
			}
		}).change();

		form.submit(function() {
			if ( form.find('.tables-list input:checkbox:checked').length < 1 ) {
				alert('Select at least one table to export.');
				return false;
			};

			$('div.updated').css('overflow', 'hidden').animate({height: 0, opacity: 0}, 500,function() {
				$(this).remove();
			});
		});
	})()};

	// Snapshots page
	$('.snapshot-description').each(function() {
		var tables = $(this).find('ul:first');
		if ( tables.length > 0 ) {
			tables.hide().prev().append($('<a href="#">View all <small>(' + tables.children().length + ')</small></a>').click(function() {
				if ( tables.is(':visible') ) {
					tables.slideUp();
					$(this).html('View all <small>(' + tables.children().length + ')</small>');
				} else {
					tables.slideDown();
					$(this).text('Hide');	
				}
				return false;
			}));
		};
	});

	$('.snapshot-title .delete a').click(function() {
		return confirm('DELETE snapshot ' + $(this).closest('.snapshot-title').find('strong:first').text() + '?');
	});

});