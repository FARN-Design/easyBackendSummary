<?php

/**
 * Create Table on install the plugin
 * 
 * @return string with the sql code to create the custom table. //TODO you dont return anything here
 */
function create_database(): void
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
        load_limit      int DEFAULT 10,
        max_view        int DEFAULT 3,
        change_box      text,
        check_period    text DEFAULT 'lastlogin',
        PRIMARY KEY (set_ID)
    )   $charset;";

    //TODO why do you need to require this here? Something is wrong!
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}

?>