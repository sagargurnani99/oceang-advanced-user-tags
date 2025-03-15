<?php
/**
 * Plugin Name: Advanced User Taxonomies
 * Plugin URI: https://example.com/advanced-user-taxonomies
 * Description: A professional plugin that extends WordPress by allowing custom taxonomies to be assigned to users.
 * Version: 1.0.0
 * Author: Sagar Gurnani
 * Author URI: https://profiles.wordpress.org/sagargurnani/
 * Text Domain: advanced-user-taxonomies
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package AdvancedUserTaxonomies
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'AUT_VERSION', '1.0.0' );
define( 'AUT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AUT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class Advanced_User_Taxonomies {

    /**
     * Singleton instance
     *
     * @var Advanced_User_Taxonomies
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Advanced_User_Taxonomies
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        // Include core files
        require_once AUT_PLUGIN_DIR . 'includes/class-aut-taxonomy.php';
        require_once AUT_PLUGIN_DIR . 'includes/class-aut-admin.php';
        require_once AUT_PLUGIN_DIR . 'includes/class-aut-ajax.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        
        // Initialize the plugin after WordPress is fully loaded
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Activation tasks
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Deactivation tasks
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for internationalization
        load_plugin_textdomain( 'advanced-user-taxonomies', false, dirname( AUT_PLUGIN_BASENAME ) . '/languages' );
        
        // Initialize components
        new AUT_Taxonomy();
        new AUT_Admin();
        new AUT_Ajax();
    }
}

/**
 * Returns the main instance of Advanced_User_Taxonomies
 *
 * @return Advanced_User_Taxonomies
 */
function AUT() {
    return Advanced_User_Taxonomies::get_instance();
}

// Initialize the plugin
AUT();
