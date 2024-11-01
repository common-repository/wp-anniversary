<?php

/*

Copyright 2013 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'WPINC' ) ) {
    die;
}

if ( class_exists( 'iWorks_WP_Anniversary' ) ) {
    return;
}

class iWorks_WP_Anniversary
{
    private static $version;
    private static $dir;
    private static $base;
    private static $capability;
    private $options;
    private $working_mode;
    private $dev;
    public static $see_all;

    public function __construct()
    {
        $this->see_all = 'http://www.meetup.com/WordPress/?see_all=1';
        /**
         * static settings
         */
        $this->version           = '1.0';
        $this->base              = dirname( __FILE__ );
        $this->dir               = basename( dirname( $this->base ) );
        $this->capability        = apply_filters( 'iworks_wp_anniversary_capability', 'manage_options' );
        $this->dev               = ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE )? '.dev':'';
        /**
         * generate
         */
        add_action( 'init',                 array( &$this, 'init' ) );
        add_action( 'after_setup_theme',    array( &$this, 'after_setup_theme'  ) );
        add_action( 'wp_anniversary_event', array( &$this, 'update_meetups' ) );
        /**
         * global option object
         */
        global $iworks_wp_anniversary_options;
        if ( 'object' == gettype( $iworks_wp_anniversary_options ) ) {
            $this->options = $iworks_wp_anniversary_options;
            $this->options->set_option_prefix( 'wp-anniversary-' );
        }
    }

    public function after_setup_theme()
    {
    }

    public function get_version( $file = null )
    {
        if ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE ) {
            if ( null != $file ) {
                $file = dirname( dirname ( __FILE__ ) ) . $file;
                return md5_file( $file );
            }
            return rand( 0, 99999 );
        }
        return $this->version;
    }

    public function init()
    {
        add_action( 'admin_enqueue_scripts',      array( &$this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_init',                 array( &$this, 'admin_init'         ) );
        add_action( 'admin_init',                 'iworks_wp_anniversary_options_init' );
        add_action( 'admin_menu',                 array( &$this, 'admin_menu'         ) );
        add_action( 'wp_print_scripts',           array( &$this, 'wp_print_scripts'   ) );
        /**
         * cities translation
         */
        $locale = get_locale();
        $mofile = WP_LANG_DIR . '/continents-cities-' . $locale . '.mo';
        load_textdomain( 'continents-cities', $mofile );
    }

    public function admin_enqueue_scripts()
    {
        $screen = get_current_screen();
        if ( isset( $screen->id ) && $this->dir.'/admin/index' == $screen->id ) {
            /**
             * make help
             */
            $help = '<p>' .  __( 'WP Anniversary', 'wp-anniversary' ) . '</p>';
            $screen->add_help_tab( array(
                'id'      => 'overview',
                'title'   => __( 'Overview', 'wp-anniversary' ),
                'content' => $help,
            ) );
            unset( $help );

            /**
             * make sidebar help
             */
            $screen->set_help_sidebar(
                '<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
                '<p>' . __( '<a href="http://wordpress.org/extend/plugins/wp-anniversary/" target="_blank">Plugin Homepage</a>', 'wp-anniversary' ) . '</p>' .
                '<p>' . __( '<a href="http://wordpress.org/tags/wp-anniversary" target="_blank">Support Forums</a>', 'wp-anniversary' ) . '</p>' .
                '<p>' . __( '<a href="http://iworks.pl/en/" target="_blank">break the web</a>', 'wp-anniversary' ) . '</p>'
            );
        }
    }
    /**
     * Add page to theme menu
     */
    public function admin_menu()
    {
        add_options_page( __( 'WP Anniversary', 'wp-anniversary' ), __( 'WP Anniversary', 'wp-anniversary' ), $this->capability, $this->dir.'/admin/index.php' );
    }

    public function admin_init()
    {
        add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
        $last_update = $this->get_last_update();
        if ( empty( $last_update ) ) {
            $this->options->notices[] = sprintf(
                __( 'Please finish plugin configuration! Go to <a href="%s">Settings -&gt; WP Anniversary</a> to obtain cites from meetup.com.', 'wp-anniversary' ),
                admin_url( 'options-general.php?page=wp-anniversary/admin/index.php' )
            );
        }
    }

    public function plugin_row_meta( $links, $file )
    {
        if ( $this->dir.'/wp_anniversary.php' == $file ) {
            if ( !is_multisite() && current_user_can( $this->capability ) ) {
                $links[] = '<a href="themes.php?page='.$this->dir.'/admin/index.php">' . __( 'Settings' ) . '</a>';
            }
            if ( !$this->is_pro ) {
                $links[] = '<a href="http://iworks.pl/donate/wp_anniversary.php">' . __( 'Donate' ) . '</a>';
            }
        }
        return $links;
    }


    public function wp_print_scripts()
    {
    }

    public function update()
    {
        $version = $this->options->get_option( 'version' );
        if ( version_compare( $this->version, $version, '>' ) ) {
            $this->options->update_option( 'version', $this->version );
        }
    }

    private function enqueue_style( $name, $deps = null )
    {
        $file = '/styles/'.$name.$this->dev.'.css';
        wp_enqueue_style ( $name, plugins_url( $file, $this->base ), $deps, $this->get_version( $file ) );
    }

    public function update_meetups()
    {
        $last_update = $this->get_last_update();
        if ( 'first-update' == $last_update ) {
            $this->options->add_option( 'list', null );
        }
        if ( strtotime( date('Y-m-d' ) ) - strtotime( $last_update ) > 60 * 60 ) {
            $this->options->update_option( 'last_update', 0 );
            require_once ABSPATH.WPINC.'/http.php';
            $response = wp_remote_get( $this->see_all );
            if ( is_wp_error( $response ) ) {
                echo '<div class="error">';
                printf( __( "<p>Unable to connect to meetup.com server at <a href='%s' target='_blank'>this URL</a>.</p>", 'wp-anniversary'  ), $url );
                foreach ( $response->errors as $error ) {
                    foreach ( $error as $message ) {
                        printf( '<p>%s</p>', $message );
                    }
                }
                echo '</div>';
            } else {
                if ( isset( $response['body'] ) && $response['body'] ) {
                    if ( preg_match( '/"farMeetupList"/', $response['body'] ) ) {

                        $html = $response['body'];

                        /**
                         * split body
                         */

                        $html = preg_split( '/<[^>]+id="farMeetupList"[^>]+>/', $html );
                        $html = $html[1];

                        $html = preg_split( '/<[^>]+id="containerSidebar"[^>]+>/', $html );
                        $html = $html[0];

                        /**
                         * remove unnecessary items
                         */

                        $html = preg_replace( '/<img[^>]+>/', '', $html );
                        $html = preg_replace( '/<(span)[^>]*>[^<]+<\/(span|p)>/', '', $html );
                        $html = preg_replace( '/<\/?(div|h3|p)[^>]*>/', '', $html );

                        /**
                         * remove unnecessary chars
                         */
                        $html = preg_replace( '/[\n\r\t ]+/', " ", $html );
                        /**
                         * split data to array
                         */
                        $html = preg_split( '/<li[^>]+>/', $html );
                        $data = array();
                        foreach ( $html as $one ) {
                            if ( !preg_match( '@<a href="([^"]+)">([^<]+)</a> (<strong>)?(\d+)?@', $one, $matches ) ) {
                                continue;
                            }
                            $data[ $matches[2]] = array(
                                'url' => $matches[1],
                                'count' => isset( $matches[4] )? $matches[4] : 0
                            );
                        }
                        $this->options->notices[] = __( 'Meetups list sync successful.', 'wp-anniversary' );
                        $this->options->update_option( 'list', $data );
                        $this->options->update_option( 'last_update', date('c') );

                    } else {
                        $error = json_decode( $response['body'] );
                        echo '<div class="error">';
                        echo '<p><span class="slight">' . sprintf(__('Error: %s. %s', 'wp-anniversary' ), $error->error->code, $error->error->message ) . '</span></p>';
                        echo '</div>';
                    }
                }
            }
        }
    }

    public function get_city_list( $raw = false )
    {
        $data = $this->options->get_option( 'list' );
        if ( is_array( $data ) && count( $data ) ) {
            if ( $raw ) {
                return $data;
            }
            $data = array_keys( $data );
            asort( $data );
            $new = array();

            foreach( $data as $one ) {
                $new[ $one ] = __( $one, 'continents-cities' );
            }
            /**
             * return
             */
            return $new;
        }
        return array();
    }

    public function get_last_update( $format = false )
    {
        $date = $this->options->get_option( 'last_update', 'first-update' );
        if ( $format ) {
            $date = preg_replace( '/T/', ' ', $date );
            $date = preg_replace( '/:\d+\+.+/', '', $date );
        }
        return $date;
    }

}

