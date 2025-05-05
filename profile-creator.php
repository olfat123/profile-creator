<?php
/**
 * Plugin Name: Profile Creator
 * Description: Creates profiles for multiple types
 * Version: 1.0.0
 * Author: Olfat Hakeem
 * License: GPL-2.0+
 * Text Domain: profile-creator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

use ProfileCreator\ProfileCreator;

function cpc_init() {
    $plugin = new ProfileCreator();
    $plugin->init();
    if ( ! defined( 'PROFILE_CREATOR_PLUGIN_FILE' ) ) {
        define( 'PROFILE_CREATOR_PLUGIN_FILE', __FILE__ );
    }

    if ( ! defined( 'PROFILE_CREATOR_PLUGIN_DIR_URL' ) ) {
        define( 'PROFILE_CREATOR_PLUGIN_DIR_URL', untrailingslashit( plugins_url( '/', PROFILE_CREATOR_PLUGIN_FILE ) ) );
    }

    if ( ! defined( 'PROFILE_CREATOR_PLUGIN_DIR_PATH' ) ) {
        define( 'PROFILE_CREATOR_PLUGIN_DIR_PATH', untrailingslashit( plugin_dir_path( PROFILE_CREATOR_PLUGIN_FILE ) ) );
    }
    
    if ( ! defined( 'PROFILE_CREATOR_PLUGIN_VERSION' ) ) {
        define( 'PROFILE_CREATOR_PLUGIN_VERSION', '1.0.0' );
    }
}
add_action( 'plugins_loaded', 'cpc_init' );