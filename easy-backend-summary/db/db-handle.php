<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require('../../../../wp-load.php');

var_dump ($_POST);


$posts = array();
$user = array();
$changes = "";
$quantity = "";
$period = "";

// Durchlaufen Sie das $_POST-Array
foreach ($_POST as $key => $value) {
    // Überprüfen Sie, ob der Schlüssel mit 'set_posttypes_' beginnt
    if (strpos($key, 'set_posttypes_') === 0) {
        $posts[] = $value;
    }
    // Überprüfen Sie, ob der Schlüssel mit 'set_userroles_' beginnt
    elseif (strpos($key, 'set_userroles_') === 0) {
        $user[] = $value;
    } 
    elseif (strpos($key, 'changes') === 0) {
        $changes = $value;
    } 
    elseif (strpos($key, 'quantity') === 0) {
        $quantity = $value;
    } 
    elseif (strpos($key, 'period') === 0) {
        $period = $value;
    } 
}

// Geben Sie die neuen Arrays aus
var_dump($posts);
var_dump($user);
var_dump($changes);
var_dump($quantity);
var_dump($period);

//function to check POST for value to setting
function check_post ($array, $value){
    $string = implode("; ", $array);
    $string = strtolower($string);
    $type = str_contains($string, $value);
    return $type;
}

// check type to save in settings
if(!($_POST) or check_post($posts, 'posttype') or check_post($user, 'user')){
    
    set_settings($posts, 'set_posttypes', 'set_posttypes');
    set_settings($user, 'set_userroles', 'set_userroles');

}elseif ($changes or $quantity or $period){
    set_settings($changes, 'change_box',"");
    set_settings($quantity, 'max_view',"");
    set_settings($period, 'check_period',"");

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
