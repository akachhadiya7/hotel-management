<?php 
// remove pages role wise
add_action( 'admin_menu', 'register_admin_menu' );
function register_admin_menu(){
  $user = wp_get_current_user();
  $roles = ( array ) $user->roles;
  
  if( $roles[0] == 'administrator' || $roles[0] == 'gp' ){
    remove_menu_page( 'options-general.php' );
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'admin.php?page=members-settings' );
    remove_menu_page( 'edit.php?post_type=acf-field-group' );
    remove_menu_page( 'export-personal-data.php' );
    remove_menu_page( 'members' );
  }
  
  $hook = add_menu_page('Manage Patient', 'Manage Patient', 'manage_options', 'patient', 'manage_patient', 'dashicons-universal-access' );
  if( isset( $_GET['page'] ) && 'patient' == $_GET['page'] ){
    add_action( "load-$hook", 'screen_option' );
  }
}

function screen_option() {

    $option = 'per_page';
    $args = [
        'label' => 'Patients',
        'default' => 5,
        'option' => 'patient_per_page'
    ];
    
    add_screen_option( $option, $args );
    
    $patient_obj = new PatientList();

}

/**
 * Add a flash notice to {prefix}options table until a full page refresh is done
 *
 * @param string $notice our notice message
 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
 * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice
 * @return void
 */
 
function add_flash_notice( $notice = "", $type = "warning", $dismissible = true ) {
    
    $notices = get_option( "wrm_flash_notices", array() );
  
    $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
  
    // We add our new notice.
    array_push( $notices, array( 
        "notice" => $notice, 
        "type" => $type, 
        "dismissible" => $dismissible_text
    ) );
  
    update_option("wrm_flash_notices", $notices );
    
}
  
/**
 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
* our database and display them, after that, we remove the option to prevent notices being displayed forever.
* @return void
*/  
function display_flash_notices() {
    $notices = get_option( "wrm_flash_notices", array() );

    // Iterate through our notices to be displayed and print them.
    foreach ( $notices as $notice ) {
        
        printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $notice['type'],
            $notice['dismissible'],
            $notice['notice']
        );
    }

    // Now we reset our options to prevent notices being displayed forever.
    if( ! empty( $notices ) ) {
        delete_option( "wrm_flash_notices", array() );
    }
}
add_action( 'admin_notices', 'display_flash_notices', 12 );

// get patient url
function get_default_url(){
    return admin_url('admin.php?page=patient');
}
  
function get_page_Title(){
    return __( 'Patient', 'twentytwentytwo-child' );
}
  
add_action("wp_ajax_add_edit_patient", "add_edit_patient");
add_action("wp_ajax_nopriv_add_edit_patient", "my_must_login");
  
function add_edit_patient() {
  
    global $wpdb;
  
    $patient_id = '';
    $table = $wpdb->prefix . 'patient';
    $response = array();
  
    if( isset( $_REQUEST ) ){
  
        $patient_data = array();
        $patient_data = array(
            'name' => $_REQUEST['name'],
            'mobile_number' => $_REQUEST['mobile_number'],
            'hospital_Id' => $_REQUEST['hospitalId'],
            'gp_Id' => $_REQUEST['gpId'],
        );
    
        if( !empty( $_REQUEST[ 'patient_id' ] ) ){
    
            $patient_id = $_REQUEST[ 'patient_id' ];
            $wpdb->update($table, $patient_data, array( 'id' => $_REQUEST[ 'patient_id' ] ), array( '%s', '%s', '%d', '%d' ) );
        
        }else{
            
            $wpdb->insert($table, $patient_data, array( '%s', '%s', '%d', '%d' ));
            $patient_id = $wpdb->insert_id;
    
        }
    }
  
    if( '' !== $patient_id ){
        $response['success'] = true;
        add_flash_notice( __("Saved Patient"), "success", true );
    }else{
        $response['success'] = false;
        $response['message'] = 'Something Went Wrong';
    }
  
    echo json_encode( $response );
    exit();
  
}