<?php
/** This Function return all values stored for a given key
 *
 * @param string $key
 *
 * @return string[] $key with all settings, selected userRoles and postTypes form the custom table.
 */
function get_db_data( string $key ): array {
	$user_id = get_current_user_id();
	global $wpdb;
	$ebsum = $wpdb->prefix . 'easyBackendSummary';

	$data = $wpdb->get_row( "SELECT `$key` FROM `$ebsum` WHERE `user_ID` = $user_id" );
	$data = (array) $data;
	$data = implode( ";", $data );
	$data = trim( $data );
	$data = explode( ";", $data );

	return $data;
}

/**
 * This function get the period from the database and transforms it to a date.
 *
 * @return string with the current period as Date representation.
 */
function check_period(): string {
	$timestamp           = get_db_data( 'last_login' )[0];
	$period              = get_db_data( 'check_period' )[0];
	$default_date_format = get_option( 'date_format' );

	return match ( $period ) {
		'lastlogin' => gmdate( $default_date_format, $timestamp ),
		'lastweek' => gmdate( $default_date_format, strtotime( '-7 day' ) ),
		'lastmonth' => gmdate( $default_date_format, strtotime( '-30 day' ) ),
		default => "0000-00-00" //Show all results (whole-time)
	};
}


/**
 * This function get the selected posttype data from database.
 *
 * @return void
 */
function show_posts(): void {
	$to_check = get_db_data( 'post_types' );

	if ( $to_check[0] ) {
		$limit    = get_db_data( 'load_limit' )[0];
		$max_view = get_db_data( 'max_view' )[0];
		$changed  = get_db_data( 'change_box' )[0];
		$start    = check_period();

		if ( $changed == 'changes' ) {
			$orderBy     = "post_modified";
			$show_label = true;
		} else {
			$orderBy     = "post_date";
			$show_label = false;
		}
		?>
		<div><h3><strong>PostTypes</strong></h3>
		<?php
		foreach ( $to_check as $checked ) {
			$checked    = trim( $checked );
			$args       = array(
				'post_type'      => $checked,
				'posts_per_page' => $limit,
				'order'          => 'DESC',
				'orderBy'        => $orderBy,
				'date_query'     => array(
					array(
						'after'     => $start,
						'inclusive' => true,
						'column'    => $orderBy,
					),
				),
			);
			$post_query = new WP_Query( $args );
			$foundPosts = $post_query->found_posts;

			if ( $post_query->have_posts() ) {
				?>
				<div class="ebsum_showheadline"><h4><?php echo esc_html(ucfirst($checked));?></h4><span class="ebsum_countlabel"><?php echo esc_html($foundPosts);?></span></div>
				<ul class="ebsum_show_list">
				<?php
				$count = 0;
				while ( $post_query->have_posts() ) {
					$post_query->the_post();
					if ( $count < $max_view ) {
						?>
						<li><span>
						<?php
					} else {
						?>
						<li class="ebsum_hiddenposts" id="ebsum_hideposts"><span>
						<?php
					}
					//check if show_label is set to show the post date or the modified date of post
					if ( $show_label ) {
						echo esc_html(get_the_modified_date());
					} else {
						echo esc_html(get_the_date());
					}
					?>
					</span>
					<?php
					//check if show_label is set to show the new or change show_label. is not then every post is set to new
					if ( $show_label ) {
						if ( get_the_modified_date() == get_the_date() ) {
							?>
							<span class="ebsum_new_label">new</span>
							<?php
						} else {
							?>
							<span class="ebsum_change_label">change</span>
							<?php
						}
					} else {
						?>
						<span class="ebsum_new_label">new</span>
						<?php
					}
					?>
					<a href="<?php echo esc_html(get_permalink());?>"><?php echo esc_html(get_the_title());?></a></li>
					<?php
					$count ++;
				}
				if ( $post_query->found_posts > $max_view && $max_view < $limit) {
					?>
					<div class="ebsum_showmorepostbutton">
                    <button type=button id="ebsum_showmoreposts" class="ebsum_showmoreposts">▼</button>
                    <button type=button id="ebsum_showlessposts" class="ebsum_showlessposts">▲</button>
                    </div>
					<?php
				}
				?>
				<br></ul>
				<?php

			} else {
				?>
				<div class="ebsum_showheadline"><h4><?php echo esc_html(ucfirst( $checked ));?></h4><span class="ebsum_countlabel ebsum_zero">0</span></div>
				<ul class="ebsum_show_list" id="ebsum_' . $checked . '"></ul>
				<?php
			}
		}
		?>
		</div>
		<?php
	}
}

/**
 * This function get the selected user roles data from database and echo it.
 *
 * @return void
 */
function show_user(): void {
	$to_check = get_db_data( 'user_roles' );
	$max_view = get_db_data( 'max_view' )[0];
	$limit    = get_db_data( 'load_limit' )[0];
	$start    = check_period();

	if ( $to_check[0] ) {
		?>
		<div><h3><strong>User Roles</strong></h3>
		<?php

		foreach ( $to_check as $checked ) {
			$checked = trim( $checked );
			$args    = array(
				'role'       => $checked,
				'number'     => $limit,
				'order'      => 'DESC',
				'orderby'    => 'user_registered',
				'date_query' => array(
					array(
						'after'     => $start,
						'inclusive' => true,
					),
				),
			);
			$users   = new WP_User_Query( $args );

			if ( ! empty( $users->get_results() ) ) {
				?>
				<div class="ebsum_showheadline"><h4><?php echo esc_html(ucfirst( $checked ));?></h4><span class="ebsum_countlabel"><?php echo esc_html($users->get_total());?></span></div>
				<ul class="ebsum_show_list">
				<?php
				$count = 0;
				foreach ( $users->get_results() as $user ) {

					if ( $count < $max_view ) {
						?>
						<li>
							<span><?php echo esc_html(gmdate( "d M Y", strtotime( esc_html( $user->user_registered ) ) ));?></span>
							<span> </span>
							<a href="users.php?s=' . $user->user_email . '"><?php echo esc_html($user->display_name);?> [<?php echo esc_html( $user->user_email );?>]<?php echo esc_html($user->user_url );?></a>
						</li>
						<?php

					} else {
						?>
						<li class="ebsum_hiddenposts" id="ebsum_hideposts">
							<span><?php echo esc_html(gmdate( "d M Y", strtotime( esc_html( $user->user_registered ) ) ));?></span>
							<span> </span>
							<a href="users.php?s=' . $user->user_email . '"><?php echo esc_html($user->display_name);?> [<?php echo esc_html( $user->user_email );?>]<?php echo esc_html($user->user_url );?></a>
						</li>
						<?php

					}
					$count ++;
				}
				if ( $users->get_total() > $max_view && $max_view < $limit) {
					?>
					<div class="ebsum_showmorepostbutton">
						<button type=button id="ebsum_showmoreposts" class="ebsum_showmoreposts">▼</button>
						<button type=button id="ebsum_showlessposts" class="ebsum_showlessposts">▲</button>
					</div>
					<?php
				}
				?>
				<br></ul>
				<?php

			} else {
				?>
				<div class="ebsum_showheadline"><h4><?php echo esc_html(ucfirst( $checked ));?></h4><span class="ebsum_countlabel ebsum_zero">0</span></div>
				<ul class="ebsum_show_list_user" id="ebsum_<?php echo esc_html($checked);?>"></ul>
				<?php
			}
		}
		?>
		</div>
		<?php
	}
}

?>
