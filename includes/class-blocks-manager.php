<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blocks Manager - 2025 WordPress Block Standards
 */
class SportScoreX_Blocks_Manager {

    public function __construct() {
        add_action( 'init', array( $this, 'register_blocks' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_assets' ) );
    }

    /**
     * Register all blocks
     */
    public function register_blocks() {
        // Live Scores Block
        register_block_type( SPORTSCOREX_PLUGIN_PATH . 'blocks/live-scores/block.json' );
        
        // Standings Block
        register_block_type( SPORTSCOREX_PLUGIN_PATH . 'blocks/standings/block.json' );
        
        // Player Stats Block
        register_block_type( SPORTSCOREX_PLUGIN_PATH . 'blocks/player-stats/block.json' );
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_assets() {
        wp_enqueue_script(
            'sportscorex-blocks-js',
            SPORTSCOREX_PLUGIN_URL . 'build/blocks.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data' ),
            SPORTSCOREX_VERSION,
            true
        );
    }
}