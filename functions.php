<?php
add_action( 'wp_enqueue_scripts', 'twentytwentytwochild_enqueue_styles' );
function twentytwentytwochild_enqueue_styles() {
  $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
  $theme = wp_get_theme();
  wp_enqueue_style( 'child-style', get_stylesheet_uri(),
      array( $parenthandle ),
      $theme->get('Version') // this only works if you have Version in the style header
  );
}

add_action( 'admin_enqueue_scripts', 'admin_enqueue_styles' );
function admin_enqueue_styles(){
  wp_enqueue_style('intlTel-styles', get_stylesheet_directory_uri() . '/assets/css/intlTelInput.min.css');
  wp_enqueue_style('custom-styles', get_stylesheet_directory_uri() . '/assets/css/custom.css');

  wp_enqueue_script('intlTel-script', get_stylesheet_directory_uri() . '/assets/js/intlTelInput.min.js', array('jquery'));
  wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery-core') );
  wp_localize_script( 'custom-script', 'hmAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
}

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once(get_stylesheet_directory() . '/inc/patientList.php');

require_once( get_stylesheet_directory() . '/inc/settings.php' );

require_once( get_stylesheet_directory() . '/inc/postSettings.php' );

require_once( get_stylesheet_directory() . '/inc/patient.php' );