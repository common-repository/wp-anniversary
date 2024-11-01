<?php

function iworks_wp_anniversary_options()
{
    $iworks_wp_anniversary_options = array();
    $iworks_wp_anniversary_options['index'] = array(
        'use_tabs' => false,
        'version'  => '0.0',
        'options'  => array(
            array(
                'name' => 'city',
                'type' => 'select',
                'th'   => __( 'City', 'wp-anniversary' ),
                'options' => array( __( '--- select city ---', 'wp-anniversary' ) ),
                'extra_options' => 'helper_iworks_wp_anniversary_city_options',
            ),
            array(
                'name'              => 'recurrence',
                'type'              => 'radio',
                'th'                => __( 'Update meetup data', 'wp-anniversary' ),
                'default'           => 'twicedaily',
                'radio'             => array(
                    'none'          => array( 'label' => __( 'none, only on this page', 'wp-anniversary' ) ),
                    'hourly'        => array( 'label' => __( 'hourly', 'wp-anniversary' ) ),
                    'twicedaily'    => array( 'label' => __( 'two times a day', 'wp-anniversary' ), 'description' => __( 'recomended', 'wp-anniversary' ) ),
                    'daily'         => array( 'label' => __( 'daily', 'wp-anniversary' ) ),
                ),
                'sanitize_callback' => 'helper_iworks_wp_anniversary_wp_cron',

            ),
        ),
    );
    return $iworks_wp_anniversary_options;
}

function helper_iworks_wp_anniversary_city_options()
{
    global $iworks_wp_anniversary;
    return $iworks_wp_anniversary->get_city_list( false );
}
function helper_iworks_wp_anniversary_wp_cron( $value )
{
    /**
     * sanitize
     */
    if ( !preg_match( '/^(none|hourly|twicedaily|daily)$/', $value ) ) {
        $value = 'twicedaily';
    }
    /**
     * WP Cron
     */
    wp_clear_scheduled_hook( 'wp_anniversary_event' );
    if ( preg_match( '/^(hourly|twicedaily|daily)$/', $value ) ) {
        wp_schedule_event( time(), $value, 'wp_anniversary_event' );
    }
    /**
     * return
     */
    return $value;
}

