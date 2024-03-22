<?php
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
 * TODO this code must be able to support different timezones.?????????????????????
 */
function check_period(): string
{
    $timestamp = get_db_data('last_login')[0];
    $period = get_db_data('check_period')[0];
    $default_date_format = get_option('date_format');
    $start = "";
    switch ($period) {
        case 'lastlogin':
            $start = gmdate($default_date_format, $timestamp);
            break;
        case 'lastweek':
            $start = date($default_date_format, strtotime('-7 day'));
            break;
        case 'lastmonth':
            $start = date($default_date_format, strtotime('-30 day'));
            break;
        case 'whole_time':
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
        $limit      = get_db_data('load_limit')[0];
        $max_view   = get_db_data('max_view')[0];
        $changed    = get_db_data('change_box')[0];
        $start      = check_period();
        
        if ($changed == 'changes') {
            $orderby = "post_modified";
            $schow_label = true;
        } else {
            $orderby = "post_date";
            $schow_label = false;
        }
        echo "<div><h3><strong>Posttypes</strong></h3>";
        foreach ($to_check as $checked) {
            $checked    = trim($checked);
            $args       = array(
                'post_type'         => $checked,
                'posts_per_page'    => $limit,
                'order'             => 'DESC',
                'orderby'           => $orderby,
                'date_query'        => array(
                                            array(
                                                'after'     => $start,
                                                'inclusive' => true,
                                                'column'    => $orderby,
                                            ),
                                        ),
            );
            $post_query = new WP_Query($args);
            $foundPosts = $post_query->found_posts;

            if ($post_query->have_posts()) {
                echo '<div class="ebsum_showheadline"><h4>' . ucfirst($checked) . '</h4><span class="ebsum_countlabel">' . $foundPosts . '</span></div>';
                echo '<ul class="ebsum_show_list">';
                $count = 0;
                while ($post_query->have_posts()) {
                    $post_query->the_post();
                    if ($count < $max_view) {
                        echo '<li><span>';
                    } else {
                        echo '<li class="ebsum_hiddenposts" id="ebsum_hideposts"><span>';
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
                            echo '<span class="ebsum_new_label">new</span>';

                        } else {
                            echo '<span class="ebsum_change_label">change</span>';
                        }
                    } else {
                        echo '<span class="ebsum_new_label">new</span>';
                    }

                    echo '<a href="' . get_permalink() . '">' . esc_html(get_the_title()) . '</a></li>';
                    $count++;
                }
                if ($post_query->found_posts > $max_view) {
                    echo '<div class="ebsum_showmorepostbutton">
                    <button type=button id="ebsum_showmoreposts" class="ebsum_showmoreposts">▼</button>
                    <button type=button id="ebsum_showlessposts" class="ebsum_showlessposts">▲</button>
                    </div>';
                }
                echo '<br></ul>';

            } else {
                echo '<div class="ebsum_showheadline"><h4>' . ucfirst($checked) . '</h4><span class="ebsum_countlabel ebsum_zero">0</span></div>';
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
    $to_check   = get_db_data('user_roles');
    $max_view   = get_db_data('max_view')[0];
    $limit      = get_db_data('load_limit')[0];
    $start      = check_period();

    if ($to_check[0]) {
        echo "<div><h3><strong>Userrolles</strong></h3>";

        foreach ($to_check as $checked) {
            $checked    = trim($checked);
            $args       = array(
                'role'          => $checked,
                'number'        => $limit,
                'order'         => 'DESC',
                'orderby'       => 'user_registered',
                'date_query'    => array(
                                        array(
                                            'after'     => $start,
                                            'inclusive' => true,
                                        ),
                                    ),
            );
            $users = new WP_User_Query($args);
        
            if (!empty($users->get_results())) {
                echo '<div class="ebsum_showheadline"><h4>' . ucfirst($checked) . '</h4><span class="ebsum_countlabel">' . $users->get_total() . '</span></div>';
                echo '<ul class="ebsum_show_list">';
                $count = 0;
                foreach ($users->get_results() as $user) {
                    if ($count < $max_view) {
                        echo '<li>
                                <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>
                                <span> </span>
                                <a href="users.php?s=' . $user->user_email . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a>
                              </li>';
                        
                    } else {

                        echo '<li class="ebsum_hiddenposts" id="ebsum_hideposts">
                            
                            <span>' . date("d M Y", strtotime(esc_html($user->user_registered))) . '</span>

                            <span> </span>

                            <a href="users.php?s=' . $user->user_email . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . '] ' . esc_html($user->user_url) . '</a></li>';
                            
                    }
                    $count++;
                }
                if ($users->get_total() > $max_view) {
                    echo '<div class="ebsum_showmorepostbutton">
                            <button type=button id="ebsum_showmoreposts" class="ebsum_showmoreposts">▼</button>
                            <button type=button id="ebsum_showlessposts" class="ebsum_showlessposts">▲</button>
                            </div>';
                }
                echo '<br></ul>';

            } else {
                echo '<div class="ebsum_showheadline"><h4>' . ucfirst($checked) . '</h4><span class="ebsum_countlabel ebsum_zero">0</span></div>';
                echo '<ul class="ebsum_show_list_user" id="ebsum_' . $checked . '"></ul>';
            }
        }
        echo '</div>';
    }
}

?>
