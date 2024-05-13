<?php
/**
 * Create function for looping the trough the array and make for each value an checkbox in an table and checked if selected before
 *
 * @param $data_array with all post types and user roles
 *
 * @return void
 */
function ebsum_create_post_type_setting( $data_array ): void {

	$user_id = get_current_user_id();
	?>
    <ul class="ebs-ul">
        <form class="ebsum_checkbox_list" ID="<?php echo esc_html( $user_id ); ?>" method="POST" action=""
              name="ebsum_set">
            <?php wp_nonce_field()?>
            <input type="hidden" name="is_submitted" value="is_submitted">
            <input type="hidden" name="is_post_and_user" value="is_post_and_user">
			<?php
			foreach ( $data_array as $data_type => $data_type_data ) {
				$is_checkbox_checked = ebsum_get_db_data( $data_type );
				?>
                <strong><?php echo esc_html( $data_type ); ?> </strong>
				<?php
				foreach ( $data_type_data as $data ) {
					$data    = trim( $data );
					$checked = "";
					foreach ( $is_checkbox_checked as $to_check ) {
						$to_check = trim( $to_check );
						if ( $data == $to_check ) {
							$checked = "checked";
							break;
						}
					}
					?>
                    <li><input type="checkbox" id="postytpe<?php echo esc_html( $data ); ?>"
                               name="<?php echo esc_html( $data_type ); ?>[]"
                               value="<?php echo esc_html( $data ); ?>"<?php echo esc_html( $checked ); ?>>
                        <label for="postytpe<?php echo esc_html( $data ); ?>"><?php echo esc_html( $data ); ?></label>
                    </li>
					<?php

					if ( isset( $_POST[ $data ] ) ) {

						check_admin_referer();

						echo "'" . esc_html( $data ) . "is checked'<br>";
					};
				}
			}
			?>
        </form>
    </ul>
    <div class="ebsum_button_wrapper"><input form="<?php echo esc_html( $user_id ); ?>"
                                             class="button button-primary ebsum_button" type="submit" name=" "
                                             value="save"></div><br>
	<?php
}

/**
 * This function get the selected posttypes and userroles from custom database table and show in wp backend.
 *
 * @return void
 *
 */
function ebsum_setup_posts_and_users(): void {
	$post_types = get_post_types();
	global $wp_roles;
	$roles      = $wp_roles->roles;
	$user_slugs = array();
	foreach ( $roles as $role_slug => $role ) {
		$user_slugs[] = $role_slug;
	}
	$data_array = array( "post_types" => $post_types, "user_roles" => $user_slugs );

	ebsum_create_post_type_setting( $data_array );
}

/**
 * This function set the user id and the now time in unix timestamp to the custom database table.
 *
 * @return void
 *
 */
function ebsum_set_last_login(): void {
	// Get the current user ID
	$user_id = get_current_user_id();
	// Get the current time
	$now = get_user_meta( get_current_user_id(), "wfls-last-login", true );
	global $wpdb;
	$ebsum = $wpdb->prefix . 'easyBackendSummary';

	// Try to get the data from the cache
	$check_user_ID = wp_cache_get( $user_id, 'user_login_data' );

	// If the data is not in the cache, get it from the database
	if ( $check_user_ID === false ) {
		$check_user_ID = $wpdb->get_row( $wpdb->prepare( "SELECT `user_ID` FROM `$ebsum` WHERE `user_ID` = %d", $user_id ) );
		// Set the data in the cache
		wp_cache_set( $user_id, $check_user_ID, 'user_login_data' );
	}

	if ( isset( $check_user_ID->user_ID ) ) {
		if ( $check_user_ID->user_ID != $user_id ) {
			$wpdb->insert(
				$ebsum,
				[
					'user_ID'    => $user_id,
					'last_login' => $now,
				]
			);
			// Delete the cache as the data has changed
			wp_cache_delete( $user_id, 'user_login_data' );
		} else {
			$wpdb->update(
				$ebsum,
				[ 'last_login' => $now ],
				[ 'user_ID' => $user_id ]
			);
			// Delete the cache as the data has changed
			wp_cache_delete( $user_id, 'user_login_data' );
		}
	} else {
		$wpdb->insert(
			$ebsum,
			[
				'user_ID'    => $user_id,
				'last_login' => $now,
			]
		);
		// Delete the cache as the data has changed
		wp_cache_delete( $user_id, 'user_login_data' );
	}
}


/**
 * This function get the selected settings from the wp backend and will get by the js.
 *
 * @return void
 *
 */
function ebsum_main_settings(): void {


	//function to set change view
	$max_view   = ebsum_get_db_data( 'max_view' );
	$load_limit = ebsum_get_db_data( 'load_limit' );
	$period     = ebsum_get_db_data( 'check_period' );
	$last_login = "lastlogin";
	$last_week  = "lastweek";
	$last_month = "lastmonth";
	$whole_time = "whole_time";
	$changed    = ebsum_get_db_data( 'change_box' )[0];
	$checked    = "";

	if ( $changed == 'changes' ) {
		$checked = "checked";
	}

	?>
    <form ID="ebsum_main_settings" method="POST">
        <ul class="ebsum_settingslist">
            <li class="ebsum_settingslist"><label for="changes">Show changes</label>
                <input type="checkbox" id="changes" name="changes" value="changes" <?php echo esc_html( $checked ); ?> >
                <br></li>

            <li class="ebsum_settingslist"><label class="ebsum_quantity" for="quantity">Overview:</label>
                <input type="number" min="1" max="100" name="quantity" step="1" id="ebsum_quantitys"
                       value="<?php echo esc_html( $max_view[0] ); ?>">
                <br></li>

            <li class="ebsum_settingslist"><label class="ebsum_loadlimit" for="loadlimit">Limit to load:</label>
                <input type="number" min="1" max="100" name="loadlimit" step="1" id="ebsum_loadlimits"
                       value="<?php echo esc_html( $load_limit[0] ); ?>">
                <br></li>

            <p class="ebsum_load_warning">Please choose a value or overview wich is smaller than limit to load!</p>

            <li class="ebsum_settingslist"><p>Period to show</p>
                <select class="ebsum_period_time" name="period" id="periods">
                    <option class="ebsum_period_time"
                            value="<?php echo esc_html( $last_login ) ?>" <?php if ( trim( $period[0] ) == $last_login ) {
						echo ' selected';
					} ?>>since last login
                    </option>
                    <option class="ebsum_period_time"
                            value="<?php echo esc_html( $last_week ) ?>" <?php if ( trim( $period[0] ) == $last_week ) {
						echo ' selected';
					} ?>>last 7 days
                    </option>
                    <option class="ebsum_period_time"
                            value="<?php echo esc_html( $last_month ) ?>" <?php if ( trim( $period[0] ) == $last_month ) {
						echo ' selected';
					} ?>>last 30 days
                    </option>
                    <option class="ebsum_period_time"
                            value="<?php echo esc_html( $whole_time ) ?>" <?php if ( trim( $period[0] ) == $whole_time ) {
						echo ' selected';
					} ?>>whole time
                    </option>
                </select><br></li>

			<?php wp_nonce_field() ?>

            <input type="hidden" name="is_submitted" value="is_submitted">
            <input type="submit" value="save" class="button button-primary">
        </ul>
    </form>
	<?php
}

?>
