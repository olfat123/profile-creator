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
}
add_action( 'plugins_loaded', 'cpc_init' );