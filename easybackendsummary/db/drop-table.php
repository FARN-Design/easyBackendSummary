<?php


/**
 * Drop Table on uninstal the plugin
 * 
 * @return string with the sql to drop custom table.
 */
function drop_table_in_database(): void
{
    global $wpdb;
    $ebsum = $wpdb->prefix . 'easyBackendSummary';
    $sql = "DROP TABLE IF EXISTS $ebsum";
    $wpdb->query($sql);
}

?>