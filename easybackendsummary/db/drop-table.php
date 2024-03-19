<?php


/**
 * Drop Table on uninstall the plugin
 * TODO Why is this one function in a separate file?
 * 
 * @return string with the sql to drop custom table. TODO you dont return anything here
 */
function drop_table_in_database(): void
{
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    $sql = "DROP TABLE IF EXISTS $ebsum";
    $wpdb->query($sql);
}

?>