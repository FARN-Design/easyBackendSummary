<?php
/**
 * Create function for looping the trough the array and make for each value an checkbox in an table and checked if selected before
 *
 * @return string with the checkboxes for user_roles and post_types.
 */
function create_post_type_setting($data_array): string
{

    $user_id = get_current_user_id();
    $posttype_setting   = '<ul class="ebs-ul"><form class="ebsum_checkbox_list" ID="' . $user_id . '" method="POST" action="" name="ebsum_set">';
    $posttype_setting  .= '<input type="hidden" name="is_submitted" value="is_submitted"></input>';
    
    foreach($data_array as $data_type => $data_type_data){
        $is_checkbox_checked     = get_db_data($data_type);
        $posttype_setting  .= '<strong>'.$data_type.'</strong>';

        foreach($data_type_data as $data){
            $data = trim($data);
            $checked = "";
            foreach ($is_checkbox_checked  as $to_check) {
                $to_check = trim($to_check);
                if ($data== $to_check) {
                    $checked = "checked";
                    break;
                }
            }
            $posttype_setting .= '<li><input type="checkbox" id="postytpe' . $data. '" name="' . $data_type . '[]" value="' . $data. '"' . $checked . '>';
            $posttype_setting .= '<label for="postytpe' . $data. '">' . $data. '</label></li>';

            if (isset($_POST[$data])) {
                echo "'" . $data. "is checked'<br>";
            };
        }
    }
    $posttype_setting .= '</form></ul>';
    $posttype_setting .= '<div class="ebsum_button_wrapper"><input form="' . $user_id . '" class="button button-primary ebsum_button" type="submit" name=" " value="save"></div><br>';

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
 * This function get the selected settings from the wp backend and will get by the js.
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
    $whole_time      = "whole_time";
    $changed     = get_db_data('change_box')[0];
    $checked    = "";

    if ($changed == 'changes') {
        $checked = "checked";
    }

    ?>
    <form ID="ebsum_main_settings" method="POST">
        <ul class="ebsum_settingslist">
            <li class="ebsum_settingslist"><label for="changes">Show changes</label>
                <input type="checkbox" id="changes" name="changes" value="changes" <?php echo $checked; ?> >
                <br></li>

            <li class="ebsum_settingslist"><label class="ebsum_quantity" for="quantity">Overview:</label>
                <input type="number" min="1" max="100" name="quantity" step="1" id="ebsum_quantitys"
                       value="<?php echo $max_view[0]; ?>">
                <br></li>

            <li class="ebsum_settingslist"><label class="ebsum_loadlimit" for="loadlimit">Limit to load:</label>
                <input type="number" min="1" max="100" name="loadlimit" step="1" id="ebsum_loadlimits"
                       value="<?php echo $load_limit[0]; ?>">
                <br></li>

            <p class="ebsum_load_warning">Please choose a value or overview wich is smaller than limit to load!</p>

            <li class="ebsum_settingslist"><p>Period to show</p>
                <select class="ebsum_period_time" name="period" id="periods">
                    <option class="ebsum_period_time"
                            value="<?php echo $last_login ?>" <?php if (trim($period[0]) == $last_login) {
                        echo ' selected';
                    } ?>>since last login
                    </option>
                    <option class="ebsum_period_time"
                            value="<?php echo $last_week ?>" <?php if (trim($period[0]) == $last_week) {
                        echo ' selected';
                    } ?>>last 7 days
                    </option>
                    <option class="ebsum_period_time"
                            value="<?php echo $last_month ?>" <?php if (trim($period[0]) == $last_month) {
                        echo ' selected';
                    } ?>>last 30 days
                    </option>
                    <option class="ebsum_period_time" value="<?php echo $whole_time ?>" <?php if (trim($period[0]) == $whole_time) {
                        echo ' selected';
                    } ?>>whole time
                    </option>
                </select><br></li>

            <input type="hidden" name="is_submitted" value="is_submitted">
            <input type="submit" value="save" class="button button-primary">
            </ul>
    </form>
    <?php
}

?>
