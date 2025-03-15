<?php
/**
 * AJAX functionality
 *
 * @package AdvancedUserTaxonomies
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for AJAX functionality
 */
class AUT_Ajax {

    /**
     * Taxonomy name
     *
     * @var string
     */
    private $taxonomy = 'user_tag';

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX actions
        add_action( 'wp_ajax_aut_search_terms', array( $this, 'search_terms' ) );
    }

    /**
     * AJAX handler for searching terms
     */
    public function search_terms() {
        // Check nonce
        if ( ! check_ajax_referer( 'aut_ajax_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'advanced-user-taxonomies' ) ) );
        }

        // Check if user has permission
        if ( ! current_user_can( 'edit_users' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action', 'advanced-user-taxonomies' ) ) );
        }

        // Get search term
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
        
        // Default response
        $response = array(
            'results' => array(),
            'pagination' => array(
                'more' => false,
            ),
        );

        // Set up arguments for get_terms
        $args = array(
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => false,
            'number'     => 10,
            'offset'     => ( $page - 1 ) * 10,
        );

        // Add search if provided
        if ( ! empty( $search ) ) {
            $args['search'] = $search;
        }

        // Get terms
        $terms = get_terms( $args );
        $total_terms = wp_count_terms( array(
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => false,
            'search'     => ! empty( $search ) ? $search : '',
        ) );

        // Check if we have terms
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $response['results'][] = array(
                    'id'   => $term->term_id,
                    'text' => $term->name,
                );
            }

            // Check if there are more pages
            if ( $total_terms > $page * 10 ) {
                $response['pagination']['more'] = true;
            }
        }

        wp_send_json_success( $response );
    }
}
