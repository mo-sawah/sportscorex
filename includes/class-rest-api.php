<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST API Class - Custom endpoints for AJAX calls
 */
class SportScoreX_REST_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route( 'sportscorex/v1', '/live-scores', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_live_scores' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'sport' => array(
                    'default' => 'football',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'league' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ) );

        register_rest_route( 'sportscorex/v1', '/standings', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_standings' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'league' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'season' => array(
                    'default' => date('Y'),
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ) );
    }

    /**
     * Check permissions for API access
     */
    public function check_permissions() {
        // Allow public access but verify nonce for authenticated requests
        if ( is_user_logged_in() ) {
            return wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'] ?? '', 'wp_rest' );
        }
        return true;
    }

    /**
     * Get live scores endpoint
     */
    public function get_live_scores( $request ) {
        $sport = $request->get_param( 'sport' );
        $league = $request->get_param( 'league' );

        $api_manager = SportScoreX::instance()->api_manager;
        $data = $api_manager->get_live_scores( $sport, $league );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Get standings endpoint
     */
    public function get_standings( $request ) {
        $league = $request->get_param( 'league' );
        $season = $request->get_param( 'season' );

        $api_manager = SportScoreX::instance()->api_manager;
        $data = $api_manager->get_standings( $league, $season );

        return new WP_REST_Response( $data, 200 );
    }
}