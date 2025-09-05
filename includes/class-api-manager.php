<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API Manager Class - Handles multiple sports APIs with fallback
 */
class SportScoreX_API_Manager {

    private $apis = array();
    private $settings;

    public function __construct() {
        $this->settings = get_option( 'sportscorex_settings', array() );
        $this->init_apis();
    }

    /**
     * Initialize available APIs
     */
    private function init_apis() {
        // API-Sports (Primary)
        if ( ! empty( $this->settings['apis']['api_sports'] ) ) {
            $this->apis['api_sports'] = array(
                'key' => $this->settings['apis']['api_sports'],
                'base_url' => 'https://v3.football.api-sports.io/',
                'headers' => array(
                    'x-rapidapi-key' => $this->settings['apis']['api_sports'],
                    'x-rapidapi-host' => 'v3.football.api-sports.io'
                )
            );
        }

        // TheSportsDB (Fallback)
        $this->apis['thesportsdb'] = array(
            'key' => $this->settings['apis']['thesportsdb'] ?? '3',
            'base_url' => 'https://www.thesportsdb.com/api/v1/json/',
            'headers' => array()
        );

        // Football-API (Free with real-time)
        if ( ! empty( $this->settings['apis']['football_api'] ) ) {
            $this->apis['football_api'] = array(
                'key' => $this->settings['apis']['football_api'],
                'base_url' => 'https://api.football-api.com/v1/',
                'headers' => array(
                    'X-Auth-Token' => $this->settings['apis']['football_api']
                )
            );
        }
    }

    /**
     * Get live scores with API fallback
     */
    public function get_live_scores( $sport = 'football', $league = null ) {
        $cache_key = 'sportscorex_live_' . md5( $sport . $league );
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $data = array();

        // Try API-Sports first
        if ( isset( $this->apis['api_sports'] ) ) {
            $data = $this->fetch_api_sports_live( $sport, $league );
        }

        // Fallback to TheSportsDB
        if ( empty( $data ) && isset( $this->apis['thesportsdb'] ) ) {
            $data = $this->fetch_thesportsdb_live( $sport );
        }

        // Cache for 2 minutes for live data
        set_transient( $cache_key, $data, 120 );

        return $data;
    }

    /**
     * Fetch from API-Sports
     */
    private function fetch_api_sports_live( $sport, $league ) {
        $url = $this->apis['api_sports']['base_url'] . 'fixtures?live=all';
        
        if ( $league ) {
            $url .= '&league=' . $league;
        }

        $response = wp_remote_get( $url, array(
            'headers' => $this->apis['api_sports']['headers'],
            'timeout' => 15
        ) );

        if ( is_wp_error( $response ) ) {
            return array();
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $this->normalize_api_sports_data( $data );
    }

    /**
     * Fetch from TheSportsDB
     */
    private function fetch_thesportsdb_live( $sport ) {
        $sport_map = array(
            'football' => 'Soccer',
            'basketball' => 'Basketball',
            'tennis' => 'Tennis'
        );

        $sport_name = $sport_map[ $sport ] ?? 'Soccer';
        $key = $this->apis['thesportsdb']['key'];
        $url = $this->apis['thesportsdb']['base_url'] . $key . '/livescore.php?s=' . $sport_name;

        $response = wp_remote_get( $url, array( 'timeout' => 15 ) );

        if ( is_wp_error( $response ) ) {
            return array();
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $this->normalize_thesportsdb_data( $data );
    }

    /**
     * Normalize API-Sports data format
     */
    private function normalize_api_sports_data( $data ) {
        if ( empty( $data['response'] ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $data['response'] as $fixture ) {
            $normalized[] = array(
                'id' => $fixture['fixture']['id'],
                'home_team' => $fixture['teams']['home']['name'],
                'away_team' => $fixture['teams']['away']['name'],
                'home_score' => $fixture['goals']['home'] ?? 0,
                'away_score' => $fixture['goals']['away'] ?? 0,
                'status' => $fixture['fixture']['status']['short'],
                'time' => $fixture['fixture']['status']['elapsed'] ?? 0,
                'league' => $fixture['league']['name'],
                'date' => $fixture['fixture']['date']
            );
        }

        return $normalized;
    }

    /**
     * Normalize TheSportsDB data format
     */
    private function normalize_thesportsdb_data( $data ) {
        if ( empty( $data['events'] ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $data['events'] as $event ) {
            $normalized[] = array(
                'id' => $event['idEvent'],
                'home_team' => $event['strHomeTeam'],
                'away_team' => $event['strAwayTeam'],
                'home_score' => $event['intHomeScore'] ?? 0,
                'away_score' => $event['intAwayScore'] ?? 0,
                'status' => $event['strStatus'] ?? 'Live',
                'time' => $event['strProgress'] ?? '',
                'league' => $event['strLeague'],
                'date' => $event['dateEvent']
            );
        }

        return $normalized;
    }

    /**
     * Get team standings
     */
    public function get_standings( $league_id, $season = null ) {
        $cache_key = 'sportscorex_standings_' . md5( $league_id . $season );
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $data = array();

        if ( isset( $this->apis['api_sports'] ) ) {
            $url = $this->apis['api_sports']['base_url'] . 'standings?league=' . $league_id;
            if ( $season ) {
                $url .= '&season=' . $season;
            }

            $response = wp_remote_get( $url, array(
                'headers' => $this->apis['api_sports']['headers'],
                'timeout' => 15
            ) );

            if ( ! is_wp_error( $response ) ) {
                $body = wp_remote_retrieve_body( $response );
                $raw_data = json_decode( $body, true );
                $data = $this->normalize_standings_data( $raw_data );
            }
        }

        // Cache standings for 1 hour
        set_transient( $cache_key, $data, 3600 );

        return $data;
    }

    /**
     * Normalize standings data
     */
    private function normalize_standings_data( $data ) {
        if ( empty( $data['response'][0]['league']['standings'][0] ) ) {
            return array();
        }

        $standings = array();
        foreach ( $data['response'][0]['league']['standings'][0] as $team ) {
            $standings[] = array(
                'rank' => $team['rank'],
                'team' => $team['team']['name'],
                'logo' => $team['team']['logo'],
                'points' => $team['points'],
                'played' => $team['all']['played'],
                'won' => $team['all']['win'],
                'drawn' => $team['all']['draw'],
                'lost' => $team['all']['lose'],
                'goals_for' => $team['all']['goals']['for'],
                'goals_against' => $team['all']['goals']['against'],
                'goal_difference' => $team['goalsDiff']
            );
        }

        return $standings;
    }
}