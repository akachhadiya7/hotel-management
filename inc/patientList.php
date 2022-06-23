<?php 
class PatientList extends WP_List_Table {

    /** Class constructor */
    public function __construct() {

        parent::__construct( [
            'singular' => __( 'Patient', 'twentytwentytwo-child' ), 
            'plural' => __( 'Patients', 'twentytwentytwo-child' ), 
            'ajax' => false 
        ] );

    }

    public static function get_patient( $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        if( $roles[0] == 'gp' ){
            $select_fields = "pt.id AS patient_id, pt.name AS patient_name, pt.mobile_number AS mobile_number, p.post_title AS hospital, DATE_FORMAT(pt.created_date, '%Y/%m/%d') AS created_date, DATE_FORMAT(pt.updated_date, '%Y/%m/%d') AS updated_date";
        }else{
            $select_fields = "pt.id AS patient_id, pt.name AS patient_name, pt.mobile_number AS mobile_number, p.post_title AS hospital, u.display_name AS gp, DATE_FORMAT(pt.created_date, '%Y/%m/%d') AS created_date, DATE_FORMAT(pt.updated_date, '%Y/%m/%d') AS updated_date";
        }

        $sql = "SELECT {$select_fields}
                FROM {$wpdb->prefix}patient AS pt
                LEFT JOIN {$wpdb->prefix}posts AS p ON pt.hospital_Id = p.ID
                LEFT JOIN {$wpdb->prefix}users AS u ON pt.gp_Id = u.ID";
        
        if( $roles[0] == 'gp' ){
            $sql .= " WHERE pt.gp_Id = ". $user->ID;
        }
        
        // if( ! empty( $_REQUEST['s'] ) ){

        //     $search = esc_sql( $_REQUEST['s'] );
        //     $sql .= " WHERE wr.work_request_id LIKE '%{$search}%'";

        // }

        if ( ! empty( $_REQUEST['orderby'] ) ) {

            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
            
        }else{

            $sql .= ' ORDER BY pt.id';

        }
        
        $sql .= " LIMIT $per_page";
        
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        
        return $result;
    }

    public static function delete_patient( $id ){

        global $wpdb;
        
        $wpdb->delete( $wpdb->prefix . 'patient' , array( 'id' => $id ) );

    }

    public static function record_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}patient";
        
        return $wpdb->get_var( $sql );
    }

    public function no_items() {
        _e( 'No Patient available.', 'twentytwentytwo-child' );
    }

    public function column_name( $item ) {
        
        $nonce = array(
            'edit' => wp_create_nonce( 'edit_work_request' ),
            'delete' => wp_create_nonce( 'delete_work_request' ),
        );
        
        $actions_lists = array('edit', 'delete');

        $title = sprintf( '<a href="?page=%s&action=%s&patient_id=%s&_wpnonce=%s"><strong>' . $item . '</strong></a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item ), $nonce['edit'] );
        
        foreach( $actions_lists as $action ){

            $actions[$action] = sprintf( '<a href="?page=%s&action=%s&patient_id=%s&_wpnonce=%s">'. ucfirst( $action ) .'</a>', esc_attr( $_REQUEST['page'] ), $action, absint( $item ), $nonce[$action] );

        }
        
        return $title . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name ) {
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        if( $roles[0] == 'gp' ){
            switch ( $column_name ) {
                case 'patient_id':
                    return $this->column_name( $item[ $column_name ] );
                case 'patient_name':
                case 'mobile_number':
                case 'hospital':
                case 'created_date':
                case 'updated_date':
                    return $item[ $column_name ];
                default:
                    return print_r( $item, true ); //Show the whole array for troubleshooting purposes
            }

        }else{
            switch ( $column_name ) {
                case 'patient_id':
                    return $this->column_name( $item[ $column_name ] );
                case 'patient_name':
                case 'mobile_number':
                case 'hospital':
                case 'gp':
                case 'created_date':
                case 'updated_date':
                    return $item[ $column_name ];
                default:
                    return print_r( $item, true ); //Show the whole array for troubleshooting purposes
            }
        }
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-actions[]" value="%s" />', $item['patient_id']
        );
    }

    function get_columns() {
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        if( $roles[0] == 'gp' ){
            $columns = [
                'cb' => '<input type="checkbox" />',
                'patient_id' => __( 'Patient#', 'twentytwentytwo-child' ),
                'patient_name' => __( 'Name', 'twentytwentytwo-child' ),
                'mobile_number' => __( 'Mobile Number', 'twentytwentytwo-child' ),
                'hospital' => __( 'Hospital', 'twentytwentytwo-child' ),
                'created_date' => __( 'Created Date', 'twentytwentytwo-child' ),
                'updated_date' => __( 'Updated Date', 'twentytwentytwo-child' ),
            ];
        }else{
            $columns = [
                'cb' => '<input type="checkbox" />',
                'patient_id' => __( 'Patient#', 'twentytwentytwo-child' ),
                'patient_name' => __( 'Name', 'twentytwentytwo-child' ),
                'mobile_number' => __( 'Mobile Number', 'twentytwentytwo-child' ),
                'hospital' => __( 'Hospital', 'twentytwentytwo-child' ),
                'gp' => __( 'GP', 'twentytwentytwo-child' ),
                'created_date' => __( 'Created Date', 'twentytwentytwo-child' ),
                'updated_date' => __( 'Updated Date', 'twentytwentytwo-child' ),
            ];
        }
        
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'patient_id' => array( 'patient_id', true ),
        );
        
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete',
        ];
        
        return $actions;
    }
    
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();
        
        /** Process bulk action */
        $this->process_bulk_action();
        
        $per_page = $this->get_items_per_page( 'patient_per_page', 5 );
        
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        
        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ] );
        
        $this->items = self::get_patient( $per_page, $current_page );
        
    }

    public function process_bulk_action() {

        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();
        
        switch ( $action ) {
                
            case 'delete':
                self::delete_patient( absint( $_GET['patient_id'] ) );
                
                wp_redirect( esc_url( get_default_url() ) );
                break;

            case 'bulk-delete':
                $ids = esc_sql( $_POST['bulk-actions'] );

                foreach ( $ids as $id ) {
                    self::delete_patient( $id );
                }

                wp_redirect( esc_url( get_default_url() ) );
                
                break;

            default:
                return;
                break;
        }
        add_flash_notice( __("Saved patient"), "success", true );
        return;
    }
}