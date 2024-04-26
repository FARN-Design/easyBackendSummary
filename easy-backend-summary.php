<?php

/*
 Plugin Name: Easy Backend Summary
 Plugin URI: https://www.easy-wordpress-plugins.de/
 Description: This is a dashboard plugin for the WordPress backend who shows a easy summary of the latest activity's
 Author: Farn - Digital Brand Design
 Version: 1.0.1
 Author URI: https://farn.de
 License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) (exit);

require plugin_dir_path(__FILE__) . 'db/custom-db-handle.php';
require plugin_dir_path(__FILE__) . 'db/create-drop-custom-table.php';
require plugin_dir_path(__FILE__) . 'settings/settings.php';
require plugin_dir_path(__FILE__) . 'db/wp-db-handle.php';

//-----------------------------initializing-----------------------------


/**
 * This function is used to enqueue scripts and styles for the Easy Backend Summary plugin.
 * It adds the 'easy-backend-summary-script' script and the 'easy-backend-summary-style' style to the script and style queues respectively.
 *
 * @return void
 */
function farn_enqueueScriptsAndStyles(): void
{
    wp_enqueue_script('easy-backend-summary-script', plugin_dir_url(__FILE__) . 'js/easy-backend-summary.js', array('jquery'), '1.0.1', true);
    wp_enqueue_style('easy-backend-summary-style', plugin_dir_url(__FILE__) . 'css/easy-backend-summary.css', array(),'1.0.1');
}
add_action('admin_enqueue_scripts', 'farn_enqueueScriptsAndStyles');


/**
 * This function adds a meta box to the dashboard screen with the title "Easy Backend Summary" and content generated by the "meta_callback_function" function.
 *
 * @return void
 */
function easy_backend_summary(): void
{
    add_meta_box(
        'easy_backend_summary',
        'Easy Backend Summary',
	    'ebsum_metaBox_callback_function',
        'dashboard',
        'normal',
        'high'
    );
}

add_action('wp_dashboard_setup', 'ebsum_db_handle');
add_action('wp_dashboard_setup', 'easy_backend_summary');

register_activation_hook(__FILE__, 'create_ebsum_database');
register_deactivation_hook(__FILE__, 'drop_ebsum_table_in_database');

//-----------------------------display the functions with meta box in wp backend---------------------------------


/**
 * Content of the metaBox used in the dashboard.
 *
 * @return void
 */
function ebsum_metaBox_callback_function(): void
{
    ebsum_set_last_login(); ?>
    <div class="ebsum_wrapper">
        <div class="ebsum_show_wrapper">
            <?php ebsum_show_posts(); ?>
            <?php ebsum_show_user(); ?>
        </div>
        <div class="ebsum_setting_wrapper_wrapper">
            <span class="ebsum_setting_categories_wrapper">+ Add new categories</span>
            <button type="button" id="ebsum_setting_button"><span class="dashicons dashicons-admin-generic"></span>
            </button>
        </div>
        <div class="ebsum_setting_posttypes"> 
            <?php ebsum_setup_posts_and_users(); ?>
        </div>
        <div class="ebsum_setting_wrapper">
            <div class="ebsum_setting_main">
                <?php ebsum_main_settings(); ?>
            </div>
        </div>
    </div>
    <?php
}
?>