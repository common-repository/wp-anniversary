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

class iWorks_WP_Anniversary_Widget extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'wp-anniversary',
            __( 'WP Anniversary', 'wp-anniversary' ),
            array(
                'description' => __( 'Celebrate the 10th anniversary of the very first WordPress release in 2003!', 'wp-anniversary' ),
            )
        );
        $this->keys = array( 'title', 'city', 'logo' );
        $this->dimentions = array(
            'none' => __( 'none', 'wp-anniversary' ),
            'wide' => __( 'wide', 'wp-anniversary' ),
            'square' => __( 'square', 'wp-anniversary' )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance )
    {
        extract( $args );

        echo $before_widget;
        if ( !empty( $instance['title'] ) ) {
            $title = apply_filters( 'widget_title', $instance['title'] );
            echo $before_title . $title . $after_title;
        }
        switch( $instance['logo'] ) {
        case 'wide':
            printf( '<img src="%s" style="max-width:100%%;display:block;margin-bottom:10px;" alt="%s" />', plugins_url( 'images/10th.png', dirname( __FILE__ ) ), __( 'WordPress 10th Anniversary', 'wp-anniversary' ) );
            break;
        case 'square':
            printf( '<img src="%s" style="max-width:160px;display:block;margin:0 auto 10px auto;width: 100%%;" alt="%s" />', plugins_url( 'images/10th-square.png', dirname( __FILE__ ) ), __( 'WordPress 10th Anniversary', 'wp-anniversary' ) );
            break;
        }
        global $iworks_wp_anniversary;
        $list = $iworks_wp_anniversary->get_city_list( true );
        echo '<p>';
        if ( !empty( $instance['city'] ) && isset( $list[$instance['city']] ) ) {
            $place = sprintf(
                '<a href="%s">%s</a>',
                $list[$instance['city']]['url'],
                __( $instance['city'], 'continents-cities' )
            );

            printf(
                __( 'Celebrate the 10th anniversary in %1$s with <strong>%2$d</strong> WordPress Enthusiasts.', 'wp-anniversary' ),
                $place,
                $list[$instance['city']]['count']
            );
        } else {
            __( 'Celebrate the 10th anniversary of the very first WordPress release in 2003!', 'wp-anniversary' );
            echo '<br />';
            printf(
                '<a href="%s">%s</(a>',
                $iworks_wp_anniversary->see_all,
                __( 'Find a cool place that people can meet up and hang out to celebrate.', 'wp-anniversary' )
            );
        }
        echo '</p>';
        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance )
    {
        $instance = array();
        foreach( $this->keys as $key ) {
            $callback = 'sanitize_'.$key;
            if ( function_exists( array( $this, $callback ) ) ) {
                $instance[ $key ] = $this->$callback( $new_instance[ $key ] );
            } else {
                $instance[ $key ] = strip_tags( $new_instance[ $key ] );
            }
        }
        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance )
    {
        /**
         * title
         */
        $title = __( 'WordPress 10th Anniversary', 'wp-anniversary' );
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        /**
         * city
         */
        $city = '';
        if ( isset( $instance[ 'city' ] ) ) {
            $city = $instance[ 'city' ];
        }
        /**
         * logo
         */
        $logo = 'none';
        if ( isset( $instance[ 'logo' ] ) ) {
            $logo = $instance[ 'logo' ];
        }
        $logo = $this->sanitize_logo( $logo );
        /**
         * get list
         */
        $list = helper_iworks_wp_anniversary_city_options();
?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'logo' ); ?>"><?php _e( 'Logo:' ); ?></label>
            <ul>
<?php
        foreach( $this->dimentions as $dimention => $label ) {
            $id = 'iworks_'.crc32( $this->get_field_id( 'logo' ) . $dimention );
            printf(
                '<li><input name="%s" type="radio" value="%s" id="%s" %s /><label for="%s"> %s</label></li>',
                $this->get_field_name( 'logo' ),
                $dimention,
                $id,
                checked( $dimention, $logo, false ),
                $id,
                $label
            );
        }
?>
        </ul>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City:', 'wp-anniversary' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>">
                <option value="0"><?php _e( '--- select city ---', 'wp-anniversary' ); ?></option>
<?php
        foreach( $list as $key => $value ) {
            printf(
                '<option value="%s" %s>%s</option>',
                $key,
                ( $key == $city )? 'selected="selected"':'',
                $value
            );
        }
?>
            </select>
        </p>
<?php
    }

    /**
     * Sanitize logo
     */

    private function sanitize_logo( $logo )
    {
        if ( in_array( $logo, $this->dimentions ) ) {
            return $logo;
        }
        return 'none';
    }

} // class Foo_Widget

add_action( 'widgets_init', create_function( '', 'register_widget( "iWorks_WP_Anniversary_Widget" );' ) );

