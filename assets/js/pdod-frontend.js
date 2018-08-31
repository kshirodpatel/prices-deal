jQuery(function($) {

	var timezone_offset_minutes = new Date().getTimezoneOffset();
	timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

	$.post(pdodvars.ajaxurl, {
			action: 'pdod_get_timezone',
			timezoneOffset: timezone_offset_minutes
		}, function(data) {
			console.log(data);
		});

	
	if( $('.pdod-timer').length > 0 ) {

		$('.pdod-timer').each(function(index, value) {
			var DealEndDate = $(this).attr('data-pdod-end');
			var ProductId 	= $(this).attr('data-product-id');

			if( isNaN(DealEndDate) == true ) {
			var austDay = new Date(DealEndDate);

			$(this).countdown({
					until: austDay,
					onExpiry: function(){
						location.reload();
					}
				});
		}
		});
	}
});