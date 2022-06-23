<?php
// hospital custom post
function hospital_custom_post() {
  
  $labels = array(
    'name'                => _x( 'Hospitals', 'Post Type General Name', 'twentytwentytwo-child' ),
    'singular_name'       => _x( 'Hospital', 'Post Type Singular Name', 'twentytwentytwo-child' ),
    'menu_name'           => __( 'Hospitals', 'twentytwentytwo-child' ),
    'parent_item_colon'   => __( 'Parent Hospital', 'twentytwentytwo-child' ),
    'all_items'           => __( 'All Hospitals', 'twentytwentytwo-child' ),
    'view_item'           => __( 'View Hospital', 'twentytwentytwo-child' ),
    'add_new_item'        => __( 'Add New Hospital', 'twentytwentytwo-child' ),
    'add_new'             => __( 'Add New', 'twentytwentytwo-child' ),
    'edit_item'           => __( 'Edit Hospital', 'twentytwentytwo-child' ),
    'update_item'         => __( 'Update Hospital', 'twentytwentytwo-child' ),
    'search_items'        => __( 'Search Hospital', 'twentytwentytwo-child' ),
    'not_found'           => __( 'Not Found', 'twentytwentytwo-child' ),
    'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwentytwo-child' ),
  );
      
  $args = array(
    'label'               => __( 'Hospitals', 'twentytwentytwo-child' ),
    'description'         => __( 'Hospital news and reviews', 'twentytwentytwo-child' ),
    'labels'              => $labels,
    'supports'            => array( 'title', 'custom-fields', ), 
    'taxonomies'          => array( 'genres' ),
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 5,
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'post',
    'show_in_rest'        => true,
  );
  register_post_type( 'hospital', $args );      
}    
add_action( 'init', 'hospital_custom_post', 0 );

// Show custom hospital fields in list
add_filter( 'manage_hospital_posts_columns', 'hospital_columns' );
function hospital_columns( $columns ) {  
  $columns = array(
    'cb' => $columns['cb'],
    'title' => __( 'Hospital Name' ),
    'logo' => __( 'Logo' ),      
    'mobileNumber' => __( 'Mobile Number', 'twentytwentytwo-child' ),
    'status' => __( 'Status', 'twentytwentytwo-child' ),
    'created_date' => __('Created Date', 'twentytwentytwo-child'),
    'updated_date' => __('Updated Date', 'twentytwentytwo-child'),
  );
  return $columns;
}

// Add and change column in list table
add_action( 'manage_hospital_posts_custom_column', 'hospital_column', 10, 2);
function hospital_column( $column, $post_id ) {
  
  if ( 'logo' === $column ) {
    echo '<img src="'. get_field( 'logo', $post_id ) .'" width="200px" />';
  }
  if( 'mobileNumber' === $column ){
    echo get_field( 'mobile_number', $post_id );
  }
  if( 'status' === $column ){
    echo ( get_field( 'status', $post_id ) == 'active' ) ? 'Active' : 'Inactive';
  }
  if( 'created_date' === $column ){
    echo get_the_time( 'F jS, Y', get_post( $post_id ) ). ' At ' .get_the_time( '', get_post( $post_id ) );
  }
  if( 'updated_date' === $column ){
    echo get_the_modified_time('F jS, Y', get_post( $post_id )). ' At ' .get_the_modified_time('', get_post( $post_id ) );
  }
}