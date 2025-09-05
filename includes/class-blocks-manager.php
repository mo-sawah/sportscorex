<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blocks Manager - Fixed Version
 */
class SportScoreX_Blocks_Manager {

    private static $blocks_registered = false;

    public function __construct() {
        add_action( 'init', array( $this, 'register_blocks' ), 10 );
    }

    /**
     * Register all blocks - FIXED to prevent double registration
     */
    public function register_blocks() {
        // Prevent double registration
        if ( self::$blocks_registered ) {
            return;
        }

        // Only register if we're in admin or block editor
        if ( ! is_admin() && ! wp_is_json_request() ) {
            return;
        }

        // Live Scores Block - Register only once
        if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'sportscorex/live-scores' ) ) {
            register_block_type( 'sportscorex/live-scores', array(
                'api_version' => 2,
                'title' => __( 'Live Scores', 'sportscorex' ),
                'description' => __( 'Display live sports scores', 'sportscorex' ),
                'category' => 'widgets',
                'icon' => 'chart-line',
                'attributes' => array(
                    'sport' => array(
                        'type' => 'string',
                        'default' => 'football'
                    ),
                    'league' => array(
                        'type' => 'string',
                        'default' => ''
                    )
                ),
                'render_callback' => array( $this, 'render_live_scores_block' )
            ) );
        }

        // Standings Block
        if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'sportscorex/standings' ) ) {
            register_block_type( 'sportscorex/standings', array(
                'api_version' => 2,
                'title' => __( 'League Standings', 'sportscorex' ),
                'description' => __( 'Display league standings', 'sportscorex' ),
                'category' => 'widgets',
                'icon' => 'list-view',
                'attributes' => array(
                    'league' => array(
                        'type' => 'string',
                        'default' => '39'
                    )
                ),
                'render_callback' => array( $this, 'render_standings_block' )
            ) );
        }

        self::$blocks_registered = true;
    }

    /**
     * Render live scores block
     */
    public function render_live_scores_block( $attributes ) {
        $widget_id = 'block-' . uniqid();
        $sport = $attributes['sport'] ?? 'football';
        $league = $attributes['league'] ?? '';

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $widget_id ); ?>" 
             class="sportscorex-widget sportscorex-live-scores-widget"
             data-sport="<?php echo esc_attr( $sport ); ?>"
             data-league="<?php echo esc_attr( $league ); ?>"
             data-refresh-interval="30">
            
            <div class="sportscorex-widget-header">
                <h3 class="sportscorex-widget-title">
                    <?php echo esc_html( sprintf( __( 'Live %s Scores', 'sportscorex' ), ucfirst( $sport ) ) ); ?>
                </h3>
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
        return '<div class="sportscorex-widget"><p>' . __( 'Standings widget coming soon!', 'sportscorex' ) . '</p></div>';
    }
}