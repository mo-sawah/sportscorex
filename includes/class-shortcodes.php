<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcodes Class - Legacy support for theme compatibility
 */
class SportScoreX_Shortcodes {

    public function __construct() {
        add_shortcode( 'sportscorex_live_scores', array( $this, 'live_scores_shortcode' ) );
        add_shortcode( 'sportscorex_standings', array( $this, 'standings_shortcode' ) );
    }

    /**
     * Live scores shortcode
     */
    public function live_scores_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'sport' => 'football',
            'league' => '',
            'theme' => 'auto',
            'refresh' => '30'
        ), $atts, 'sportscorex_live_scores' );

        $widget_id = 'shortcode-' . uniqid();

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $widget_id ); ?>" 
             class="sportscorex-widget sportscorex-live-scores-widget"
             data-sport="<?php echo esc_attr( $atts['sport'] ); ?>"
             data-league="<?php echo esc_attr( $atts['league'] ); ?>"
             data-refresh-interval="<?php echo esc_attr( $atts['refresh'] ); ?>">
            
            <div class="sportscorex-widget-header">
                <h3 class="sportscorex-widget-title">
                    <?php echo esc_html( sprintf( __( 'Live %s Scores', 'sportscorex' ), ucfirst( $atts['sport'] ) ) ); ?>
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
     * Standings shortcode
     */
    public function standings_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'league' => '39', // Premier League ID
            'season' => date( 'Y' ),
            'theme' => 'auto'
        ), $atts, 'sportscorex_standings' );

        $api_manager = SportScoreX::instance()->api_manager;
        $standings = $api_manager->get_standings( $atts['league'], $atts['season'] );

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
}