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

 require "db/db-handle.php";
 require "db/create-table.php";
 require "db/drop-table.php";
 
//-----------------------------initializing-----------------------------

//  -enque js, css and set ajax---

function farn_enqueueScriptsAndStyles(): void
{
    wp_enqueue_script('easy-backend-summary-script', plugin_dir_url(__FILE__) . 'js/easy-backend-summary.js', array('jquery'), '', true);
    wp_enqueue_style('easy-backend-summary-style', plugin_dir_url(__FILE__) . 'css/easy-backend-summary.css');
}

add_action('admin_enqueue_scripts', 'farn_enqueueScriptsAndStyles');

//set metabox data
function easy_backend_summary(): void
{
    add_meta_box(
        'easy_backend_summary',
        'Easy Backend Summary',
        'meta_callback_function',
        'dashboard',
        'normal',
        'high'
    );
}

add_action('wp_dashboard_setup', 'db_handle');
add_action('wp_dashboard_setup', 'easy_backend_summary');
register_activation_hook(__FILE__, 'create_database');
register_deactivation_hook(__FILE__, 'drop_table_in_database');



//-----------------------------settings---------------------------------


/**
 * Create function for looping the trough the array and make for each value an checkbox in an table and checked if selected before
 *
 * @return string with the checkboxes for user_roles and post_types.
 */
function create_post_type_setting($data_array): string
{

    $user_id = get_current_user_id();
    $posttype_setting   = '<ul class="ebs-ul"><form class="ebsum-class" ID="' . $user_id . '" method="POST" action="" name="ebsum_set">';
    $posttype_setting  .= '<input type="hidden" name="is_submitted" value="is_submitted"></input>';
    
    foreach($data_array as $key => $data_type){
        $is_checkbox_checked     = get_db_data($key);
        $posttype_setting  .= '<strong>'.$key.'</strong>';

        foreach($data_type as $data){
            $data = trim($data);
            $checked = "";
            foreach ($is_checkbox_checked  as $to_check) {
                $to_check = trim($to_check);
                if ($data== $to_check) {
                    $checked = "checked";
                    break;
                }
            }
            $posttype_setting .= '<li><input type="checkbox" id="postytpe' . $data. '" name="' . $key . '[]" value="' . $data. '"' . $checked . '>';
            $posttype_setting .= '<label for="postytpe' . $data. '">' . $data. '</label></li>';

            if (isset($_POST[$data])) {
                echo "'" . $data. "is checked'<br>";
            };
        }
    }
    $posttype_setting .= '</form></ul>';
    $posttype_setting .= '<div class="ebsum_button_wrapper"><input form="' . $user_id . '" class="button button-primary ebsum_button" type="submit" name=" " value="Speichern"></div><br>';

    return $posttype_setting;
}

/**
 * This function get the selected posttypes and userroles from custom database table and show in wp backend.
 */
function setup_posts_and_users(): void
{
    $post_types = get_post_types();
    global $wp_roles;
    $roles = $wp_roles->roles;
    $user_slugs=array();
    foreach ($roles as $role_slug => $role) {
        $user_slugs[]=$role_slug;
    }
    $data_array = array("post_types"=>$post_types, "user_roles"=>$user_slugs);
    
    echo create_post_type_setting($data_array);
}

/**
 * This function set the user id and the now time in unix timestamp to the custom database table.
 */
function set_last_login(): void
{
    $user_id = get_current_user_id();
    $now = get_user_meta(get_current_user_id(), "wfls-last-login", true);
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    $check_user_ID = $wpdb->get_row("SELECT `user_ID` FROM `$ebsum` WHERE `user_ID` = $user_id");

   
    if (isset($check_user_ID->user_ID)) {
        if ($check_user_ID->user_ID != $user_id) {
            $wpdb->insert(
                $ebsum,
                [
                    'user_ID' => $user_id,
                    'last_login' => $now,
                ]
            );
        } else {
            $wpdb->update(
                $ebsum,
                ['last_login' => $now],
                ['user_ID' => $user_id]

            );
        }
    } else {
        $wpdb->insert(
            $ebsum,
            [
                'user_ID' => $user_id,
                'last_login' => $now,
            ]
        );
    }
}


/**
 * This function get the selected settings from the wp backend and wiill get by the js.
 */
function main_settings(): void
{

    
    //function to set change view
    $max_view   = get_db_data('max_view');
    $load_limit  = get_db_data('load_limit');
    $period     = get_db_data('check_period');
    $last_login  = "lastlogin";
    $last_week   = "lastweek";
    $last_month  = "lastmonth";
    $whole      = "whole"; //TODO Rename whole_timeframe
    $changed     = get_db_data('change_box')[0]; //TODO Rename changed
    $checked    = "";

    if ($changed == 'changes') {
        $checked = "checked";
    }

    ?>
    <form ID="main_settings" method="POST">
        <ul class="settingslist">
            <li class="settingslist"><label for="changes">Änderungen anzeigen</label>
                <input type="checkbox" id="changes" name="changes" value="changes" <?php echo $checked; ?> >
                <br></li>

            <li class="settingslist"><label class="quantity" for="quantity">Übersicht:</label>
                <input type="number" min="1" max="100" name="quantity" step="1" id="quantitys" default="3"
                       value="<?php echo $max_view[0]; ?>">
                <br></li>

            <li class="settingslist"><label class="loadlimit" for="loadlimit">max. Anzahl:</label>
                <input type="number" min="1" max="100" name="loadlimit" step="1" id="loadlimits" default="10"
                       value="<?php echo $load_limit[0]; ?>">
                <br></li>

            <p class="load_warning">Bitte setzte einen Wert für Übersicht der kleiner oder gleich dem Wert der max. Anzahl ist!</p>

            <li class="settingslist"><p>Anzeigeperiode</p>
                <select class="period_time" name="period" id="periods">
                    <option class="period_time"
                            value="<?php echo $last_login ?>" <?php if (trim($period[0]) == $last_login) {
                        echo ' selected';
                    } ?>>Seit dem letzten Login
                    </option>
                    <option class="period_time"
                            value="<?php echo $last_week ?>" <?php if (trim($period[0]) == $last_week) {
                        echo ' selected';
                    } ?>>Innerhalb der letzen 7 Tage
                    </option>
                    <option class="period_time"
                            value="<?php echo $last_month ?>" <?php if (trim($period[0]) == $last_month) {
                        echo ' selected';
                    } ?>>Innerhalb der letzen 30 Tage
                    </option>
                    <option class="period_time" value="<?php echo $whole ?>" <?php if (trim($period[0]) == $whole) {
                        echo ' selected';
                    } ?>>Gesamter Zeitraum
                    </option>
                </select><br></li>

            <input type="hidden" name="is_submitted" value="is_submitted"></input>
            <input type="submit" value="Speichern" class="button button-primary">
            </ul>
    </form>
    <?php
}



//-----------------------------get data from db and return---------------------------------

/** This Function return all values stored for a given key
 *
 *@return string $key with all settings, selected userroles and posttypes form the custom table.
 */
function get_db_data($key):array
{
    $user_id = get_current_user_id();
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    $datas = $wpdb->get_row("SELECT `$key` FROM `$ebsum` WHERE `user_ID` = $user_id");
    $datas = (array)$datas;
    $datas = implode(";", $datas);
    $datas = trim($datas);
    $datas = explode(";", $datas);
    return $datas;
}


/**
 * This function get the period from the database and transforms it to a date.
 *
 * @return string with the current period as Date representation.
 * TODO look into PHP DateTime objects. They make it easy to handle dates.
 * TODO this code must be able to support different timezones.
 */
function check_period(): string
{
    $timestamp = get_db_data('last_login')[0];
    $period = get_db_data('check_period')[0];
    $start = "";

    switch ($period) {
        case 'lastlogin':
            $start = gmdate("Y-m-d", $timestamp);
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
            $start = "0000-00-00";
    }

    return $start;

}



/**
 * This function get the selected posttype data from database.
 *
 * echo string with the selected posttypes.
 */
function show_posts(): void
{
    $to_check = get_db_data('post_types');

    if ($to_check[0]) {
        echo "<div><h3><strong>Posttypes</strong></h3>";

        $limit = get_db_data('load_limit')[0];
        $max_view = get_db_data('max_view')[0];
        $start = check_period();

        // check if view of change is activ or not. if it is then the ordby by will change to modifed date and the modified date will show in collum
        $changed = get_db_data('change_box')[0];
        if ($changed == 'changes') {
            $orderby = "post_modified";
            $schow_label = true; //TODO Rename labe to state like name. "ShowLable"
        } else {
            $orderby = "post_date";
            $schow_label = false;
        }

        //TODO Rename check to checked_post_type
        foreach ($to_check as $checked) {
            $checked = trim($checked);
            $args = array(
                'post_type' => $checked,
                'posts_per_page' => $limit,
                'order' => 'DESC',
                'orderby' => $orderby,
                'date_query' => array(
                    array(
                        'after' => $start,
                        'inclusive' => true,
                        'column' => $orderby,
                    ),
                ),
            );
            $post_query = new WP_Query($args);
            $foundPosts = $post_query->found_posts;

            if ($post_query->have_posts()) {
                echo '<div class="showheadline"><h4>' . ucfirst($checked) . '</h4><span class="countlabel">' . $foundPosts . '</span></div>';
                echo '<ul class="ebsum_show_list">';
                $count = 0;
                while ($post_query->have_posts()) {
                    $post_query->the_post();
                    if ($count < $max_view) {
                        echo '<li><span>';
                    } else {
                        echo '<li class="hiddenposts" id="hideposts"><span>';
                    }
                    //check if schow_label is set to show the post date or the modfied date of post
                    if ($schow_label) {
                        echo get_the_modified_date();
                    } else {
                        echo get_the_date();
                    }
                    echo '</span>';

                    //check if schow_label is set to show the new or change schow_label. is not then every post is set to new
                    if ($schow_label) {
                        if (get_the_modified_date() == get_the_date()) {
                            echo '<span class="changelabelnew">neu</span>';

                        } else {
                            echo '<span class="changelabelchange">change</span>';
                        }
                    } else {
                        echo '<span class="changelabelnew">neu</span>';
                    }

                    echo '<a href="' . get_permalink() . '">' . esc_html(get_the_title()) . '</a></li>';

                    $count++;
                }

                if ($post_query->found_posts > $max_view) {
                    echo '<div class="showmorepostbutton">
                    <button type=button id="showmoreposts" class="showmoreposts">▼</button>
                    <button type=button id="showlessposts" class="showlessposts">▲</button>
                    </div>';
                }
                echo '<br></ul>';

            } else {
                echo '<div class="showheadline"><h4>' . ucfirst($checked) . '</h4><span class="countlabel zero">0</span></div>';
                echo '<ul class="ebsum_show_list" id="ebsum_' . $checked . '"></ul>';
            }


        }
        echo '</div>';
    }

}

/**
 * This function get the selected userroles data from database and echo it.
 *
 */
function show_user(): void
{


    $to_check = get_db_data('user_roles');
    $max_view = get_db_data('max_view')[0];
    $limit = get_db_data('load_limit')[0];
    $start = check_period();

    if ($to_check[0]) {
        echo "<div><h3><strong>Userrolles</strong></h3>";

        foreach ($to_check as $checked) {
            $checked = trim($checked);
            $args = array(
                'role' => $checked,
                'number' => $limit,
                'order' => 'DESC',
                'orderby' => 'user_registered',
                'date_query' => array(
                    array(
                        'after' => $start,
                        'inclusive' => true,
                    ),
                ),
            );
            $users = get_users($args);
            
            $count = 0;
            if (count($users) > 0) {

                echo '<div class="showheadline"><h4>' . ucfirst($checked) . ' </h4><span class="countlabel">' . count($users) . '</span></div>';
                echo '<ul class="ebsum_show_list_user">';
                foreach ($users as $user) {
                    if ($count < $max_view) {
                        echo '<li>
                                
                                <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>

                                <span> </span>

                                <a href="users.php?s=' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a></li>';
                        
                    } else {

                        echo '<li class="hiddenposts" id="hideposts">
                            
                            <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>

                            <span> </span>

                            <a href="users.php?s=' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a></li>';
                            
                    }
                    $count++;
                }
                if (count($users) > $max_view) {
                    echo '<div class="showmorepostbutton">
                            <button type=button id="showmoreposts" class="showmoreposts">▼</button>
                            <button type=button id="showlessposts" class="showlessposts">▲</button>
                            </div>';
                }


                echo '<br></ul>';



            } else {
                echo '<div class="showheadline"><h4>' . ucfirst($checked) . '</h4><span class="countlabel zero">0</span></div>';
                echo '<ul class="ebsum_show_list_user" id="ebsum_' . $checked . '"></ul>';
            }
           
            
        }
        echo '</div>';
    }
}



//-----------------------------display the functions with meta box in wp backend---------------------------------

/**
 * This function displays in the meta box to show the value of all functions in widget
 *
 */
function meta_callback_function(): void
{
    set_last_login();
    ?>
    <div class="ebsum_wrapper">

        <div class="ebsum_show_wrapper">

            <?php echo show_posts(); ?>

            <?php echo show_user(); ?>

        </div>

        <div class="setting_wrapper_wrapper">

            <span class="setting_categories_wrapper">+ weitere Kategorien hinzufügen</span>
            <button type="button" id="ebsum_setting_button"><span class="dashicons dashicons-admin-generic"></span>
            </button>

        </div>
        <div class="setting_posttypes"> <?php echo setup_posts_and_users(); ?>     </div>

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
?>