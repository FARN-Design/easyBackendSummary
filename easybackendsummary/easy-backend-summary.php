<?php
/**
 * @package easy backend summary by farn
 * @version 1.0.1
 */

/*
 Plugin Name: Easy Backend Summary
 Plugin URI: https://farn.de 
 Description: This is a a dashboard plugin for the wordpress backend who shows a easy summary of the latest activitys
 Author: Farn - Digital Brand Design
 Version: 1.0.1
 Author URI: https://farn.de
 */

 require 'db/custom-db-handle.php';
 require 'db/create-drop-costum-table.php';
 require 'settings/settings.php';
 require 'db/wp-db-handle.php';
 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 
//-----------------------------initializing-----------------------------


/**
 * Enque the scripts and styles
 *
 */
function farn_enqueueScriptsAndStyles(): void
{
    wp_enqueue_script('easy-backend-summary-script', plugin_dir_url(__FILE__) . 'js/easy-backend-summary.js', array('jquery'), '', true);
    wp_enqueue_style('easy-backend-summary-style', plugin_dir_url(__FILE__) . 'css/easy-backend-summary.css');
}
add_action('admin_enqueue_scripts', 'farn_enqueueScriptsAndStyles');

/**
 * Set meta box data
 *
 */
function easy_backend_summary(): void
{
    add_meta_box(
        'easy_backend_summary',
        'Easy Backend Summary',
        'meta_callback_function',
        'dashboard',
        'normal',
        'high'
    );
}

add_action('wp_dashboard_setup', 'db_handle');
add_action('wp_dashboard_setup', 'easy_backend_summary');
register_activation_hook(__FILE__, 'create_database');
register_deactivation_hook(__FILE__, 'drop_table_in_database');

//-----------------------------display the functions with meta box in wp backend---------------------------------

/**
 * This function displays in the meta box to show the value of all functions in widget
 *
 */
function meta_callback_function(): void
{
    set_last_login(); ?>
    <div class="ebsum_wrapper">
        <div class="ebsum_show_wrapper">
            <?php echo show_posts(); ?>
            <?php echo show_user(); ?>
        </div>
        <div class="ebsum_setting_wrapper_wrapper">
            <span class="ebsum_setting_categories_wrapper">+ Add new categories</span>
            <button type="button" id="ebsum_setting_button"><span class="dashicons dashicons-admin-generic"></span>
            </button>
        </div>
        <div class="ebsum_setting_posttypes"> 
            <?php echo setup_posts_and_users(); ?>
        </div>
        <div class="ebsum_setting_wrapper">
            <div class="ebsum_setting_main">
                <?php echo main_settings(); ?>
            </div>
        </div>
    </div>
    <?php
}
?>