<?php
/*
Plugin Name: SportScoreX - Ultimate Sports Stats & Widgets
Description: Modern sports statistics plugin with live scores, widgets, and comprehensive coverage. Built for 2025 with latest WordPress standards.
Version: 2.0.1
Author: mo-sawah
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 8.0
Network: false
License: GPL v2 or later
Text Domain: sportscorex
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'SPORTSCOREX_VERSION', '2.0.1' );
define( 'SPORTSCOREX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPORTSCOREX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SPORTSCOREX_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Main plugin class
require_once SPORTSCOREX_PLUGIN_PATH . 'includes/class-sportscorex.php';

/**
 * Initialize the plugin
 */
function sportscorex_init() {
    return SportScoreX::instance();
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', 'sportscorex_init' );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, array( 'SportScoreX', 'activate' ) );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, array( 'SportScoreX', 'deactivate' ) );