(function () {
	var POLL_INTERVAL_MS = 2000;
	var MAX_POLL_ATTEMPTS = 150;

	var statusUrl = window.omnisendConnectionStatus && window.omnisendConnectionStatus.url;
	var interval;
	var attempts = 0;

	if ( ! statusUrl ) {
		return;
	}

	function stopPolling() {
		clearInterval( interval );
		interval = undefined;
	}

	function reloadWhenConnected() {
		if ( interval ) {
			return;
		}

		attempts = 0;

		interval = setInterval( function () {
			attempts++;

			if ( attempts > MAX_POLL_ATTEMPTS ) {
				stopPolling();
				return;
			}

			fetch( statusUrl + '&_=' + Date.now(), { credentials: 'same-origin' } )
				.then( function ( response ) { return response.json(); } )
				.then( function ( status ) {
					if ( status && status.connected ) {
						stopPolling();
						location.reload();
					}
				} )
				.catch( function () {} );
		}, POLL_INTERVAL_MS );
	}

	document.querySelectorAll( '.omnisend-connect-action' ).forEach( function ( element ) {
		element.addEventListener( 'click', reloadWhenConnected );
	} );
})();
