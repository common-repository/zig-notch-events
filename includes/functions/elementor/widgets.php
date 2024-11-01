<?php
    add_action( 'elementor/widgets/register', 'register_zig_notch_elementor_widgets' );

    function register_zig_notch_elementor_widgets( $widgets_manager ) {

        require_once( __DIR__ . '/widgets/agenda.php' );
        require_once( __DIR__ . '/widgets/exhibitors.php' );
        require_once( __DIR__ . '/widgets/products.php' );
        require_once( __DIR__ . '/widgets/event_feed.php' );

        $widgets_manager->register( new \Zig_notch_agenda() );
        $widgets_manager->register( new \Zig_notch_exhibitors() );
        $widgets_manager->register( new \Zig_notch_products() );
        $widgets_manager->register( new \Zig_notch_event_feed() );
    }

