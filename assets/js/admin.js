/**
 * Advanced User Taxonomies Admin JavaScript
 *
 * @package AdvancedUserTaxonomies
 */

(function($) {
    'use strict';

    /**
     * User Tags Admin functionality
     */
    var AUTAdmin = {
        /**
         * Initialize the admin functionality
         */
        init: function() {
            this.initSelect2();
            this.fixUserTagsFilter();
        },

        /**
         * Initialize Select2 for user tags selection
         */
        initSelect2: function() {
            // Initialize Select2 for user profile tags only (not for filters)
            $('.aut-select2').select2({
                allowClear: true,
                placeholder: $(this).data('placeholder'),
                width: '100%',
                ajax: {
                    url: autData.ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term, // search term
                            page: params.page || 1,
                            action: 'aut_search_terms',
                            nonce: autData.nonce,
                            taxonomy: autData.taxonomy
                        };
                    },
                    processResults: function(data, params) {
                        // Parse the results into the format expected by Select2
                        if (data.success && data.data) {
                            return data.data;
                        }
                        return {
                            results: [],
                            pagination: {
                                more: false
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                minimumInputLength: 0,
                templateResult: function(term) {
                    if (term.loading) {
                        return autData.searching;
                    }
                    return term.text;
                },
                templateSelection: function(term) {
                    return term.text;
                },
                language: {
                    noResults: function() {
                        return autData.noResults;
                    },
                    searching: function() {
                        return autData.searching;
                    }
                }
            });
        },
        
        /**
         * Fix the User Tags filter functionality
         */
        fixUserTagsFilter: function() {
            // When the Change button is clicked, ensure the form submits correctly
            $('.tablenav .actions input[type="submit"]').on('click', function(e) {
                // Get the taxonomy name from the script data
                var taxonomyName = autData.taxonomy;
                
                // Find the corresponding select element
                var $select = $('select[name="' + taxonomyName + '"]');
                
                // If the select exists and has a value
                if ($select.length && $select.val()) {
                    // Add a hidden field for filter_action if it doesn't exist
                    if (!$('input[name="filter_action"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'filter_action',
                            value: 'Filter'
                        }).appendTo($select.closest('form'));
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        AUTAdmin.init();
    });

})(jQuery);
