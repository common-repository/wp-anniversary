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

class iWorks_WP_Anniversary_Meetup_City_List_Widget extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'wp-anniversary-city-list',
            __( 'WP Anniversary - City List', 'wp-anniversary' ),
            array(
                'description' => __( 'Celebrate the 10th anniversary of the very first WordPress release in 2003! Show city list.', 'wp-anniversary' ),
            )
        );
        $this->keys = array( 'width', 'bg' );
        $this->dimentions = array(
            'light' => __( 'light', 'wp-anniversary' ),
            'dark' => __( 'dark', 'wp-anniversary' ),
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
        printf(
            '<iframe src="http://www.meetup.com/everywhere/widget3/WordPress?width=%d&amp;bg=%s&amp;ref=%s" width="%d" height="475" frameborder="0" border="0" allowtransparency="true" scrolling="no"></iframe>',
            $this->sanitize_width( $instance['width'] ),
            $this->sanitize_bg( $instance['bg'] ),
            urlencode( home_url('/') ),
            $this->sanitize_width( $instance['width'] )
        );
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
        $title = __( 'WordPress 10th Anniversary - City List', 'wp-anniversary' );
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        /**
         * width
         */
        $width = 'none';
        if ( isset( $instance[ 'width' ] ) ) {
            $width = $instance[ 'width' ];
        }
        $width = $this->sanitize_width( $width );
        /**
         * bg
         */
        $bg = 'none';
        if ( isset( $instance[ 'bg' ] ) ) {
            $bg = $instance[ 'bg' ];
        }
        $bg = $this->sanitize_bg( $bg );
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
            <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:', 'wp-anniversary' ); ?></label>
            <input class="small-text" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="number" min="100" value="<?php echo esc_attr( $width ); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'bg' ); ?>"><?php _e( 'Background:', 'wp-anniversary' ); ?></label>
            <ul>
<?php
        foreach( $this->dimentions as $dimention => $label ) {
            $id = 'iworks_'.crc32( $this->get_field_id( 'bg' ) . $dimention );
            printf(
                '<li><input name="%s" type="radio" value="%s" id="%s" %s /><label for="%s"> %s</label></li>',
                $this->get_field_name( 'bg' ),
                $dimention,
                $id,
                checked( $dimention, $bg, false ),
                $id,
                $label
            );
        }
?>
        </ul>
        </p>
<?php
    }

    /**
     * Sanitize bg
     */

    private function sanitize_bg( $value )
    {
        if ( in_array( $value, $this->dimentions ) ) {
            return $value;
        }
        return 'light';
    }

    /**
     * Sanitize width
     */

    private function sanitize_width( $value )
    {
        $value = intval( $value );
        if ( $value > 99 ) {
            return $value;
        }
        return 300;
    }

} // class Foo_Widget

add_action( 'widgets_init', create_function( '', 'register_widget( "iWorks_WP_Anniversary_Meetup_City_List_Widget" );' ) );

