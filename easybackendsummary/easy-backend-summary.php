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

//TODO Allgemeine Struktur A vor B oder B vor A



//-----------------------------enque js, css and set ajax-----------------------------

function farn_enqueueScriptsAndStyles(): void
{
    wp_enqueue_script('easy-backend-summary-script', plugin_dir_url(__FILE__) . 'js/easy-backend-summary.js', array('jquery'), '', true);
    wp_localize_script( 'easy-backend-summary-script', 'ebsum_ajax_data',[
        'ebsum_url' => plugin_dir_url(__FILE__) . 'db/db-handle.php',
        'nonce' => wp_create_nonce('ebsum_nonce')
    ]);

    wp_enqueue_style('easy-backend-summary-style', plugin_dir_url(__FILE__) . 'css/easy-backend-summary.css');
}

add_action('admin_enqueue_scripts', 'farn_enqueueScriptsAndStyles');


add_action('wp_ajax_show_posts', 'show_posts');
add_action('wp_ajax_nopriv_show_posts', 'show_posts');
add_action('wp_ajax_show_user', 'show_user');
add_action('wp_ajax_nopriv_show_user', 'show_user');
add_action('wp_ajax_create_post_type_setting', 'create_post_type_setting');
add_action('wp_ajax_nopriv_create_post_type_setting', 'create_post_type_setting');
add_action('wp_ajax_main_settings', 'main_settings');
add_action('wp_ajax_nopriv_main_settings', 'main_settings');

//set metabox datas
function easy_backend_summary()
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

add_action('wp_dashboard_setup', 'easy_backend_summary');

//Creat Database once by activating the Plugin
function create_database()
{
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
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
//TODO add deactivation
/*register_deactivation_hook(__FILE__, function () {
    //here
});*/

//setting all functions to show
//TODO Remove echos & English & callback in name
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


//-----------------------------setting the Post Functions-----------------------------

//create function for looping the trough the array and make for each value an checkbox in an table
function create_post_type_setting($types, $name, $roles, $rolenames)
{

    $user_id = get_current_user_id();
    $posttype_setting   = '<ul class="ebs-ul"><form class="ebsum-class" ID="' . $user_id . '" method="POST" action="" name="ebsum_set">';
    $posttype_setting  .= '<strong>Posttypes</strong>';
    $to_check_posts     = get_db_data($name);
    $to_check_roles     = get_db_data($rolenames);

    foreach ($types as $type) {
        $type = trim($type);

        $checked = "";
        foreach ($to_check_posts as $check) {
            $check = trim($check);
            if ($type == $check) {
                $checked = "checked";
                break;
            }
        }

        $posttype_setting .= '<li><input type="checkbox" id="postytpe' . $type . '" name="' . $name . '" value="' . $type . '"' . $checked . '>';
        $posttype_setting .= '<label for="postytpe' . $type . '">' . $type . '</label></li>';

        if (isset($_POST[$type])) {
            echo "'" . $type . "is checked'<br>";
        };
    }
    $posttype_setting .= "<br> <strong>Userrolles</strong>";
    foreach ($roles as $role) {
        $role = trim($role);

        $checked = "";
        foreach ($to_check_roles as $check) {
            $check = trim($check);
            if ($role == $check) {
                $checked = "checked";
                break;
            }
        }

        $posttype_setting .= '<li><input type="checkbox" id="postytpe' . $role . '" name="' . $rolenames . '" value="'. $role . '"' . $checked . '>';
        $posttype_setting .= '<label for="postytpe' . $role . '">' . $role . '</label></li>';

        if (isset($_POST[$role])) {
            echo "'" . $role . "is checked'<br>";
        };
    }

    $posttype_setting .= '</form></ul>';
    $posttype_setting .= '<div class="ebsum_button_wrapper"><input form="' . $user_id . '" class="button button-primary ebsum_button" type="submit" name=" " value="Speichern"></div><br>';

    return $posttype_setting;
}

// setup for the posttypes
function setup_posts_and_users()
{
    $types = get_post_types();
    global $wp_roles;
    $roles = $wp_roles->roles;
    $slug_array=array();
    foreach ($roles as $role_slug => $role) {
        $slug_array[]=$role_slug;
    }
    echo create_post_type_setting($types, "set_posttypes", $slug_array, "set_userroles");
}

//function to set the last login time (checks if user id allready set and then saves the last login time)
function set_last_login()
{
    $user_id = get_current_user_id();
    $now = get_user_meta(get_current_user_id(), "wfls-last-login", true);
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    //TODO Dynamische implementation
    $check_user_ID = $wpdb->get_row("SELECT `user_ID` FROM `$ebsum` WHERE `user_ID` = $user_id");

    //TODO überleg nochmal ob das wirklich so sein muss
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


//Function for all settings (changes, periods, limits)
//TODO move [0] to the declaration
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
    <form ID="main_settings">
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

            <input type="submit" value="Speichern" class="button button-primary">
            </ul>
    </form>
    <?php
}


//-----------------------------get data from database functions-----------------------------


//get the settings from databes
//TODO anderer Name
function get_db_data($key)
{
    $user_id = get_current_user_id();
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    //TODO dynamic implementation
    $datas = $wpdb->get_row("SELECT `$key` FROM `$ebsum` WHERE `user_ID` = $user_id");
    $datas = (array)$datas;
    $datas = implode(";", $datas);
    $datas = trim($datas);
    $datas = explode(";", $datas);
    return $datas;
}


/**
 * This function get the setted period from the database an transform it to a date.
 *
 * @return string with the current period as Date representation.
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

//TODO [0]
// set function to show the post by posttype
function show_posts()
{
    $to_check = get_db_data('set_posttypes');

    if ($to_check[0]) {
        echo "<h3><strong>Posttypes</strong></h3>";

        $limit = get_db_data('load_limit')[0];
        $max_view = get_db_data('max_view')[0];
        $start = check_period();

        // check if view of change is activ or not. if it is then the ordby by will change to modifed date and the modified date will show in collum
        $changed = get_db_data('change_box')[0];
        if ($changed == 'changes') {
            $orderby = "post_modified";
            $label = true; //TODO Rename labe to state like name. "ShowLable"
        } else {
            $orderby = "post_date";
            $label = false;
        }

        //TODO Rename check to checked_post_type
        foreach ($to_check as $check) {
            $check = trim($check);
            $args = array(
                'post_type' => $check,
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
            //TODO $foundPosts = $post_query->found_posts;

            if ($post_query->have_posts()) {
                echo '<div class="showheadline"><h4>' . ucfirst($check) . '</h4><span class="countlabel">' . $post_query->found_posts . '</span></div>';
                echo '<ul class="ebsum_show_list">';
                $count = 0;
                while ($post_query->have_posts()) {
                    $post_query->the_post();
                    if ($count < $max_view) {
                        echo '<li><span>';
                    } else {
                        echo '<li class="hiddenposts" id="hideposts"><span>';
                    }
                    //TODO check if that works
                    //check if label is set to show the post date or the modfied date of post
                    if ($label) {
                        echo get_the_modified_date();
                    } else {
                        echo get_the_date();
                    }
                    echo '</span>';

                    //check if label is set to show the new or change label. is not then every post is set to new
                    //TODO fix names
                    if ($label) {
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
                echo '<div class="showheadline"><h4>' . ucfirst($check) . '</h4><span class="countlabel zero">0</span></div>';
                echo '<ul class="ebsum_show_list" id="ebsum_' . $check . '"></ul>';
            }

        }
    }

}

// set function to show the user by roles
function show_user()
{


    $to_check = get_db_data('set_userroles');
    $max_view = get_db_data('max_view');
    $max_view = $max_view[0];


    if ($to_check[0]) {
        echo "<h3><strong>Userrolles</strong></h3>";

        $limit = get_db_data('max_view')[0];
        $start = check_period();

        foreach ($to_check as $check) {
            $check = trim($check);
            $args = array(
                'role' => $check,
                'posts_per_page' => $limit,
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

                echo '<div class="showheadline"><h4>' . ucfirst($check) . ' </h4><span class="countlabel">' . count($users) . '</span></div>';
                echo '<ul class="ebsum_show_list_user">';
                foreach ($users as $user) {
                    if ($count < $max_view) {
                        echo '<li>
                                
                                <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>

                                <span> </span>

                                <a href="users.php?s=' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a></li>';
                        $count++;
                    } else {

                        echo '<li class="hiddenposts" id="hideposts">
                            
                            <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>

                            <span> </span>

                            <a href="users.php?s=' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a></li>';

                    }
                }
                if (count($users) > $max_view) {
                    echo '<div class="showmorepostbutton">
                            <button type=button id="showmoreposts" class="showmoreposts">▼</button>
                            <button type=button id="showlessposts" class="showlessposts">▲</button>
                            </div>';
                }

                echo '</ul>';


            } else {
                echo '<div class="showheadline"><h4>' . ucfirst($check) . '</h4><span class="countlabel zero">0</span></div>';
                echo '<ul class="ebsum_show_list_user" id="ebsum_' . $check . '"></ul>';
            }
        }
    }
}

?>