<?php

wp_enqueue_script('post');
include_once ABSPATH.'/wp-admin/includes/meta-boxes.php';

?>
<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php _e('WP Anniversary', 'wp-anniversary') ?></h2>
    <?php $iworks_wp_anniversary->update(); ?>
    <?php $iworks_wp_anniversary->update_meetups(); ?>
    <form method="post" action="options.php" id="iworks_wp_anniversary_admin_index">
        <div class="postbox-container" style="width:75%">
<?php

$option_name = basename( __FILE__, '.php');
$iworks_wp_anniversary_options->settings_fields( $option_name );
$iworks_wp_anniversary_options->build_options( $option_name );

$list = $iworks_wp_anniversary_options->get_option( 'list' );

?>
        </div>
        <div class="postbox-container" style="width:23%;margin-left:2%">
            <div class="metabox-holder">
                <div id="links" class="postbox">
                    <h3 class="hndle"><?php _e( 'WordPress 10th Anniversary', 'wp-anniversary' ); ?></h3>
                    <div class="inside">
                        <p><?php printf( __( 'Happening in %d communities', 'wp-anniversary' ), count( $list ) );?></p>
                        <p><?php printf( __( 'Last update: %s', 'wp-anniversary' ), $iworks_wp_anniversary->get_last_update( true ) ); ?></p>
                        <p><?php _e( 'Celebrate the 10th anniversary of the very first WordPress release in 2003!', 'wp-anniversary' ); ?></p>
                        <p><a href="http://www.meetup.com/WordPress/?see_all=1"><?php _e( 'See all WordPress 10th Anniversary communities', 'wp-anniversary'); ?></a></p>
                    </div>
                </div>

                <div id="links" class="postbox">
                    <h3 class="hndle"><?php _e( 'Loved this Plugin?', 'wp-anniversary' ); ?></h3>
                    <div class="inside">
                        <p><?php _e( 'Below are some links to help spread this plugin to other users', 'wp-anniversary' ); ?></p>
                        <ul>
                            <li><a href="http://wordpress.org/extend/plugins/wp-anniversary/"><?php _e( 'Give it a 5 star on Wordpress.org', 'wp-anniversary' ); ?></a></li>
                            <li><a href="http://wordpress.org/extend/plugins/wp-anniversary/"><?php _e( 'Link to it so others can easily find it', 'wp-anniversary' ); ?></a></li>
                        </ul>
                    </div>
                </div>

                <div id="help" class="postbox">
                    <h3 class="hndle"><?php _e( 'Need Assistance?', 'wp-anniversary' ); ?></h3>
                    <div class="inside">
                        <p><?php _e( 'Problems? The links bellow can be very helpful to you', 'wp-anniversary' ); ?></p>
                        <ul>
                            <li><a href="<?php _e( 'http://wordpress.org/tags/wp_anniversary', 'wp-anniversary' ); ?>"><?php _e( 'Wordpress Help Forum', 'wp-anniversary' ); ?></a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

