<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require('../../../../wp-load.php');


$str = ($_POST['formData']);



function parseString($str) {
    $pairs = explode('&', $str);
    $result = array();
    foreach ($pairs as $pair) {
        $keyValue = explode('=', $pair);
        $key = $keyValue[0];
        $value = $keyValue[1];
        if (!isset($result[$key])) {
            $result[$key] = array();
        }
        array_push($result[$key], $value);
    }
    return $result;
}


$array = parseString($str);

print_r ($array);

if (isset($array['set_posttypes'][0]) || isset($array['set_userroles'][0]) ){
$posts = ($array['set_posttypes']);
$user = ($array['set_userroles']);

var_dump ($posts);
var_dump ($user);
set_settings($posts, 'set_posttypes', 'set_posttypes');
set_settings($user, 'set_userroles', 'set_userroles');
} else {
$changed = ($array['changes'][0]);
$quantity = ($array['quantity'][0]);
$loadlimit = ($array['loadlimit'][0]);
$period = ($array['period'][0]);

set_settings($changed, 'change_box',"");
    set_settings($quantity, 'max_view',"");
    set_settings($period, 'check_period',"");
    set_settings($loadlimit, 'load_limit', "");
}


// setup function save Post to db $array = $_POST, $key = DB Key and $value = word to replace with nothing
function set_settings($array, $key, $value){
    
    if(is_array($array)){
    $string = implode("; ", $array);

    $string = str_replace($value, '', $string);
    } else{
    $string = $array;
    }
    $user_id = get_current_user_id();
    global $wpdb;
    $ebsum = $wpdb->prefix.'easyBackendSummary';
    $wpdb->update(
        $ebsum,
        [$key       => $string],  
        ['user_ID'  => $user_id] 
    );
}






?>
