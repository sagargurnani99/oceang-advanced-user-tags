<?php
/**
 * Admin functionality
 *
 * @package AdvancedUserTaxonomies
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class responsible for admin UI and functionality
 */
class AUT_Admin {

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
        // Add menu items
        add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
        
        // Add user profile fields
        add_action( 'show_user_profile', array( $this, 'add_user_taxonomy_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'add_user_taxonomy_fields' ) );
        
        // Add filter dropdown to users list - priority 20 to ensure it appears after role filter
        add_action( 'restrict_manage_users', array( $this, 'add_taxonomy_filter_to_users_list' ), 20 );
        
        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add admin menus
     */
    public function add_admin_menus() {
        add_submenu_page(
            'users.php',                      // Parent slug
            __( 'User Tags', 'advanced-user-taxonomies' ), // Page title
            __( 'User Tags', 'advanced-user-taxonomies' ), // Menu title
            'manage_user_tags',               // Capability
            'edit-tags.php?taxonomy=' . $this->taxonomy, // Menu slug with post_type parameter
            null                              // Callback function
        );
    }

    /**
     * Add user taxonomy fields to user profile
     *
     * @param WP_User $user User object.
     */
    public function add_user_taxonomy_fields( $user ) {
        // Get the taxonomy object
        $tax = get_taxonomy( $this->taxonomy );
        
        // Check if current user can assign terms
        if ( ! current_user_can( $tax->cap->assign_terms ) ) {
            return;
        }
        
        // Get all terms for this taxonomy
        $terms = get_terms( array(
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => false,
        ) );
        
        // Get user's terms
        $user_terms = get_user_meta( $user->ID, $this->taxonomy . '_terms', true );
        $user_terms = is_array( $user_terms ) ? $user_terms : array();
        
        ?>
        <h3><?php esc_html_e( 'User Tags', 'advanced-user-taxonomies' ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="<?php echo esc_attr( $this->taxonomy ); ?>">
                        <?php esc_html_e( 'Select User Tags', 'advanced-user-taxonomies' ); ?>
                    </label>
                </th>
                <td>
                    <select name="<?php echo esc_attr( $this->taxonomy ); ?>[]" 
                            id="<?php echo esc_attr( $this->taxonomy ); ?>" 
                            class="aut-select2" 
                            multiple="multiple" 
                            style="width: 100%;"
                            data-placeholder="<?php esc_attr_e( 'Select or search for user tags...', 'advanced-user-taxonomies' ); ?>">
                        <?php foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->term_id ); ?>" 
                                <?php selected( in_array( $term->term_id, $user_terms ) ); ?>>
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e( 'Select tags to assign to this user. You can select multiple tags.', 'advanced-user-taxonomies' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Add taxonomy filter dropdown to users list
     * 
     * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
     */
    public function add_taxonomy_filter_to_users_list( $which ) {
        // Only proceed if we have terms
        $terms = get_terms( array(
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => false,
        ) );
        
        if ( empty( $terms ) ) {
            return;
        }
        
        // Get current selected term
        $selected = isset( $_GET[$this->taxonomy] ) ? intval( $_GET[$this->taxonomy] ) : 0;
        
        // Generate a unique ID based on which location we're at (top or bottom)
        $id = 'bottom' === $which ? $this->taxonomy . '2' : $this->taxonomy;
        
        // Output the dropdown using the exact same format as WordPress core
        ?>
        <label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>">
            <?php esc_html_e( 'Filter by user tag', 'advanced-user-taxonomies' ); ?>
        </label>
        <select name="<?php echo esc_attr( $this->taxonomy ); ?>" id="<?php echo esc_attr( $id ); ?>">
            <option value=""><?php esc_html_e( 'All User Tags', 'advanced-user-taxonomies' ); ?></option>
            <?php foreach ( $terms as $term ) : ?>
                <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $selected, $term->term_id ); ?>>
                    <?php echo esc_html( $term->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only enqueue on relevant pages
        if ( ! in_array( $hook, array( 'user-edit.php', 'profile.php', 'users.php', 'edit-tags.php' ) ) ) {
            return;
        }
        
        // Check if we're on the User Tags taxonomy page
        $taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( $_GET['taxonomy'] ) : '';
        if ( 'edit-tags.php' === $hook && $this->taxonomy !== $taxonomy ) {
            return;
        }
        
        // Register and enqueue Select2
        wp_register_style( 'select2', AUT_PLUGIN_URL . 'assets/css/select2.min.css', array(), '4.0.13' );
        wp_register_script( 'select2', AUT_PLUGIN_URL . 'assets/js/select2.min.js', array( 'jquery' ), '4.0.13', true );
        
        // Plugin custom styles and scripts
        wp_register_style( 'aut-admin', AUT_PLUGIN_URL . 'assets/css/admin.css', array( 'select2' ), AUT_VERSION );
        wp_register_script( 'aut-admin', AUT_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'select2' ), AUT_VERSION, true );
        
        // Localize script
        wp_localize_script( 'aut-admin', 'autData', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'aut_ajax_nonce' ),
            'taxonomy'  => $this->taxonomy,
            'searching' => __( 'Searching...', 'advanced-user-taxonomies' ),
            'noResults' => __( 'No results found', 'advanced-user-taxonomies' ),
        ) );
        
        // Enqueue all assets
        wp_enqueue_style( 'select2' );
        wp_enqueue_style( 'aut-admin' );
        wp_enqueue_script( 'select2' );
        wp_enqueue_script( 'aut-admin' );
    }
}
