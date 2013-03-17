<?php
/**
 * WordPress custom install script.
 *
 * Drop-ins are advanced plugins in the `wp-content` directory that replace WordPress functionality when present.
 *
 * Language: 'nl'
 *
 * if ( file_exists( WP_CONTENT_DIR . '/install.php' ) )
 *   require ( WP_CONTENT_DIR . '/install.php' );
 *
 * @param $user_id
 */
function wp_install_defaults( $user_id ) {
	global $wpdb, $wp_rewrite, $current_site, $table_prefix;

	/** @see wp-admin/includes|schema.php */

	/** RSS language: 'nl' */
	update_option( 'rss_language', 'nl' );

	/** @see wp-admin|options-general.php */

	/** Date format: '23 November 2012' */
	update_option( 'date_format', 'j F Y' );
	/** Time format: '20:56' */
	update_option( 'time_format', 'H:i' );
	/** Time zone: 'Europe/Amsterdam' */
	update_option( 'timezone_string', 'Europe/Amsterdam' );

	/** @see wp-admin|options-reading.php */

	/** The character encoding of this site: 'utf-8' */
	update_option( 'blog_charset', 'utf-8' );

	/** @see wp-admin|options-discussion.php */

	/** Before a comment appears an administrator must always approve the comment: true */
	update_option( 'comment_moderation', '1' );
	/** Before a comment appears the comment author must have a previously approved comment: false */
	update_option( 'comment_whitelist', '0' );
	/** Allow people to post comments on new articles (this setting may be overridden for individual articles): false */
	update_option( 'default_comment_status', '0' );
	/** Allow link notifications from other blogs: false */
	update_option( 'default_ping_status', '0' );
	/** Attempt to notify any blogs linked to from the article: false */
	update_option( 'default_pingback_flag', '0' );
	/** Show avatars: false */
	update_option( 'show_avatars', '0' );
	/** Enable threaded (nested) comments: false */
	update_option( 'thread_comments', '0' );

	/** @see wp-admin|options-media.php */

	/** Organize my uploads into month- and year-based folders: false */
	update_option( 'uploads_use_yearmonth_folders', '0' );

	/** @see wp-admin|options-permalink.php */

	/** Category base: '/categorie' */
	update_option( 'category_base', '/categorie' );
	/** Permalink custom structure: '/%postname%' */
	update_option( 'permalink_structure', '/%postname%' );
	/** Tag base: '/trefwoord' */
	update_option( 'tag_base', '/trefwoord' );

	/** @see wp-admin/includes|upgrade.php */

	/** Default category */
	$cat_name = __( 'Uncategorized' );
	$cat_slug = sanitize_title( _x( 'Uncategorized', 'Default category slug' ) );

	if ( global_terms_enabled() ) {
		$cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_ID FROM {$wpdb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
		if ( $cat_id == null ) {
			$wpdb->insert( $wpdb->sitecategories, array(
				'cat_ID' => 0,
				'cat_name' => $cat_name,
				'category_nicename' => $cat_slug,
				'last_updated' => current_time( 'mysql', true )
			) );
			$cat_id = $wpdb->insert_id;
		}
		update_option( 'default_category', $cat_id );
	} else {
		$cat_id = 1;
	}

	$wpdb->insert( $wpdb->terms, array(
		'name' => $cat_name,
		'slug' => $cat_slug,
		'term_group' => 0,
		'term_id' => $cat_id
	) );
	$wpdb->insert( $wpdb->term_taxonomy, array(
		'count' => 1,
		'description' => '',
		'parent' => 0,
		'taxonomy' => 'category',
		'term_id' => $cat_id
	) );
	$cat_tt_id = $wpdb->insert_id;

	/** First post */
	$now = date( 'Y-m-d H:i:s' );
	$now_gmt = gmdate( 'Y-m-d H:i:s' );
	$first_post_guid = get_option( 'home' ) . '/?p=1';

	$first_post = __( 'Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!' );

	$wpdb->insert( $wpdb->posts, array(
		'comment_count' => '',
		'guid' => $first_post_guid,
		'pinged' => '',
		'post_author' => $user_id,
		'post_content' => $first_post,
		'post_content_filtered' => '',
		'post_date' => $now,
		'post_date_gmt' => $now_gmt,
		'post_excerpt' => '',
		'post_modified' => $now,
		'post_modified_gmt' => $now_gmt,
		'post_name' => sanitize_title( _x( 'hello-world', 'Default post slug' ) ),
		'post_title' => __( 'Hello world!' ),
		'to_ping' => ''
	) );
	$wpdb->insert( $wpdb->term_relationships, array(
		'object_id' => 1,
		'term_taxonomy_id' => $cat_tt_id
	) );

	/** @see wp-admin/includes|screen.php */

	/** Show welcome panel: false */
	update_user_meta( $user_id, 'show_welcome_panel', 0 );

	/** @see wp-includes|user.php */

	/** Disable the visual editor when writing: false */
	update_user_meta( $user_id, 'rich_editing', 0 );

	/** Show toolbar when viewing site: false */
	update_user_meta( $user_id, 'show_admin_bar_front', 0 );
}