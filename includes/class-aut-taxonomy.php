<?php
/**
 * User Taxonomy functionality
 *
 * @package AdvancedUserTaxonomies
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for registering and managing user taxonomies
 */
class AUT_Taxonomy {

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
        // Register the taxonomy
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        
        // Add user taxonomy term relationship management
        add_action( 'personal_options_update', array( $this, 'save_user_taxonomy_terms' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_taxonomy_terms' ) );
        
        // Filter users by taxonomy in admin
        add_action( 'pre_get_users', array( $this, 'filter_users_by_taxonomy' ) );
        
        // Handle the user filtering directly
        add_action( 'admin_init', array( $this, 'handle_user_filter' ) );
    }

    /**
     * Register the User Tags taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'                       => _x( 'User Tags', 'taxonomy general name', 'advanced-user-taxonomies' ),
            'singular_name'              => _x( 'User Tag', 'taxonomy singular name', 'advanced-user-taxonomies' ),
            'search_items'               => __( 'Search User Tags', 'advanced-user-taxonomies' ),
            'popular_items'              => __( 'Popular User Tags', 'advanced-user-taxonomies' ),
            'all_items'                  => __( 'All User Tags', 'advanced-user-taxonomies' ),
            'parent_item'                => __( 'Parent User Tag', 'advanced-user-taxonomies' ),
            'parent_item_colon'          => __( 'Parent User Tag:', 'advanced-user-taxonomies' ),
            'edit_item'                  => __( 'Edit User Tag', 'advanced-user-taxonomies' ),
            'view_item'                  => __( 'View User Tag', 'advanced-user-taxonomies' ),
            'update_item'                => __( 'Update User Tag', 'advanced-user-taxonomies' ),
            'add_new_item'               => __( 'Add New User Tag', 'advanced-user-taxonomies' ),
            'new_item_name'              => __( 'New User Tag Name', 'advanced-user-taxonomies' ),
            'separate_items_with_commas' => __( 'Separate user tags with commas', 'advanced-user-taxonomies' ),
            'add_or_remove_items'        => __( 'Add or remove user tags', 'advanced-user-taxonomies' ),
            'choose_from_most_used'      => __( 'Choose from the most used user tags', 'advanced-user-taxonomies' ),
            'not_found'                  => __( 'No user tags found.', 'advanced-user-taxonomies' ),
            'no_terms'                   => __( 'No user tags', 'advanced-user-taxonomies' ),
            'menu_name'                  => __( 'User Tags', 'advanced-user-taxonomies' ),
            'items_list_navigation'      => __( 'User Tags list navigation', 'advanced-user-taxonomies' ),
            'items_list'                 => __( 'User Tags list', 'advanced-user-taxonomies' ),
            'most_used'                  => _x( 'Most Used', 'user_tag', 'advanced-user-taxonomies' ),
            'back_to_items'              => __( '&larr; Back to User Tags', 'advanced-user-taxonomies' ),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => false,
            'public'            => false,
            'show_in_nav_menus' => false,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => false,
            'rewrite'           => false,
            'capabilities'      => array(
                'manage_terms' => 'manage_user_tags',
                'edit_terms'   => 'edit_user_tags',
                'delete_terms' => 'delete_user_tags',
                'assign_terms' => 'assign_user_tags',
            ),
            'show_in_rest'      => true,
        );

        register_taxonomy( $this->taxonomy, null, $args );

        // Add capabilities to administrator role
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'manage_user_tags' );
            $admin_role->add_cap( 'edit_user_tags' );
            $admin_role->add_cap( 'delete_user_tags' );
            $admin_role->add_cap( 'assign_user_tags' );
        }
    }

    /**
     * Get user taxonomy terms
     *
     * @param int    $user_id  User ID.
     * @param string $taxonomy Taxonomy name.
     * @return array Array of term objects
     */
    public function get_user_taxonomy_terms( $user_id, $taxonomy = '' ) {
        $taxonomy = $taxonomy ? $taxonomy : $this->taxonomy;
        $terms    = get_user_meta( $user_id, $taxonomy . '_terms', true );
        
        if ( empty( $terms ) ) {
            return array();
        }
        
        $term_ids = maybe_unserialize( $terms );
        
        if ( ! is_array( $term_ids ) ) {
            return array();
        }
        
        $term_args = array(
            'include'    => $term_ids,
            'hide_empty' => false,
        );
        
        return get_terms( $taxonomy, $term_args );
    }

    /**
     * Save user taxonomy terms
     *
     * @param int $user_id User ID.
     */
    public function save_user_taxonomy_terms( $user_id ) {
        // Check if we have permission to save user data
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        // Security check
        check_admin_referer( 'update-user_' . $user_id );

        // Save user tags if present in POST data
        if ( isset( $_POST[$this->taxonomy] ) ) {
            $term_ids = array_map( 'intval', (array) $_POST[$this->taxonomy] );
            update_user_meta( $user_id, $this->taxonomy . '_terms', $term_ids );
        } else {
            // If no terms were selected, delete the meta
            delete_user_meta( $user_id, $this->taxonomy . '_terms' );
        }
    }

    /**
     * Filter users by taxonomy in admin
     *
     * @param WP_User_Query $query The WP_User_Query instance.
     */
    public function filter_users_by_taxonomy( $query ) {
        global $pagenow;
        
        // Only filter on the users.php admin page
        if ( 'users.php' !== $pagenow ) {
            return;
        }
        
        // Check if we're filtering by the user tag
        if ( isset( $_GET[$this->taxonomy] ) ) {
            $term_id = intval( $_GET[$this->taxonomy] );
            
            // If term_id is 0 or empty, don't apply any filtering (show all users)
            if ( empty( $term_id ) ) {
                // Just return without modifying the query to show all users
                return;
            }
            
            // Get the term to make sure it exists
            $term = get_term( $term_id, $this->taxonomy );
            if ( is_wp_error( $term ) || empty( $term ) ) {
                $query->set( 'include', array( 0 ) ); // No valid term, return no results
                return;
            }
            
            // Get all users with this term
            $user_ids = $this->get_users_by_term_id( $term_id );
            
            if ( empty( $user_ids ) ) {
                $user_ids = array( 0 ); // No users found, return no results
            }
            
            // Apply the filter to the query
            $query->set( 'include', $user_ids );
        }
    }

    /**
     * Get users by term ID
     *
     * @param int $term_id Term ID.
     * @return array Array of user IDs
     */
    public function get_users_by_term_id( $term_id ) {
        global $wpdb;
        
        // Get all users with this term
        $meta_key = $this->taxonomy . '_terms';
        
        // Query users who have this term ID in their serialized meta
        $sql = $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND (
                meta_value LIKE %s
                OR meta_value LIKE %s
                OR meta_value LIKE %s
                OR meta_value LIKE %s
            )",
            $meta_key,
            '%' . $wpdb->esc_like( 's:' . strlen( $term_id ) . ':"' . $term_id . '"' ) . '%',
            '%' . $wpdb->esc_like( 'i:' . $term_id . ';' ) . '%',
            '%' . $wpdb->esc_like( 's:' . strlen( (string) $term_id ) . ':"' . $term_id . '"' ) . '%',
            '%' . $wpdb->esc_like( '"term_id";i:' . $term_id . ';' ) . '%'
        );
        
        return $wpdb->get_col( $sql );
    }

    /**
     * Handle the user filter directly
     */
    public function handle_user_filter() {
        global $pagenow;
        
        // Only on the users.php page
        if ( 'users.php' !== $pagenow ) {
            return;
        }
        
        // Check if our taxonomy filter is being used and the Change button was clicked
        if ( isset( $_GET[$this->taxonomy] ) && ! empty( $_GET[$this->taxonomy] ) && isset( $_GET['changeit'] ) ) {
            // Get the current URL parameters
            $params = $_GET;
            
            // Add the filter_action parameter
            $params['filter_action'] = 'Filter';
            
            // Remove the changeit parameter as it's not needed
            unset( $params['changeit'] );
            
            // Build the redirect URL
            $redirect_url = admin_url( 'users.php?' . http_build_query( $params ) );
            
            // Perform the redirect
            wp_redirect( $redirect_url );
            exit;
        }
    }
}
