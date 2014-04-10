jQuery(function($) {
	var form = $('form#export-filters');

	// Export Page
	if ( form.length > 0 ) {(function(){
		$('input:radio[name=wple_preset]', form).change(function() {
			if ( $(this).val() == 'standard' ) {
				$('.export-settings', form).slideUp();
			} else {
				$('.export-settings', form).slideDown();
			}
		}).filter(':checked').each(function() {
			$('.export-settings', form).toggle( $(this).val() != 'standard' );
		});

		$('.tables-list li input:checkbox', form).change(function() {
			$(this).closest('li').toggleClass('inactive', $(this).is(':not(:checked)'));
		}).change();

		$('a[data-action=select-all]', form).click(function() {
			$(this).closest('.tables-list').find('input:checkbox:not(:checked)').click();
			return false;
		});

		$('a[data-action^=select-]', form).click(function() {
			var tables_list = $(this).closest('.tables-list');
			switch ( $(this).attr('data-action') ) {
				case 'select-all':
					tables_list.find('input:checkbox:not(:checked)').click();
					break;
				case 'select-none':
					tables_list.find('input:checkbox:checked').click();
					break;
				case 'select-standard':
					tables_list.find('li.standard input:checkbox:not(:checked), li:not(.standard) input:checkbox:checked').click();
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

			$('div.updated').css({'overflow': 'hidden'}).animate({height: 0, opacity: 0}, 500, function() {
				$(this).remove();
			});
		});
	})()};

	// Snapshots page
	if ( $('.wple-snapshots').length > 0 ) {(function(){
		$('.snapshot-description').each(function() {
			var $th = $(this),
				tables = $th.find('ul:first');
			
			if ( tables.length > 0 ) {
				tables.hide().prev().append($('<a href="#">View all <small>(' + tables.children().length + ')</small></a>').click(function() {
					if ( tables.is(':visible') ) {
						tables.hide();
						$(this).html('View all <small>(' + tables.children().length + ')</small>');
					} else {
						tables.show();
						$(this).text('Hide');	
					}
					return false;
				}));
			};
		});

		$('.snapshot-title .delete a').click(function() {
			return confirm('DELETE snapshot ' + $(this).closest('.snapshot-title').find('strong:first').text() + '?');
		});

		$('#doaction').click(function() {
			if ( $(this).prev('select').val() == 'delete' ) {
				return confirm('Delete selected snapshots?');
			};
		});
	})();};

});