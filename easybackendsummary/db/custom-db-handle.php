<?php

/** check if form is submitted and start to write the settings to custom table
 *
 */
function db_handle(): void {
	if ( ! isset( $_POST['is_submitted'] ) ) {
		return;
	}
	set_data_to_db( $_POST );
}


/** check the _POST for key and give to the db function
 *
 * @param
 *
 * @return void
 */
function set_data_to_db( $post_array ): void {

	if ( isset( $post_array['post_types'][0] ) || isset( $post_array['user_roles'][0] ) ) {
		set_settings( $post_array['post_types'], 'post_types', 'post_types' );
		set_settings( $post_array['user_roles'], 'user_roles', 'user_roles' );
	} else {
		set_settings( $post_array['quantity'], 'max_view', "" );
		set_settings( $post_array['period'], 'check_period', "" );
		set_settings( $post_array['loadlimit'], 'load_limit', "" );
		if ( ! isset( $post_array['changes'] ) ) {
			//TODO Prüfen und wenn nötig mit " " dokumentieren!
			set_settings( ' ', 'change_box', "" );
		} else {
			set_settings( $post_array['changes'], 'change_box', "" );
		}
	}
}


/** setup function save Post to db $post_array = $_POST, $key = DB Key and $value = word to replace with nothing
 *
 * @param $post_array
 * @param $key
 * @param $value
 *
 * @return void
 */
function set_settings( $post_array, $key, $value ): void {

	if ( is_array( $post_array ) ) {
		$string = implode( "; ", $post_array );

		$string = str_replace( $value, '', $string );
	} else {
		$string = $post_array;
	}
	$user_id = get_current_user_id();
	global $wpdb;
	$ebsum = $wpdb->prefix . 'easyBackendSummary';
	$wpdb->update(
		$ebsum,
		[ $key => $string ],
		[ 'user_ID' => $user_id ]
	);
}

?>
