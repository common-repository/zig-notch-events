<?php 
    add_action('wp_enqueue_scripts', 'zignotch_enqueue_styles');


    function zignotch_enqueue_styles(){
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zignotch_style_css', plugin_dir_url(__DIR__). "css/style.css", array(), 1.1);
        wp_enqueue_style('zignotch_agenda_css', plugin_dir_url(__DIR__). "css/agenda.css");
        wp_enqueue_style('zignotch_exhibitors_css', plugin_dir_url(__DIR__). "css/exhibitors.css");
        wp_enqueue_style('zignotch_bootstrap_grid', plugin_dir_url(__DIR__). "css/bootstrap-grid.css");
        wp_enqueue_style('zignotch_sponsors_css', plugin_dir_url(__DIR__). "css/sponsors.css");
        wp_enqueue_style('zignotch_speakers_css', plugin_dir_url(__DIR__). "css/speakers.css");
        wp_enqueue_style('zignotch_products_css', plugin_dir_url(__DIR__). "css/products.css");
    }
   
   