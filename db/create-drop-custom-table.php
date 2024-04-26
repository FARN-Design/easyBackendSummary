<?php

/**
 * This function will create the sql command to CREATE the custom table for the Plugin Settings
 * 
 * @return void
 */
function ebsum_create_ebsum_database(): void
{
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS " . $ebsum . "(
        set_ID          int     NOT NULL AUTO_INCREMENT,
        user_ID         int     UNIQUE,
        last_login      BIGINT,
        post_types   text,
        user_roles   text,
        load_limit      int DEFAULT 10,
        max_view        int DEFAULT 3,
        change_box      text,
        check_period    text DEFAULT 'lastlogin',
        PRIMARY KEY (set_ID)
    )   $charset;";
    
    dbDelta($sql);
}

/**
 * This function will create the sql command to DROP the custom table for the Plugin Settings
 * 
 * @return void
 */
function ebsum_drop_ebsum_table_in_database(): void
{
    global $wpdb;
$ebsum = esc_sql($wpdb->prefix . 'easyBackendSummary');
$wpdb->query("DROP TABLE IF EXISTS `$ebsum`");


}

?>