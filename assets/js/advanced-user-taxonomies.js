/**
 * Advanced User Taxonomies JavaScript
 * 
 * Initializes Select2 for user tag dropdowns and handles user filtering
 */
jQuery(document).ready(function($) {
    // Initialize Select2 for user tag dropdowns
    $('.aut-select2').select2({
        width: '100%',
        allowClear: true,
        placeholder: function() {
            return $(this).data('placeholder') || '';
        }
    });
    
    // Fix both top and bottom Change buttons on the users list page
    function setupChangeButtonHandler() {
        // When any Change button is clicked
        $('input[name="changeit"], input[name="changeit2"]').off('click').on('click', function(e) {
            // Get the current button
            var $button = $(this);
            var isBottom = $button.attr('name') === 'changeit2';
            
            // Get the taxonomy name from the data passed by PHP, or fallback to window variable or default
            var taxonomyName = (typeof autData !== 'undefined' && autData.taxonomyName) ? 
                               autData.taxonomyName : 
                               (window.autTaxonomyName || 'user_tag');
                               
            // Find the corresponding select element based on its ID
            var selectId = isBottom ? taxonomyName + '2' : taxonomyName;
            var $select = $('#' + selectId);
            
            // Only proceed if our taxonomy dropdown exists
            if ($select.length) {
                // Prevent the default form submission
                e.preventDefault();
                
                // Build the URL manually
                var baseUrl = window.location.href.split('?')[0];
                var params = new URLSearchParams(window.location.search);
                
                // Set our taxonomy value (or remove it if empty)
                if ($select.val()) {
                    params.set(taxonomyName, $select.val());
                } else {
                    params.delete(taxonomyName);
                }
                
                // Add the filter_action parameter
                params.set('filter_action', 'Filter');
                
                // Redirect to the filtered URL
                window.location.href = baseUrl + '?' + params.toString();
            }
        });
    }
    
    // Initialize the change button handlers if we're on the users page
    if ($('body').hasClass('users-php')) {
        // Initial setup
        setupChangeButtonHandler();
        
        // Also set up the handler again after a short delay to ensure it works with any dynamically loaded elements
        setTimeout(setupChangeButtonHandler, 500);
    }
});
