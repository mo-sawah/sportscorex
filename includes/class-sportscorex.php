<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main SportScoreX Class
 */
final class SportScoreX {

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * API Manager instance
     */
    public $api_manager;

    /**
     * Blocks Manager instance
     */
    public $blocks_manager;

    /**
     * Get plugin instance
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-api-manager.php';
        require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-blocks-manager.php';
        require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-admin.php';
        require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-shortcodes.php';
        require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-rest-api.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'editor_scripts' ) );
    }

    /**
     * Initialize plugin components
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain( 'sportscorex', false, dirname( SPORTSCOREX_PLUGIN_BASENAME ) . '/languages' );

        // Initialize components
        $this->api_manager = new SportScoreX_API_Manager();
        $this->blocks_manager = new SportScoreX_Blocks_Manager();
        
        new SportScoreX_Admin();
        new SportScoreX_Shortcodes();
        new SportScoreX_REST_API();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_scripts() {
        wp_enqueue_style(
            'sportscorex-frontend',
            SPORTSCOREX_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            SPORTSCOREX_VERSION
        );

        wp_enqueue_script(
            'sportscorex-frontend',
            SPORTSCOREX_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'wp-element' ),
            SPORTSCOREX_VERSION,
            true
        );

        wp_localize_script( 'sportscorex-frontend', 'sportscorex', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'sportscorex_nonce' ),
            'api_url' => home_url( '/wp-json/sportscorex/v1/' )
        ) );
    }

    /**
     * Enqueue editor scripts
     */
    public function editor_scripts() {
        wp_enqueue_script(
            'sportscorex-blocks',
            SPORTSCOREX_PLUGIN_URL . 'build/blocks.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
            SPORTSCOREX_VERSION,
            true
        );

        wp_enqueue_style(
            'sportscorex-blocks-editor',
            SPORTSCOREX_PLUGIN_URL . 'assets/css/editor.css',
            array( 'wp-edit-blocks' ),
            SPORTSCOREX_VERSION
        );
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Create default options
        $default_options = array(
            'apis' => array(),
            'cache_duration' => 300, // 5 minutes
            'theme_mode' => 'auto'
        );
        add_option( 'sportscorex_settings', $default_options );

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}