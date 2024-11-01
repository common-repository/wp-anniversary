<?php
/**
 * require
 */
require_once dirname( dirname( __FILE__ )).'/etc/options.php';
require_once dirname( __FILE__ ).'/class-iworks-options.php';
require_once dirname( __FILE__ ).'/class-iworks-wp-anniversary.php';
require_once dirname( __FILE__ ).'/class-iworks-wp-anniversary-widget.php';
require_once dirname( __FILE__ ).'/class-iworks-wp-anniversary-meetup-widget.php';

/**
 * i18n
 */
load_plugin_textdomain( 'wp-anniversary', false, dirname( dirname( plugin_basename( __FILE__) ) ).'/languages' );

/**
 * load options
 */
$iworks_wp_anniversary_options = new IworksOptions();
$iworks_wp_anniversary_options->set_option_function_name( 'iworks_wp_anniversary_options' );
$iworks_wp_anniversary_options->set_option_prefix( IWORKS_ANNIVERSARY_PREFIX );

Function iworks_wp_anniversary_options_init()
{
    global $iworks_wp_anniversary_options;
    $iworks_wp_anniversary_options->options_init();
}

function iworks_wp_anniversary_activate()
{
    $iworks_wp_anniversary_options = new IworksOptions();
    $iworks_wp_anniversary_options->set_option_function_name( 'iworks_wp_anniversary_options' );
    $iworks_wp_anniversary_options->set_option_prefix( IWORKS_ANNIVERSARY_PREFIX );
    $iworks_wp_anniversary_options->activate();

    /**
     * WP Cron
     */
    $recurrence = $iworks_wp_anniversary_options->get_option( 'recurrence' );
    wp_schedule_event( time(), $recurrence, 'wp_anniversary_event' );
}

function iworks_wp_anniversary_deactivate()
{
    global $iworks_wp_anniversary_options;
    $iworks_wp_anniversary_options->deactivate();

    /**
     * WP Cron
     */
    wp_clear_scheduled_hook( 'wp_anniversary_event' );
}


