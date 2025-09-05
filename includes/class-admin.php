<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Class - Settings page and configuration
 */
class SportScoreX_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'SportScoreX Settings', 'sportscorex' ),
            __( 'SportScoreX', 'sportscorex' ),
            'manage_options',
            'sportscorex',
            array( $this, 'admin_page' ),
            'dashicons-chart-line',
            30
        );

        add_submenu_page(
            'sportscorex',
            __( 'API Settings', 'sportscorex' ),
            __( 'API Settings', 'sportscorex' ),
            'manage_options',
            'sportscorex-apis',
            array( $this, 'api_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'sportscorex_settings', 'sportscorex_settings', array(
            'sanitize_callback' => array( $this, 'sanitize_settings' )
        ) );

        add_settings_section(
            'sportscorex_api_section',
            __( 'API Configuration', 'sportscorex' ),
            array( $this, 'api_section_callback' ),
            'sportscorex'
        );

        // API-Sports
        add_settings_field(
            'api_sports_key',
            __( 'API-Sports Key', 'sportscorex' ),
            array( $this, 'api_sports_field' ),
            'sportscorex',
            'sportscorex_api_section'
        );

        // TheSportsDB
        add_settings_field(
            'thesportsdb_key',
            __( 'TheSportsDB Key', 'sportscorex' ),
            array( $this, 'thesportsdb_field' ),
            'sportscorex',
            'sportscorex_api_section'
        );

        // Football-API
        add_settings_field(
            'football_api_key',
            __( 'Football-API Key', 'sportscorex' ),
            array( $this, 'football_api_field' ),
            'sportscorex',
            'sportscorex_api_section'
        );

        // General settings section
        add_settings_section(
            'sportscorex_general_section',
            __( 'General Settings', 'sportscorex' ),
            array( $this, 'general_section_callback' ),
            'sportscorex'
        );

        add_settings_field(
            'cache_duration',
            __( 'Cache Duration (seconds)', 'sportscorex' ),
            array( $this, 'cache_duration_field' ),
            'sportscorex',
            'sportscorex_general_section'
        );

        add_settings_field(
            'default_theme',
            __( 'Default Theme', 'sportscorex' ),
            array( $this, 'default_theme_field' ),
            'sportscorex',
            'sportscorex_general_section'
        );
    }

    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="sportscorex-admin-header">
                <p class="description">
                    <?php _e( 'Configure your sports APIs and widget settings. Get your API keys from the providers below:', 'sportscorex' ); ?>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'sportscorex_settings' );
                do_settings_sections( 'sportscorex' );
                submit_button();
                ?>
            </form>

            <div class="sportscorex-api-links">
                <h3><?php _e( 'API Providers', 'sportscorex' ); ?></h3>
                <ul>
                    <li><a href="https://www.api-football.com/" target="_blank">API-Sports (Football)</a> - Premium with free tier</li>
                    <li><a href="https://www.thesportsdb.com/api.php" target="_blank">TheSportsDB</a> - Free with optional premium</li>
                    <li><a href="https://freewebapi.com/" target="_blank">Football-API</a> - Free real-time updates</li>
                </ul>
            </div>
        </div>

        <style>
        .sportscorex-admin-header {
            background: #f8f9fa;
            border-left: 4px solid #3b82f6;
            margin: 20px 0;
            padding: 15px;
        }
        .sportscorex-api-links {
            background: #fff;
            border: 1px solid #ddd;
            margin-top: 30px;
            padding: 20px;
        }
        .sportscorex-api-links ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .sportscorex-api-links li {
            margin-bottom: 10px;
            padding: 8px 0;
        }
        .sportscorex-api-links a {
            color: #3b82f6;
            text-decoration: none;
        }
        </style>
        <?php
    }

    /**
     * API section callback
     */
    public function api_section_callback() {
        echo '<p>' . __( 'Enter your API keys below. Multiple APIs provide redundancy and fallback options.', 'sportscorex' ) . '</p>';
    }

    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . __( 'Configure general plugin behavior and appearance.', 'sportscorex' ) . '</p>';
    }

    /**
     * API Sports field
     */
    public function api_sports_field() {
        $settings = get_option( 'sportscorex_settings', array() );
        $value = $settings['apis']['api_sports'] ?? '';
        ?>
        <input type="text" name="sportscorex_settings[apis][api_sports]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Your API-Sports key">
        <p class="description"><?php _e( 'Premium API with comprehensive coverage. Get from api-football.com', 'sportscorex' ); ?></p>
        <?php
    }

    /**
     * TheSportsDB field
     */
    public function thesportsdb_field() {
        $settings = get_option( 'sportscorex_settings', array() );
        $value = $settings['apis']['thesportsdb'] ?? '';
        ?>
        <input type="text" name="sportscorex_settings[apis][thesportsdb]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Your TheSportsDB key (optional)">
        <p class="description"><?php _e( 'Free tier available. Leave empty to use public API.', 'sportscorex' ); ?></p>
        <?php
    }

    /**
     * Football API field
     */
    public function football_api_field() {
        $settings = get_option( 'sportscorex_settings', array() );
        $value = $settings['apis']['football_api'] ?? '';
        ?>
        <input type="text" name="sportscorex_settings[apis][football_api]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Your Football-API key">
        <p class="description"><?php _e( 'Free API with real-time updates every 15 seconds.', 'sportscorex' ); ?></p>
        <?php
    }

    /**
     * Cache duration field
     */
    public function cache_duration_field() {
        $settings = get_option( 'sportscorex_settings', array() );
        $value = $settings['cache_duration'] ?? 300;
        ?>
        <input type="number" name="sportscorex_settings[cache_duration]" value="<?php echo esc_attr( $value ); ?>" min="60" max="3600" class="small-text">
        <p class="description"><?php _e( 'How long to cache API responses (60-3600 seconds).', 'sportscorex' ); ?></p>
        <?php
    }

    /**
     * Default theme field
     */
    public function default_theme_field() {
        $settings = get_option( 'sportscorex_settings', array() );
        $value = $settings['theme_mode'] ?? 'auto';
        ?>
        <select name="sportscorex_settings[theme_mode]">
            <option value="auto" <?php selected( $value, 'auto' ); ?>><?php _e( 'Auto (System)', 'sportscorex' ); ?></option>
            <option value="light" <?php selected( $value, 'light' ); ?>><?php _e( 'Light', 'sportscorex' ); ?></option>
            <option value="dark" <?php selected( $value, 'dark' ); ?>><?php _e( 'Dark', 'sportscorex' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Default theme for widgets. Users can override with toggle button.', 'sportscorex' ); ?></p>
        <?php
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Sanitize API keys
        if ( isset( $input['apis'] ) ) {
            foreach ( $input['apis'] as $api => $key ) {
                $sanitized['apis'][ $api ] = sanitize_text_field( $key );
            }
        }

        // Sanitize cache duration
        $sanitized['cache_duration'] = isset( $input['cache_duration'] ) ? 
            max( 60, min( 3600, intval( $input['cache_duration'] ) ) ) : 300;

        // Sanitize theme mode
        $sanitized['theme_mode'] = isset( $input['theme_mode'] ) && 
            in_array( $input['theme_mode'], array( 'auto', 'light', 'dark' ) ) ? 
            $input['theme_mode'] : 'auto';

        return $sanitized;
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_scripts( $hook ) {
        if ( strpos( $hook, 'sportscorex' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'sportscorex-admin',
            SPORTSCOREX_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SPORTSCOREX_VERSION
        );
    }
}