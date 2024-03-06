<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require('../../../../wp-load.php');

// var_dump ($_POST);
// echo implode(" ", $_POST);

//function to check POST for value to setting
function check_post ($array, $value){
    $string = implode("; ", $array);
    $string = strtolower($string);
    $type = str_contains($string, $value);
    return $type;
}

// check type to save in settings
if(check_post($_POST, 'posttype')){
      
    set_settings($_POST, 'set_posttypes', 'set_posttypes');
    

} elseif (check_post ($_POST, 'user')){
    
    set_settings($_POST, 'set_userroles', 'set_userroles');
}elseif (isset($_POST['period'])){
    set_settings($_POST, 'check_period', '');
}elseif (isset($_POST['Quantity'])){
    set_settings($_POST, 'max_view', '');
}



// setup function save Post to db $array = $_POST, $key = DB Key and $value = word to replace with nothing
function set_settings($array, $key, $value){
    $string = implode("; ", $array);
    $string = str_replace($value, '', $string); 
    $user_id = get_current_user_id();
    global $wpdb;
    $ebsum = $wpdb->prefix.'easyBackendSummary';
    $wpdb->update(
        $ebsum,
        [$key       => $string],  // die neuen Werte
        ['user_ID'  => $user_id]  // die Bedingung
    );
}


?>
