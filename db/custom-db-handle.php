<?php

if ( ! defined( 'ABSPATH' ) ) (exit);

/** check if form is submitted and start to write the settings to custom table
 *
 * @return void
 */
function ebsum_db_handle(): void {

	if ( ! isset( $_POST['is_submitted']) || !check_admin_referer("ebsum_nonce")) {
		return;
	} else {
		ebsum_set_data_to_db();
	}
}


/** check the _POST for key and give to the db function
 *
 * @return void
 */
function ebsum_set_data_to_db(): void {

	if (isset($_POST['post_types'][0]) && isset($_POST['user_roles'][0])) {
		ebsum_set_settings( ebsum_sanitize($_POST['post_types']), 'post_types', 'post_types' );
		ebsum_set_settings( ebsum_sanitize($_POST['user_roles']), 'user_roles', 'user_roles' );
	} elseif(isset( $_POST['user_roles'][0]) && !isset( $_POST['post_types'][0])){
		ebsum_set_settings( ebsum_sanitize($_POST['user_roles']), 'user_roles', 'user_roles' );
		ebsum_set_settings( '', 'post_types', 'post_types' );
	}elseif(!isset( $_POST['user_roles'][0]) && isset($_POST['post_types'][0])){
		ebsum_set_settings( ebsum_sanitize($_POST['post_types']), 'post_types', 'post_types' );
		ebsum_set_settings( '', 'user_roles', 'user_roles' );
	}elseif(!isset( $_POST['user_roles'][0]) && !isset( $_POST['post_types'][0]) && isset( $_POST['is_post_and_user'][0])){
		ebsum_set_settings( '', 'post_types', 'post_types' );
		ebsum_set_settings( '', 'user_roles', 'user_roles' );
	}else {
		ebsum_set_settings( ebsum_sanitize($_POST['quantity']), 'max_view', "" );
		ebsum_set_settings( ebsum_sanitize($_POST['period']), 'check_period', "" );
		ebsum_set_settings( ebsum_sanitize($_POST['loadlimit']), 'load_limit', "" );
		if ( ! isset( $_POST['changes']) )  {
			//set empty string to change_box in db if the checkbox of change is not set
			ebsum_set_settings( '', 'change_box', "" );
		} else {
			ebsum_set_settings( ebsum_sanitize($_POST['changes']), 'change_box', "" );
		}
	}
}


/** setup function save Post to db $post_array = $_POST, $key = DB Key and $value = word to replace with nothing
 *
 * @param $post_array array|string of the post data
 * @param $key string of the key for the db
 * @param $value string wich were deleted
 *
 * @return void
 */
function ebsum_set_settings( array|string $post_array, string $key, string $value ) {

	if ( is_array( $post_array ) ) {
		$string = implode( "; ", $post_array );

		$string = str_replace( $value, '', $string );
	} else {
		$string = $post_array;
	}
	$user_id = get_current_user_id();
	global $wpdb;
	$ebsum = $wpdb->prefix . 'easyBackendSummary';

	$x =  $wpdb->update(
		$ebsum,
		[ $key => $string ],
		[ 'user_ID' => $user_id ]
	);
}

/**
 * Sanitizes an input value or an array of input values by removing any characters that are not allowed in a key
 * or value of a database field.
 *
 * @param array|string $input The input value or array of input values to sanitize.
 *
 * @return string|array The sanitized input value or array of sanitized input values.
 */
function ebsum_sanitize(array|string $input):string|array{
	if (is_array($input)){
		$tmp = [];
		foreach ($input as $key => $value){
			$tmp[$key] = sanitize_key($value);
		}
		return $tmp;
	}
	return sanitize_key($input);
}

?>
