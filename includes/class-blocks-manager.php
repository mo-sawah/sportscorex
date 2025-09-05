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
     * Register all blocks - FIXED VERSION
     */
    public function register_blocks() {
        // Only register blocks if the directories exist
        // This prevents the namespace error until we create the actual block files
        
        $blocks_path = SPORTSCOREX_PLUGIN_PATH . 'blocks/';
        
        // Check if blocks directory exists
        if ( ! is_dir( $blocks_path ) ) {
            return; // Skip block registration if directory doesn't exist
        }

        // Live Scores Block
        if ( file_exists( $blocks_path . 'live-scores/block.json' ) ) {
            register_block_type( $blocks_path . 'live-scores/block.json' );
        }
        
        // Standings Block  
        if ( file_exists( $blocks_path . 'standings/block.json' ) ) {
            register_block_type( $blocks_path . 'standings/block.json' );
        }
        
        // Player Stats Block
        if ( file_exists( $blocks_path . 'player-stats/block.json' ) ) {
            register_block_type( $blocks_path . 'player-stats/block.json' );
        }

        // Alternative: Register blocks programmatically (no JSON files needed)
        $this->register_programmatic_blocks();
    }

    /**
     * Register blocks programmatically (alternative method)
     */
    private function register_programmatic_blocks() {
        // Live Scores Block
        register_block_type( 'sportscorex/live-scores', array(
            'api_version' => 2,
            'title' => __( 'Live Scores', 'sportscorex' ),
            'description' => __( 'Display live sports scores with real-time updates', 'sportscorex' ),
            'category' => 'widgets',
            'icon' => 'chart-line',
            'keywords' => array( 'sports', 'scores', 'live', 'football' ),
            'supports' => array(
                'html' => false,
                'color' => array(
                    'background' => true,
                    'text' => true,
                    'gradients' => true
                ),
                'spacing' => array(
                    'padding' => true,
                    'margin' => true
                )
            ),
            'attributes' => array(
                'sport' => array(
                    'type' => 'string',
                    'default' => 'football'
                ),
                'league' => array(
                    'type' => 'string', 
                    'default' => ''
                ),
                'theme' => array(
                    'type' => 'string',
                    'default' => 'auto'
                ),
                'refreshInterval' => array(
                    'type' => 'number',
                    'default' => 30
                )
            ),
            'render_callback' => array( $this, 'render_live_scores_block' )
        ) );

        // Standings Block
        register_block_type( 'sportscorex/standings', array(
            'api_version' => 2,
            'title' => __( 'League Standings', 'sportscorex' ),
            'description' => __( 'Display league standings table', 'sportscorex' ),
            'category' => 'widgets',
            'icon' => 'list-view',
            'keywords' => array( 'sports', 'standings', 'league', 'table' ),
            'attributes' => array(
                'league' => array(
                    'type' => 'string',
                    'default' => '39'
                ),
                'season' => array(
                    'type' => 'string',
                    'default' => date( 'Y' )
                ),
                'theme' => array(
                    'type' => 'string',
                    'default' => 'auto'
                )
            ),
            'render_callback' => array( $this, 'render_standings_block' )
        ) );
    }

    /**
     * Render live scores block
     */
    public function render_live_scores_block( $attributes ) {
        $widget_id = 'block-' . uniqid();
        
        $sport = $attributes['sport'] ?? 'football';
        $league = $attributes['league'] ?? '';
        $refresh = $attributes['refreshInterval'] ?? 30;

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $widget_id ); ?>" 
             class="sportscorex-widget sportscorex-live-scores-widget"
             data-sport="<?php echo esc_attr( $sport ); ?>"
             data-league="<?php echo esc_attr( $league ); ?>"
             data-refresh-interval="<?php echo esc_attr( $refresh ); ?>">
            
            <div class="sportscorex-widget-header">
                <h3 class="sportscorex-widget-title">
                    <?php echo esc_html( sprintf( __( 'Live %s Scores', 'sportscorex' ), ucfirst( $sport ) ) ); ?>
                </h3>
                <button class="sportscorex-theme-toggle" title="<?php _e( 'Toggle theme', 'sportscorex' ); ?>">
                    🌓
                </button>
            </div>

            <div class="sportscorex-live-scores">
                <div class="sportscorex-loading">
                    <div class="sportscorex-spinner"></div>
                    <div class="sportscorex-loading-text"><?php _e( 'Loading live scores...', 'sportscorex' ); ?></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render standings block
     */
    public function render_standings_block( $attributes ) {
        $league = $attributes['league'] ?? '39';
        $season = $attributes['season'] ?? date( 'Y' );

        // Get standings data
        $api_manager = SportScoreX::instance()->api_manager;
        $standings = $api_manager->get_standings( $league, $season );

        if ( empty( $standings ) ) {
            return '<p>' . __( 'No standings data available.', 'sportscorex' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="sportscorex-widget sportscorex-standings-widget">
            <div class="sportscorex-widget-header">
                <h3 class="sportscorex-widget-title">
                    <?php _e( 'League Standings', 'sportscorex' ); ?>
                </h3>
                <button class="sportscorex-theme-toggle" title="<?php _e( 'Toggle theme', 'sportscorex' ); ?>">
                    🌓
                </button>
            </div>

            <div class="sportscorex-standings">
                <table class="sportscorex-standings-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Pos', 'sportscorex' ); ?></th>
                            <th><?php _e( 'Team', 'sportscorex' ); ?></th>
                            <th><?php _e( 'P', 'sportscorex' ); ?></th>
                            <th><?php _e( 'W', 'sportscorex' ); ?></th>
                            <th><?php _e( 'D', 'sportscorex' ); ?></th>
                            <th><?php _e( 'L', 'sportscorex' ); ?></th>
                            <th><?php _e( 'GF', 'sportscorex' ); ?></th>
                            <th><?php _e( 'GA', 'sportscorex' ); ?></th>
                            <th><?php _e( 'GD', 'sportscorex' ); ?></th>
                            <th><?php _e( 'Pts', 'sportscorex' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $standings as $team ) : ?>
                            <tr>
                                <td><?php echo esc_html( $team['rank'] ); ?></td>
                                <td>
                                    <?php if ( ! empty( $team['logo'] ) ) : ?>
                                        <img src="<?php echo esc_url( $team['logo'] ); ?>" class="sportscorex-team-logo" alt="">
                                    <?php endif; ?>
                                    <?php echo esc_html( $team['team'] ); ?>
                                </td>
                                <td><?php echo esc_html( $team['played'] ); ?></td>
                                <td><?php echo esc_html( $team['won'] ); ?></td>
                                <td><?php echo esc_html( $team['drawn'] ); ?></td>
                                <td><?php echo esc_html( $team['lost'] ); ?></td>
                                <td><?php echo esc_html( $team['goals_for'] ); ?></td>
                                <td><?php echo esc_html( $team['goals_against'] ); ?></td>
                                <td><?php echo esc_html( $team['goal_difference'] ); ?></td>
                                <td><strong><?php echo esc_html( $team['points'] ); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_assets() {
        // Only enqueue if the JS file exists
        $blocks_js = SPORTSCOREX_PLUGIN_URL . 'build/blocks.js';
        
        if ( file_exists( SPORTSCOREX_PLUGIN_PATH . 'build/blocks.js' ) ) {
            wp_enqueue_script(
                'sportscorex-blocks-js',
                $blocks_js,
                array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data' ),
                SPORTSCOREX_VERSION,
                true
            );
        }
    }
}