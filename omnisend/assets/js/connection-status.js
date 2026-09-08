(function () {
    var statusUrl = window.omnisendConnectionStatus && window.omnisendConnectionStatus.url;
    var interval;

    if ( ! statusUrl ) {
        return;
    }

    function reloadWhenConnected() {
        if ( interval ) {
            return;
        }

        interval = setInterval( function () {
            fetch( statusUrl + '&_=' + Date.now(), { credentials: 'same-origin' } )
                .then( function ( response ) { return response.json(); } )
                .then( function ( status ) {
                    if ( status && status.connected ) {
                        clearInterval( interval );
                        location.reload();
                    }
                } )
                .catch( function () {} );
        }, 2000 );
    }

    document.querySelectorAll( '.omnisend-connect-action' ).forEach( function ( element ) {
        element.addEventListener( 'click', reloadWhenConnected );
    } );
})();
