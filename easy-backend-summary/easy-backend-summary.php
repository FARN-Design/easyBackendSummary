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

add_action( 'wp_ajax_show_posts', 'show_posts' );
add_action( 'wp_ajax_nopriv_show_posts', 'show_posts' );
add_action( 'wp_ajax_show_user', 'show_user' );
add_action( 'wp_ajax_nopriv_show_user', 'show_user' );


 //Add Widget to show content
function easy_backend_summary() {
    add_meta_box(
        'easy_backend_summary', 
        'Easy Backend Summary', 
        'easy_backend_summary_funktion', 
        'dashboard', 
        'normal', 
        'high' 
    );
}
add_action('wp_dashboard_setup', 'easy_backend_summary');

//Creat Database once by activating the Plugin
function create_database(){
    global $wpdb;
    $ebsum = $wpdb->prefix.'easyBackendSummary';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS " . $ebsum . "(
        set_ID          int     NOT NULL AUTO_INCREMENT,
        user_ID         int     UNIQUE,
        last_login      BIGINT,
        set_posttypes   text,
        set_userroles   text,
        load_limit      int,
        max_view        int DEFAULT 3,
        change_box      text,
        check_period    text,
        PRIMARY KEY (set_ID)
    )   $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_database');




//setting all functions to show
function easy_backend_summary_funktion() {
    
    
    set_last_login();
    ?> 
    <div class="ebsum_wrapper">

    <div class="ebsum_show_wrapper">
    
    <?php echo show_posts();?> 
    
    <?php echo show_user();?> 
    
    </div>
    
    <div class="setting_wrapper_wrapper">
    
    <span class="setting_categories_wrapper">+ weitere Kategorien hinzufügen</span>
    <button type="button" id="ebsum_setting_button"><span class="dashicons dashicons-admin-generic"></span></button>
    
    </div>
    <div class="setting_posttypes"> <?php echo setup_posts_and_users();?>     </div>
   
    <div class="ebsum_setting_wrapper">
    <div class="setting_main">      
        <?php 
        echo main_settings();
        ?>       
    </div>
    </div>
    </div>
    <?php
}


//-----------------------------setting the Post Functions-----------------------------

//create function for looping the trough the array and make for each value an checkbox in an table
function create_post_type_setting($types,$name, $roles, $rolenames) {

    $user_id = get_current_user_id( );
    $posttype_setting = '<ul class="ebs-ul"><form class="ebsum-class" ID="'.$user_id.'" method="POST" action="" name="ebsum_set">';
    $posttype_setting .= '<strong>Posttypes</strong>';
    $to_check_posts = get_sets($name);
    $to_check_roles = get_sets($rolenames);

    foreach($types as $type){
        $type = trim($type);

        $checked = "";
        foreach($to_check_posts as $check){
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
    $posttype_setting .= "<br> <strong>Userrolles</strong>";
    foreach($roles as $role){
        $role = trim($role);

        $checked = "";
        foreach($to_check_roles as $check){
            $check = trim($check);
            if($role == $check ){
                $checked = "checked";
                break;
            }
        }

        $posttype_setting .= '<li><input type="checkbox" id="postytpe'.$role.'" name="'.$rolenames.' '.$role.'" value="'.$rolenames.' '.$role.'"'.$checked.'>';
        $posttype_setting .= '<label for="postytpe'.$role.'">'.$role.'</label></li>';

        if(isset($_POST[$role])){
            echo "'".$role."is checked'<br>";
        };
    }

    $posttype_setting .= '</form></ul>';
    $posttype_setting .= '<div class="ebsum_button_wrapper"><input form="'.$user_id.'" class="button button-primary ebsum_button" type="submit" name=" " value="Speichern"></div><br>';
    
    return $posttype_setting;
}


// setup for the posttypes
function setup_posts_and_users(){
    $types      = get_post_types();
    global $wp_roles;
    $roles      =$wp_roles->get_names();
    echo create_post_type_setting($types, "set_posttypes", $roles, "set_userroles");
    
}



//function to set the last login time (checks if user id allready set and then saves the last login time)
function set_last_login(){
    
    $user_id    = get_current_user_id( );
    $now        = get_user_meta(get_current_user_id(), "wfls-last-login", true);
    global $wpdb;
    $ebsum      = $wpdb->prefix.'easyBackendSummary';
    $check_user_ID = $wpdb->get_row( "SELECT `user_ID` FROM `uPQ3q_easyBackendSummary` WHERE `user_ID` = $user_id");

    if(isset($check_user_ID->user_ID)){
        if($check_user_ID->user_ID != $user_id){
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
        }else{
            $wpdb->insert(
                $ebsum,
                [
                    'user_ID'           =>  $user_id,
                    'last_login'        =>  $now,
                ]
            );
        }
    }



//Function for all settings (changes, periods, limits)
function main_settings(){

    //function to set change view
    $max_view   = get_sets('max_view');
    $loadlimit  = get_sets('load_limit');
    $period     = get_sets('check_period');
    $lastlogin  = "lastlogin";
    $today      = "today";
    $lastweek   = "lastweek";
    $lastmonth  = "lastmonth";
    $whole      = "whole";
    $change     = get_sets('change_box');
    $checked    = "";

    if($change[0] == 'changes' ){
        $checked = "checked";
    }

    ?>
    <form ID="main_settings">
        <ul class="settingslist">
        <li class="settingslist" ><label for="changes">Änderungen anzeigen</label>
        <input type="checkbox" id="changes" name="changes" value="changes" <?php echo $checked; ?> >
        <br></li>

        <li class="settingslist" ><label class="quantity" for="quantity">Übersicht:</label>
        <input type="number" min="1" max="100" name="quantity" step="1" id="quantitys" default="3" value="<?php echo $max_view[0]; ?>">
        <br></li>

        <li class="settingslist" ><label class="loadlimit" for="loadlimit">max. Anzahl:</label>
        <input type="number" min="1" max="100" name="loadlimit" step="1" id="loadlimits" default="10" value="<?php echo $loadlimit[0]; ?>">
        <br></li>

        <li class="settingslist"><p>Anzeigeperiode</p>
            <select class="period_time" name="period" id="periods">
            <option class="period_time" value="<?php echo $lastlogin ?>" <?php if(trim($period[0])==$lastlogin){echo ' selected';} ?>>Seit dem letzten Login</option>
            <option class="period_time" value="<?php echo $lastweek ?>" <?php if(trim($period[0])==$lastweek){echo ' selected';} ?>>Innerhalb der letzen 7 Tage</option>
            <option class="period_time" value="<?php echo $lastmonth ?>" <?php if(trim($period[0])==$lastmonth){echo ' selected';} ?>>Innerhalb der letzen 30 Tage</option>
            <option class="period_time" value="<?php echo $whole ?>" <?php if(trim($period[0])==$whole){echo ' selected';} ?>>Gesamter Zeitraum</option>
        </select><br></li>

        <input type="submit" value="Speichern" class="button button-primary">
        <ul></ul>
    </form>
    <?php
}
    


//-----------------------------get data from database functions-----------------------------


//get the settings from databes
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


function check_period(){

    $timestamp  = get_sets('last_login');

    $period     = get_sets('check_period');
    $start      = "";
    $end        = date("Y-m-d");

     switch ($period[0]) {
        case 'lastlogin':
            $start = gmdate("Y-m-d", $timestamp[0]);
            break;
        case 'lastweek':
            $start = date("Y-m-d", strtotime('-7 day'));
            break;
        case 'lastmonth':
            $start = date("Y-m-d", strtotime('-30 day'));
            break;
        case 'whole':
            $start = "0000-00-00";
            break;
        default:
            echo "Ungültige Auswahl.";
    }

    return $start;

}

// set function to show the post by posttype
function show_posts(){
    $to_check = get_sets('set_posttypes');
    

    if($to_check[0]){
        echo "<h3><strong>Posttypes</strong></h3>";
            
        $limit      = get_sets('load_limit');
        $max_view   = get_sets('max_view');
        $max_view   = $max_view[0];
        $start      = check_period();

        // check if view of change is activ or not. if it is then the ordby by will change to modifed date and the modified date will show in collum
        $change = get_sets('change_box');
        $orderby = "";
        if($change[0] == 'changes' ){
            $orderby    = "post_modified";
            $label      = true;
        }else{
            $orderby    = "post_date";
            $label      = false;
        }


        foreach($to_check as $check){
            $check  = trim($check);
            $args   = array(
                'post_type'              => $check,
                'posts_per_page'         => $limit[0],
                'order'                  => 'DESC',
                'orderby'                => $orderby,
                'date_query'             => array(
                                            array(
                                            'after'     => $start,
                                            'inclusive' => true,
                                            'column'    => $orderby,
                                    ),
                                ),
            );
            $post_query = new WP_Query( $args );
            $foundPosts = $post_query->found_posts;

            if ( $post_query->have_posts() ) {
                echo '<div class="showheadline"><h4>'.ucfirst($check).'</h4><span class="countlabel">'.$post_query->found_posts.'</span></div>';
                echo '<ul class="ebsum_show_list">';
                $count = 0;
                while ( $post_query->have_posts() ) {
                    $post_query->the_post();
                    if($count < $max_view){
                        
                        echo '<li>
                        
                        <span>';
                        //check if label is set to show the post date or the modfied date of post
                        if($label){
                                echo get_the_modified_date();
                            }else{
                                echo get_the_date();
                            }
                        echo '</span>';

                        //check if label is set to show the new or change label. is not then every post is set to new
                        if($label){
                            if(get_the_modified_date() == get_the_date()){
                                echo '<span class="changelabelnew">neu</span>';

                            }else{
                                echo '<span class="changelabelchange">change</span>';
                            }
                        }else{
                            echo '<span class="changelabelnew">neu</span>';
                        }
                        
                        echo '<a href="'.get_permalink().'">'.esc_html(get_the_title()).'</a></li>';
                        
                        $count ++;
                    }
                    else{
                        
                        echo '<li class="hiddenposts" id="hideposts">
                        
                        <span>';
                        //check if label is set to show the post date or the modfied date of post
                        if($label){
                                echo get_the_modified_date();
                            }else{
                                echo get_the_date();
                            }
                        echo '</span>';

                        //check if label is set to show the new or change label. is not then every post is set to new
                        if($label){
                            if(get_the_modified_date() == get_the_date()){
                                echo '<span class="changelabelnew">neu</span>';

                            }else{
                                echo '<span class="changelabelchange">change</span>';
                            }
                        }else{
                            echo '<span class="changelabelnew">neu</span>';
                        }
                        
                        echo '<a href="'.get_permalink().'">'.esc_html(get_the_title()).'</a></li>';
                        
                        
                    }
                    
                }
                
                if($post_query->found_posts > $max_view){
                    echo '<div class="showmorepostbutton">
                    <button type=button id="showmoreposts" class="showmoreposts">▼</button>
                    <button type=button id="showlessposts" class="showlessposts">▲</button>
                    </div>';
                }
                echo '<br></ul>';
                
            } else{
                echo '<div class="showheadline"><h4>'.ucfirst($check).'</h4><span class="countlabel zero">0</span></div>';
                echo '<ul class="ebsum_show_list" id="ebsum_'.$check.'"></ul>';
                
            }
            
        }
    }
    
}

// set function to show the user by roles
function show_user(){
    $to_check       = get_sets('set_userroles');
    $max_view       = get_sets('max_view');
        $max_view   = $max_view[0];
    

    if($to_check[0]){
        echo "<h3><strong>Userrolles</strong></h3>";

        $limit = get_sets('max_view');
        $start = check_period();

        foreach($to_check as $check){
            $check  = trim($check);
            $args   = array(
                'role'            =>    $check,
                'posts_per_page'  =>    $limit[0],
                'order'           =>    'DESC',
                'orderby'         =>    'user_registered',
                'date_query'             => array(
                                            array(
                                            'after'     => $start,
                                            'inclusive' => true,
                                    ),
                                ),
            );
            $users = get_users( $args );
            $count = 0;
            if (count($users) > 0){
                
                    echo '<div class="showheadline"><h4>'.$check.' </h4><span class="countlabel">'.count($users).'</span></div>';
                    echo '<ul class="ebsum_show_list_user">';
                        foreach ( $users as $user ) {
                            if($count < $max_view){
                                echo '<li>
                                
                                <span>' .date("d M Y", strtotime(esc_html($user->user_registered))).'</span>

                                <span> </span>

                                <a href="users.php?s='.$user->ID.'">'. esc_html( $user->display_name ) . '[' . esc_html( $user->user_email ) . ']'.esc_html( $user->user_url ).'</a></li>';
                                $count ++;
                        }
                    
                        else{
                        
                            echo '<li class="hiddenposts" id="hideposts">
                            
                            <span>' .date("d M Y", strtotime(esc_html($user->user_registered))).'</span>

                            <span> </span>

                            <a href="users.php?s='.$user->ID.'">'. esc_html( $user->display_name ) . '[' . esc_html( $user->user_email ) . ']'.esc_html( $user->user_url ).'</a></li>';

                        }
                    }
                        if(count($users) > $max_view){
                            echo '<div class="showmorepostbutton">
                            <button type=button id="showmoreposts" class="showmoreposts">▼</button>
                            <button type=button id="showlessposts" class="showlessposts">▲</button>
                            </div>';
                        }

                    echo '</ul>';
                    
                
            }
                    else{
                        echo '<div class="showheadline"><h4>'.ucfirst($check).'</h4><span class="countlabel zero">0</span></div>';
                        echo '<ul class="ebsum_show_list_user" id="ebsum_'.$check.'"></ul>';
                    }            
        }
    }    
}



?>