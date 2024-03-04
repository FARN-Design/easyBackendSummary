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


 //register AJAX Function
add_action( 'wp_ajax_set_settings', 'set_settings' );
add_action( 'wp_ajax_nopriv_set_settings', 'set_settings' );

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
    check_period    date,
    PRIMARY KEY (set_ID)
)$charset;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_database');




//start function
function easy_backend_summary_funktion() {
    echo setup_posttypes();
    echo setup_userroles();
    set_settings();
}

//create function for looping the trough the array and make for each value an checkbox in an table
function createPostTypeSetting($types,$name) {
    $posttype_setting  = '<strong>Einstellung der '.$name.'</strong>';
    $posttype_setting .= '<ul class="ebs-ul"><form class="ebsum-class" ID="ebsum_set_id" method="POST" action="" name="ebsum_set">';

    foreach($types as $type){
        $posttype_setting .= '<li><input type="checkbox" id="postytpe'.$type.'" name="'.$type.'" value="'.$type.'">';
        $posttype_setting .= '<label for="postytpe'.$type.'">'.$type.'</label></li>';

        if(isset($_POST[$type])){
            echo "'".$type."is checked'<br>";
        };
    }

    $posttype_setting .= '<input type="submit" name=" " value="submit">';
    $posttype_setting .= '</form></ul>';

    return $posttype_setting;
}

// setup for the posttypes
function setup_posttypes(){
    $types = get_post_types();

    echo createPostTypeSetting($types, "Posttypes");
    
}

// setup for the userroles
function setup_userroles(){
    global $wp_roles;
    $roles =$wp_roles->get_names();
    
    echo createPostTypeSetting($roles, "Nutzer-Rollen");
}



//set and get the settings
function set_settings(){
    
    $user_id = get_current_user_id( );
    $now = get_user_meta(get_current_user_id(), "wfls-last-login", true);
    

    global $wpdb;
    $ebsum = $wpdb->prefix.'easyBackendSummary';

    $wpdb->replace(
        $ebsum,
        [
            'user_ID'       =>  $user_id,
            'last_login'    =>  $now
        ]

    );

}




?>