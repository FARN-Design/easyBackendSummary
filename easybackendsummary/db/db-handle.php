<?php

//TODO description and return type
function db_handle(){
    if(!isset($_POST['is_submitted'])){
        return;
    }
    set_data_to_db($_POST);
}

//TODO description and return type
function set_data_to_db($post_array){


    if (isset($post_array['post_types'][0]) || isset($post_array['user_roles'][0]) ){
        set_settings($post_array['post_types'], 'post_types', 'post_types');
        set_settings($post_array['user_roles'], 'user_roles', 'user_roles');
    } else {
        set_settings($post_array['quantity'], 'max_view',"");
        set_settings($post_array['period'], 'check_period',"");
        set_settings($post_array['loadlimit'], 'load_limit', "");
        if(!isset($post_array['changes'])){
            set_settings(' ', 'change_box',"");
        }else{
            set_settings($post_array['changes'], 'change_box',"");
        }
    }
}

// setup function save Post to db $post_array = $_POST, $key = DB Key and $value = word to replace with nothing
//TODO Documentation and return type
function set_settings($post_array, $key, $value){
    
    if(is_array($post_array)){
    $string = implode("; ", $post_array);

    $string = str_replace($value, '', $string);
    } else{
    $string = $post_array;
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
