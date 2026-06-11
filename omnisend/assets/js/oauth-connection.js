jQuery(document).ready(function($) {
    $('.omnisend-connect-button').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const originalText = $button.text();
        
        // Disable button and show loading state
        $button.prop('disabled', true).text('Connecting...');
        
        // Register OAuth client
        $.ajax({
            url: omnisend_oauth.ajax_url,
            type: 'POST',
            data: {
                action: 'omnisend_register_oauth_client',
                nonce: omnisend_oauth.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Client registered successfully, redirect to authorization URL
                    window.location.href = response.data.auth_url;
                } else {
                    // Show error message
                    alert('Error: ' + (response.data.message || 'Failed to connect to Omnisend.'));
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Connection error. Please try again.');
                $button.prop('disabled', false).text(originalText);
            }
        });
    });
}); 