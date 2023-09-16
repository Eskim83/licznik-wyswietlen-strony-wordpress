<?php

/**
 * Plugin Name:       Prosty licznik wyświetleń od eskim.pl
 * Plugin URI:        https://eskim.pl/licznik-wyswietlen-strony-w-wordpress/
 * Description:       Przykład tworzenia wtyczek w WordPress na podstawie kursu https://eskim.pl/licznik-wyswietlen-strony-w-wordpress
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Maciej Włodarczak
 * Author URI:        https://eskim.pl
 * License:           GPL v3 or later
 * Text Domain:       simple_page_views_counter_eskim_pl
 * Domain Path:       /languages
 */

if ( !function_exists( 'add_action' ) ) {

	echo 'Zapraszam do artykułu <a href="https://eskim.pl/licznik-wyswietlen-strony-w-wordpress/">Licznik wyświetleń strony w WordPress</a>';
	exit;
}

if ( !function_exists('eskim_pl_post_count_activate') ) :
function eskim_pl_post_count_activate() {
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	global $wpdb;
	$table_name = $wpdb->prefix . 'eskim_pl_post_count';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			postId INT NOT NULL,
			views INT,
			PRIMARY KEY (postId)
		);";

	dbDelta( $sql );

}
register_activation_hook ( __FILE__, "eskim_pl_post_count_activate" );
endif;

if ( !function_exists('eskim_pl_post_count_set') ) :
function eskim_pl_post_count_set ($postId, $views) {

	global $wpdb;
	$table_name = $wpdb->prefix . 'eskim_pl_post_count';

	if ($views === 0) {
		$wpdb->insert( $table_name,
		[ 
			'postId' => $postId,
			'views' => 1
		]);
	} else {
		
		$wpdb->update( $table_name, ['views' => $views], ['postId' => $postId]);
	}
}
endif;

if ( !function_exists ('eskim_pl_post_count_get') ) :
function eskim_pl_post_count_get ($postId) {

	global $wpdb;
	$table_name = $wpdb->prefix . 'eskim_pl_post_count';

	$query = $wpdb->prepare ("
		SELECT views
		FROM $table_name
		WHERE postId = %d",
    $postId
	  );

	$result = $wpdb->get_var ($query);
	if ($result === null) return 0;
	return $result;
}
endif;

if ( !function_exists('eskim_pl_post_count_inc') ) :
function eskim_pl_post_count_inc($postId) {

	global $wpdb;
	$views = eskim_pl_post_count_get ($postId);
	eskim_pl_post_count_set ($postId, $views+1);
	
}
endif;

if ( !function_exists('eskim_pl_post_count_load') ) :
function eskim_pl_post_count_load()
{
	 eskim_pl_post_count_inc (get_the_ID());
}
if (!is_admin()) add_action( 'wp_footer', 'eskim_pl_post_count_load' );
endif;

if (!class_exists('eskim_pl_post_count_widget')):
class eskim_pl_post_count_widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 
			'classname' => 'eskim_pl_post_count_widget',
			'description' => 'Licznik wyświetleń',
		);
		parent::__construct( 'eskim_pl_post_count_widget', 'Licznik wyświetleń', $widget_ops );
	}

	public function widget( $args, $instance ) {

		echo eskim_pl_post_count_get ( get_the_ID() );
	}
}
add_action( 'widgets_init', function(){
	register_widget( 'eskim_pl_post_count_widget' );
});
endif;


if ( !function_exists('eskim_pl_post_count_clear') ) :
function eskim_pl_post_count_clear() {

	global $wpdb;
	$table_name = $wpdb->prefix . 'eskim_pl_post_count';

	$sql = "DELETE * FROM $table_name";
	return $wpdb->query ($sql);
}

register_uninstall_hook(__FILE__, 'eskim_pl_post_count_clear');
endif;
?>
