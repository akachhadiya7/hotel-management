<?php
add_action( 'init', 'create_patient_table', 0 );
function create_patient_table() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'patient';

	$sql = "CREATE TABLE IF NOT EXISTS ". $table_name ."(
        id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
        name varchar(100) not null,
        mobile_number varchar(12) not null,
        hospital_Id BIGINT(20),
        gp_Id BIGINT(20),
        created_date timestamp NOT NULL DEFAULT current_timestamp(),
        updated_date timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE=INNODB;";
  
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

    $sql = "ALTER TABLE ". $table_name ."
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `hospital_Id` (`hospital_Id`),
        ADD UNIQUE KEY `gp_Id` (`gp_Id`)";
    dbDelta( $sql );
}

function manage_patient(){
    if( get_query_var('page', 'patient') ){

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        
        switch( $action ){

        case 'new' : 
            
            echo add_update_patient();
            break;

        case 'edit' :

            $patient_id = isset( $_REQUEST['patient_id'] ) ? $_REQUEST['patient_id'] : '';
            $patient = get_patient_by_id( $patient_id );
            echo add_update_patient( $patient_id, $patient );
            break;

        default :
            echo view_patient();
            break;

        }
        
    }
}

function get_patient_by_id( $patient_id ){
    global $wpdb;

    $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}patient WHERE id = %d", $patient_id), ARRAY_A );

    return $result;
}

function add_update_patient( $patient_id = 0, $patient = array() ){ 
    $user = wp_get_current_user();
    $roles = ( array ) $user->roles;
    $args = array(
        'role'    => 'gp',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $users = get_users( $args );
    $hp_args = array(
        'post_type' => 'hospital',
        'posts_per_page' => -1,
        'order'   => 'ASC'
    );
    $hospitals = new WP_Query($hp_args); ?>

    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo ( $patient_id != 0 ) ? 'Patient #'. $patient_id : 'Add New Patient'; ?></h1>
        <form id="patient-form" method="post" action="<?php echo get_default_url(); ?>">
            <table class="form-table">
                <tbody>
                <tr>
                    <th>Name</th>
                    <td><input type="text" name="name" value="<?php echo ( !empty($patient) ) ? $patient['name'] : ''; ?>" placeholder="Patient Name" required /></td>
                </tr>
                <tr>
                    <th>Mobile Number</th>
                    <td><input type="text" name="mobile_number" value="<?php echo ( !empty($patient) ) ? $patient['mobile_number'] : ''; ?>" required /></td>
                </tr>
                <?php if( $hospitals->have_posts() ) : ?>
                    <tr>
                    <th>Hospital</th>
                    <td>
                        <select name="hospitalId" required>
                        <option value="">Select Hospital</option>
                        <?php while ($hospitals->have_posts()) : $hospitals->the_post();
                            $selected_hp = ( !empty($patient) && $patient['hospital_Id'] == get_the_ID() ) ? ' selected' : ''; ?>
                            <option value="<?php echo get_the_ID(); ?>"<?php echo $selected_hp; ?>><?php echo get_the_title(get_the_ID()); ?></option>
                        <?php endwhile; ?>
                        </select>
                    </td>
                    </tr>
                <?php endif;
                if( $roles[0] == 'administrator' || $roles[0] == 'super_admin' ) : ?>
                <tr>
                    <th>GP</th>
                    <td>
                    <select name="gpId" required>
                        <option value="">Select GP</option>
                        <?php foreach( $users as $user ) : 
                        $selected_user = ( !empty($patient) && $patient['gp_Id'] == $user->ID ) ? ' selected' : ''; ?>
                        <option value="<?php echo $user->ID; ?>"<?php echo $selected_user; ?>><?php echo $user->display_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if( $roles[0] == 'gp' ) : ?>
                    <input type="hidden" name="gpId" value="<?php echo get_current_user_id(); ?>" />
                <?php endif; ?>
                <?php if( $patient_id != 0 ) : ?>
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>" />
                <?php endif; ?>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

<?php }

function view_patient() { ?>
    <div class="wrap view_patient">
        <h1 class="wp-heading-inline">Patient</h1>
        <a href="<?php echo get_default_url().'&action=new'; ?>" class="page-title-action patient_new_action_button">Add New</a>
        <div id="poststuff" class="patient_lists_wrap">
            <div id="post-body" class="metabox-holder patient-body columns-2">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="post">
                            <?php $patient_obj = new PatientList();
                            $patient_obj->prepare_items();
                            $patient_obj->display(); ?>                
                        </form>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
<?php }