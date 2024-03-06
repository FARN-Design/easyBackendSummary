<?php
/**
 * @package easy backend summary by farn
 * @version 1.0.1
 */

/*
 Plugin Name: Easy Backend Summary
 Plugin URI: https://farn.de 
 Description: This is a a dashboard plugin for the wordpress backend who shows a easy summary of the latest activitys
 Author: Farn - Digital Brand Design
 Version: 1.0.1
 Author URI: https://farn.de
 */


//add css and js
function farn_enqueueScriptsAndStyles(){   
    wp_enqueue_script( 'easy-backend-summary-script', plugin_dir_url( __FILE__ ) . 'js/easy-backend-summary.js', array('jquery'),'', true);
    wp_enqueue_style( 'easy-backend-summary-style', plugin_dir_url( __FILE__ ) . 'css/easy-backend-summary.css');
}

add_action('admin_enqueue_scripts',  'farn_enqueueScriptsAndStyles');

//-----------------------------Requirements-----------------------------

// require_once ('../wp-content/plugins/easy-backend-summary/db/db-handle.php');

 //Add Widget to show content
function easy_backend_summary() {
    add_meta_box(
        'easy_backend_summary', // ID des Widgets
        'Easy Backend Summary', // Titel des Widgets
        'easy_backend_summary_funktion', // Callback-Funktion, die den Inhalt des Widgets ausgibt
        'dashboard', // Ort, an dem das Widget angezeigt werden soll ('dashboard' für das Dashboard)
        'normal', // Position auf der Seite ('normal', 'side', oder 'advanced')
        'high' // Priorität in Bezug auf andere Widgets ('high' oder 'low')
    );
}
add_action('wp_dashboard_setup', 'easy_backend_summary');

//Creat Database once by activating the Plugin
function create_database(){
global $wpdb;
$ebsum = $wpdb->prefix.'easyBackendSummary';
$charset = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS " . $ebsum . "(
    set_ID      int     NOT NULL AUTO_INCREMENT,
    user_ID     int     UNIQUE,
    last_login  BIGINT,
    set_posttypes   text,
    set_userroles   text,
    max_overwiev    int,
    max_view        int,
    check_period    int,
    PRIMARY KEY (set_ID)
)$charset;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_database');




//setting all functions to show
function easy_backend_summary_funktion() {
    echo set_period();
    echo setup_posttypes();
    echo setup_userroles();
    set_last_login();
    echo set_quantity();
    echo show_posts();
    echo show_user();
    
}


//-----------------------------setting the Post Functions-----------------------------

//create function for looping the trough the array and make for each value an checkbox in an table
function createPostTypeSetting($types,$name) {
    $user_id = get_current_user_id( );
    $posttype_setting  = '<strong>Einstellung der '.$name.'</strong>';
    $posttype_setting .= '<ul class="ebs-ul"><form class="ebsum-class" ID="'.$user_id.'" method="POST" action="" name="ebsum_set">';
    $to_check = get_sets($name);
    foreach($types as $type){
        $type = trim($type);

        $checked = "";
        foreach($to_check as $check){
            $check = trim($check);
            if($type == $check ){
                $checked = "checked";
                break;
            }
        }


        $posttype_setting .= '<li><input type="checkbox" id="postytpe'.$type.'" name="'.$name.' '.$type.'" value="'.$name.' '.$type.'"'.$checked.'>';
        $posttype_setting .= '<label for="postytpe'.$type.'">'.$type.'</label></li>';

        if(isset($_POST[$type])){
            echo "'".$type."is checked'<br>";
        };
    }

    
    $posttype_setting .= '</form></ul>';
    $posttype_setting .= '<input class="button button-primary ebsum_button" "type="submit" name=" " value="Speichern"><br>';

    return $posttype_setting;
}


// setup for the posttypes
function setup_posttypes(){
    $types = get_post_types();

    echo createPostTypeSetting($types, "set_posttypes");
    
}

// setup for the userroles
function setup_userroles(){
    global $wp_roles;
    $roles =$wp_roles->get_names();
    
    echo createPostTypeSetting($roles, "set_userroles");
}


//function to set the last login time (checks if user id allready set and then saves the last login time)
function set_last_login(){
    
    $user_id = get_current_user_id( );
    $now = get_user_meta(get_current_user_id(), "wfls-last-login", true);
    
    global $wpdb;
    $ebsum = $wpdb->prefix.'easyBackendSummary';

    $check_user_ID = $wpdb->get_row( "SELECT `user_ID` FROM `uPQ3q_easyBackendSummary` WHERE `user_ID` = $user_id");

    $check_user_ID = $check_user_ID->user_ID;

    if($check_user_ID != $user_id){
    $wpdb->insert(
        $ebsum,
        [
            'user_ID'           =>  $user_id,
            'last_login'        =>  $now,
        ]
    );}
    else {
        $wpdb->update(
            $ebsum,
                ['last_login'   =>  $now],  
                ['user_ID'      =>  $user_id]  
            
        );}
    }



//function to set the shown period
function set_period(){

    ?>
        <label class="period_time" for="period">Period to check:</label>
        <select lass="period_time" name="period" id="periods">
        <option lass="period_time" value=0>Seit dem letzten Login</option>
        <option lass="period_time" value=1>Innerhalb der letzen 7 Tage</option>
        <option lass="period_time" value=2>Innerhalb der letzen 30 Tage</option>
        <option lass="period_time" value=3>Gesamter Zeitraum</option>
        </select>
        <br>
    <?php

}

//function to set the shown quantity
function set_quantity(){
    $max_view = get_sets('max_view');
    ?>
        <label class="quantity" for="Quantity">Quantity to show:</label>
        <input type="number" min="1" max="10" name="Quantity" stept="1" id="quantitys" default="3" value="<?php echo $max_view[0]; ?>">
        <br>
    <?php
}

//-----------------------------setting the Get Functions-----------------------------

function get_sets($key){
$user_id = get_current_user_id( );
global $wpdb;
$datas = $wpdb->get_row( "SELECT `$key` FROM `uPQ3q_easyBackendSummary` WHERE `user_ID` = $user_id
" );
$datas = (array) $datas;
$datas = implode(";", $datas);
$datas = trim($datas);
$datas = explode(";", $datas);
return $datas;
}

// set function to show the post by posttype
function show_posts(){
    $to_check = get_sets('set_posttypes');
    $limit = get_sets('max_view');
    $timestamp = get_sets('last_login');
    $start = gmdate("Y-m-d", $timestamp[0]);
    $end = date("Y-m-d");

    foreach($to_check as $check){
        $check = trim($check);
        $args = array(
            'post_type' => $check,
            'posts_per_page'         => $limit[0],
            'order'                  => 'DESC',
            'orderby'                => 'post_date',
            'date_query'             => array(
                                        array(
                                        // 'after'     => $start,
                                        'before'    => $end,
                                        'inclusive' => true,
                                ),
                            ),
        );
        $post_query = new WP_Query( $args );

        if ( $post_query->have_posts() ) {
            echo '<h3><strong>'.$check.'</strong></h3>';
            echo '<ul id="ebsum_'.$check.'">';
            while ( $post_query->have_posts() ) {
                $post_query->the_post();
                echo '<li><a href="'.get_permalink().'">' .get_the_date()."     " . esc_html( get_the_title() ) . 
                
                '</a></li>';
            }
            echo '</ul>';
        }
        
    }
    
}

// set function to show the user by roles
function show_user(){
    $to_check = get_sets('set_userroles');
    $limit = get_sets('max_view');
    $timestamp = get_sets('last_login');
    $start = gmdate("Y-m-d", $timestamp[0]);
    $end = date("Y-m-d");

    foreach($to_check as $check){
        $check = trim($check);
        $args = array(
            'role'            =>    $check,
            'posts_per_page'  =>    $limit[0],
            'order'           =>    'DESC',
            'orderby'         =>    'user_registered',
            'date_query'             => array(
                                        array(
                                        // 'after'     => $start,
                                        'before'    => $end,
                                        'inclusive' => true,
                                ),
                            ),
        );
        $users = get_users( $args );
        echo '<h3><strong>'.$check.'</strong></h3>';
        echo '<ul>';
            foreach ( $users as $user ) {
                echo '<li><a href="users.php?s='.$user->ID.'">' .esc_html($user->user_registered). 
                esc_html( $user->display_name ) . '[' . esc_html( $user->user_email ) . ']'.esc_html( $user->user_url ).'</a></li>';
            }
        echo '</ul>';
        
    }
    
}

?>